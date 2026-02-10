import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import morgan from 'morgan';
import rateLimit from 'express-rate-limit';
import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';
import { z } from 'zod';
import { RoleName } from '@prisma/client';
import { env } from './config/env.js';
import { prisma } from './config/prisma.js';
import { authenticate, authorize } from './middleware/auth.js';
import { deriveCaseState } from './utils/semaphore.js';
import { generateFolio } from './utils/folio.js';
import { logAudit } from './services/audit.service.js';
import { notificationQueue } from './jobs/scheduler.js';

export const app = express();
app.use(cors({ origin: env.corsOrigin }));
app.use(helmet());
app.use(express.json());
app.use(morgan('dev'));

const loginLimiter = rateLimit({ windowMs: 15 * 60 * 1000, max: 20 });

function signTokens(user: { id: string; email: string; role: { name: RoleName } }) {
  const accessToken = jwt.sign({ userId: user.id, email: user.email, role: user.role.name }, env.jwtSecret, { expiresIn: env.jwtExpiresIn as jwt.SignOptions['expiresIn'] });
  const refreshToken = jwt.sign({ userId: user.id }, env.jwtRefreshSecret, { expiresIn: env.jwtRefreshExpiresIn as jwt.SignOptions['expiresIn'] });
  return { accessToken, refreshToken };
}

app.get('/health', (_, res) => res.json({ ok: true }));
app.post('/auth/login', loginLimiter, async (req, res) => {
  const schema = z.object({ email: z.string().email(), password: z.string().min(8) });
  const parse = schema.safeParse(req.body);
  if (!parse.success) return res.status(400).json(parse.error.flatten());

  const user = await prisma.user.findUnique({ where: { email: parse.data.email }, include: { role: true } });
  if (!user || !user.isActive) return res.status(401).json({ message: 'Credenciales inválidas' });
  const valid = await bcrypt.compare(parse.data.password, user.passwordHash);
  if (!valid) return res.status(401).json({ message: 'Credenciales inválidas' });

  const tokens = signTokens(user);
  await prisma.user.update({ where: { id: user.id }, data: { refreshToken: await bcrypt.hash(tokens.refreshToken, 10) } });
  res.json({ user: { id: user.id, name: user.fullName, email: user.email, role: user.role.name }, ...tokens });
});

app.post('/auth/refresh', async (req, res) => {
  const refreshToken = req.body.refreshToken;
  if (!refreshToken) return res.status(400).json({ message: 'refreshToken requerido' });
  try {
    const payload = jwt.verify(refreshToken, env.jwtRefreshSecret) as { userId: string };
    const user = await prisma.user.findUnique({ where: { id: payload.userId }, include: { role: true } });
    if (!user?.refreshToken) return res.status(401).json({ message: 'Inválido' });
    const ok = await bcrypt.compare(refreshToken, user.refreshToken);
    if (!ok) return res.status(401).json({ message: 'Inválido' });
    const tokens = signTokens(user);
    await prisma.user.update({ where: { id: user.id }, data: { refreshToken: await bcrypt.hash(tokens.refreshToken, 10) } });
    res.json(tokens);
  } catch {
    res.status(401).json({ message: 'Inválido' });
  }
});
app.post('/auth/logout', authenticate, async (req, res) => { await prisma.user.update({ where: { id: req.user!.userId }, data: { refreshToken: null } }); res.status(204).send(); });
app.get('/auth/me', authenticate, async (req, res) => {
  const me = await prisma.user.findUnique({ where: { id: req.user!.userId }, include: { role: true } });
  res.json({ id: me?.id, fullName: me?.fullName, email: me?.email, role: me?.role.name });
});

app.get('/users', authenticate, authorize(RoleName.ADMIN), async (_, res) => res.json(await prisma.user.findMany({ include: { role: true } })));
app.post('/users', authenticate, authorize(RoleName.ADMIN), async (req, res) => {
  const schema = z.object({ fullName: z.string(), email: z.string().email(), password: z.string().min(8), role: z.nativeEnum(RoleName), phone: z.string().optional() });
  const p = schema.parse(req.body);
  const role = await prisma.role.findUniqueOrThrow({ where: { name: p.role } });
  const u = await prisma.user.create({ data: { fullName: p.fullName, email: p.email, passwordHash: await bcrypt.hash(p.password, 10), phone: p.phone, roleId: role.id } });
  await logAudit({ actorId: req.user?.userId, entity: 'User', entityId: u.id, action: 'CREATE', diff: p, ipAddress: req.ip });
  res.status(201).json(u);
});
app.patch('/users/:id', authenticate, authorize(RoleName.ADMIN), async (req, res) => {
  const updated = await prisma.user.update({ where: { id: req.params.id }, data: req.body });
  await logAudit({ actorId: req.user?.userId, entity: 'User', entityId: updated.id, action: 'UPDATE', diff: req.body, ipAddress: req.ip });
  res.json(updated);
});

app.get('/clients', authenticate, async (_, res) => res.json(await prisma.clientCompany.findMany()));
app.post('/clients', authenticate, authorize(RoleName.ADMIN), async (req, res) => {
  const schema = z.object({ name: z.string(), businessType: z.string(), contactName: z.string().optional(), email: z.string().email().optional(), phone: z.string().optional(), address: z.string().optional() });
  const data = schema.parse(req.body);
  const created = await prisma.clientCompany.create({ data });
  await logAudit({ actorId: req.user?.userId, entity: 'ClientCompany', entityId: created.id, action: 'CREATE', diff: data, ipAddress: req.ip });
  res.status(201).json(created);
});
app.patch('/clients/:id', authenticate, authorize(RoleName.ADMIN), async (req, res) => { const updated = await prisma.clientCompany.update({ where: { id: req.params.id }, data: req.body }); res.json(updated); });
app.delete('/clients/:id', authenticate, authorize(RoleName.ADMIN), async (req, res) => { await prisma.clientCompany.update({ where: { id: req.params.id }, data: { isActive: false } }); res.status(204).send(); });

app.get('/cases', authenticate, async (req, res) => {
  const settings = await prisma.settings.findUnique({ where: { id: 1 } });
  const cases = await prisma.caseFile.findMany({ include: { client: true, responsible: true } });
  const lawyerFilter = req.query.responsibleLawyer as string | undefined;
  const filtered = cases.filter((c) => !lawyerFilter || c.responsibleLawyer === lawyerFilter);
  const enriched = filtered.map((c) => ({ ...c, ...deriveCaseState(c, settings?.warningDays ?? 7) }));
  res.json(enriched);
});
app.get('/cases/:id', authenticate, async (req, res) => res.json(await prisma.caseFile.findUnique({ where: { id: req.params.id }, include: { history: true } })));
app.post('/cases', authenticate, authorize(RoleName.ADMIN, RoleName.CAPTURISTA), async (req, res) => {
  const count = await prisma.caseFile.count();
  const schema = z.object({ clientId: z.string(), type: z.string(), shortDescription: z.string(), responsibleLawyer: z.string(), startDate: z.coerce.date(), dueDate: z.coerce.date(), progressPercent: z.number().min(0).max(100).default(0), currentStage: z.string().optional(), internalNotes: z.string().optional() });
  const data = schema.parse(req.body);
  const created = await prisma.caseFile.create({ data: { ...data, folio: generateFolio(count + 1), createdById: req.user!.userId } });
  await prisma.caseStatusHistory.create({ data: { caseId: created.id, updatedById: req.user!.userId, progress: created.progressPercent, stage: created.currentStage, comment: 'Alta de expediente' } });
  await logAudit({ actorId: req.user?.userId, entity: 'CaseFile', entityId: created.id, action: 'CREATE', diff: data, ipAddress: req.ip });
  res.status(201).json(created);
});
app.patch('/cases/:id', authenticate, authorize(RoleName.ADMIN, RoleName.CAPTURISTA), async (req, res) => {
  const updated = await prisma.caseFile.update({ where: { id: req.params.id }, data: req.body });
  await logAudit({ actorId: req.user?.userId, entity: 'CaseFile', entityId: updated.id, action: 'UPDATE', diff: req.body, ipAddress: req.ip });
  res.json(updated);
});
app.post('/cases/:id/close', authenticate, authorize(RoleName.ADMIN, RoleName.CAPTURISTA), async (req, res) => {
  const closed = await prisma.caseFile.update({ where: { id: req.params.id }, data: { closedAt: new Date(), state: 'CERRADO', progressPercent: 100 } });
  await prisma.caseStatusHistory.create({ data: { caseId: closed.id, updatedById: req.user!.userId, progress: 100, comment: 'Cierre de expediente', stage: 'Cerrado' } });
  res.json(closed);
});
app.post('/cases/:id/reassign', authenticate, authorize(RoleName.ADMIN, RoleName.CAPTURISTA), async (req, res) => {
  const newLawyerId = z.string().parse(req.body.responsibleLawyer);
  const updated = await prisma.caseFile.update({ where: { id: req.params.id }, data: { responsibleLawyer: newLawyerId } });
  await logAudit({ actorId: req.user?.userId, entity: 'CaseFile', entityId: updated.id, action: 'REASSIGN', diff: req.body, ipAddress: req.ip });
  res.json(updated);
});
app.post('/cases/:id/history', authenticate, authorize(RoleName.ADMIN, RoleName.CAPTURISTA), async (req, res) => {
  const schema = z.object({ progress: z.number().min(0).max(100).optional(), stage: z.string().optional(), comment: z.string() });
  const data = schema.parse(req.body);
  const history = await prisma.caseStatusHistory.create({ data: { caseId: req.params.id, updatedById: req.user!.userId, ...data } });
  await prisma.caseFile.update({ where: { id: req.params.id }, data: { progressPercent: data.progress, currentStage: data.stage } });
  res.status(201).json(history);
});
app.get('/cases/:id/history', authenticate, async (req, res) => res.json(await prisma.caseStatusHistory.findMany({ where: { caseId: req.params.id }, include: { updatedBy: true } })));

app.get('/semaphore', authenticate, async (_, res) => {
  const settings = await prisma.settings.findUnique({ where: { id: 1 } });
  const cases = await prisma.caseFile.findMany({ include: { client: true, responsible: true } });
  res.json(cases.map((c) => ({ ...c, ...deriveCaseState(c, settings?.warningDays ?? 7) })));
});

app.get('/notifications/logs', authenticate, authorize(RoleName.ADMIN), async (_, res) => res.json(await prisma.notificationLog.findMany({ orderBy: { sentAt: 'desc' }, take: 500 })));
app.post('/notifications/retry/:id', authenticate, authorize(RoleName.ADMIN), async (req, res) => { await notificationQueue.add('alerts', { manual: req.params.id }); res.status(202).json({ queued: true }); });

app.get('/settings', authenticate, authorize(RoleName.ADMIN), async (_, res) => res.json(await prisma.settings.findUnique({ where: { id: 1 } })));
app.patch('/settings', authenticate, authorize(RoleName.ADMIN), async (req, res) => {
  const schema = z.object({ warningDays: z.number().min(1).max(60).optional(), digestHour: z.number().min(0).max(23).optional(), digestMinute: z.number().min(0).max(59).optional(), alertHourly: z.boolean().optional(), cooldownHours: z.number().min(1).max(240).optional(), preferredChannel: z.string().optional() });
  const data = schema.parse(req.body);
  const updated = await prisma.settings.upsert({ where: { id: 1 }, update: data, create: { id: 1, ...data } });
  res.json(updated);
});

app.get('/dashboard', authenticate, async (req, res) => {
  const settings = await prisma.settings.findUnique({ where: { id: 1 } });
  const warningDays = settings?.warningDays ?? 7;
  const where = req.user?.role === RoleName.CONSULTOR ? {} : {};
  const cases = await prisma.caseFile.findMany({ where, include: { client: true } });
  const sem = cases.map((c) => ({ ...c, ...deriveCaseState(c, warningDays) }));
  const cards = {
    abiertos: sem.filter((x) => x.derivedState === 'ABIERTO').length,
    riesgo: sem.filter((x) => x.derivedState === 'EN_RIESGO').length,
    vencidos: sem.filter((x) => x.derivedState === 'VENCIDO').length,
    cerrados: sem.filter((x) => x.derivedState === 'CERRADO').length
  };
  res.json({ cards, dueSoon: sem.filter((x) => x.daysRemaining <= warningDays && x.daysRemaining >= 0) });
});

app.use((err: Error, _req: express.Request, res: express.Response, _next: express.NextFunction) => {
  console.error(err);
  res.status(500).json({ message: 'Error interno', detail: err.message });
});
