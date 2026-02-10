import { Queue, Worker } from 'bullmq';
import { env } from '../config/env.js';
import { prisma } from '../config/prisma.js';
import { deriveCaseState } from '../utils/semaphore.js';
import { sendNotificationWithCooldown } from '../services/notification.service.js';

const connection = { url: env.redisUrl };
export const notificationQueue = new Queue('notifications', { connection });

export async function setupScheduler() {
  const settings = await prisma.settings.findUnique({ where: { id: 1 } });
  const digestCron = `${settings?.digestMinute ?? 0} ${settings?.digestHour ?? 8} * * *`;
  await notificationQueue.upsertJobScheduler('digest', { pattern: digestCron }, { name: 'digest', data: {} });
  await notificationQueue.upsertJobScheduler('alerts', { pattern: settings?.alertHourly ? '0 * * * *' : '0 9 * * *' }, { name: 'alerts', data: {} });
}

export function setupWorker() {
  return new Worker(
    'notifications',
    async (job) => {
      const settings = await prisma.settings.findUnique({ where: { id: 1 } });
      const warningDays = settings?.warningDays ?? 7;
      const cooldown = settings?.cooldownHours ?? 24;

      if (job.name === 'alerts') {
        const cases = await prisma.caseFile.findMany({ where: { closedAt: null }, include: { responsible: true, client: true } });
        for (const c of cases) {
          const derived = deriveCaseState(c, warningDays);
          const type = derived.trafficLight === 'ROJO' ? 'OVERDUE_ALERT' : derived.trafficLight === 'AMARILLO' ? 'WARNING_ALERT' : null;
          if (!type) continue;
          await sendNotificationWithCooldown({
            user: c.responsible,
            caseId: c.id,
            type,
            subject: `[${type}] ${c.folio} - ${c.client.name}`,
            html: `<h3>${c.folio}</h3><p>Cliente: ${c.client.name}</p><p>Días restantes: ${derived.daysRemaining}</p><p>Vence: ${c.dueDate.toDateString()}</p>`,
            whatsappText: `${c.folio} | ${c.client.name} | Días restantes: ${derived.daysRemaining} | Vence: ${c.dueDate.toDateString()} | ${env.appBaseUrl}/cases/${c.id}`,
            cooldownHours: cooldown
          });
        }
      }

      if (job.name === 'digest') {
        const lawyers = await prisma.user.findMany({ where: { isActive: true, role: { name: { in: ['ADMIN', 'CAPTURISTA'] } } }, include: { assignedCases: { include: { client: true } } } });
        for (const lawyer of lawyers) {
          const sections = { ROJO: 0, AMARILLO: 0, VERDE: 0 };
          lawyer.assignedCases.forEach((c) => {
            const tl = deriveCaseState(c, warningDays).trafficLight;
            if (tl in sections) sections[tl as keyof typeof sections] += 1;
          });
          await sendNotificationWithCooldown({
            user: lawyer,
            type: 'DAILY_DIGEST',
            subject: `Resumen de expedientes ${new Date().toLocaleDateString()}`,
            html: `<p>Rojos: ${sections.ROJO} | Amarillos: ${sections.AMARILLO} | Verdes: ${sections.VERDE}</p>`,
            whatsappText: `Resumen: Rojos ${sections.ROJO}, Amarillos ${sections.AMARILLO}, Verdes ${sections.VERDE}`,
            cooldownHours: 12
          });
        }
      }
    },
    { connection }
  );
}
