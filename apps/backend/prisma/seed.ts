import { PrismaClient, RoleName } from '@prisma/client';
import bcrypt from 'bcryptjs';
import dayjs from 'dayjs';

const prisma = new PrismaClient();

async function main() {
  await prisma.notificationLog.deleteMany();
  await prisma.caseStatusHistory.deleteMany();
  await prisma.caseFile.deleteMany();
  await prisma.auditLog.deleteMany();
  await prisma.clientCompany.deleteMany();
  await prisma.user.deleteMany();
  await prisma.role.deleteMany();

  const roles = await prisma.role.createMany({
    data: [
      { name: RoleName.ADMIN },
      { name: RoleName.CAPTURISTA },
      { name: RoleName.CONSULTOR }
    ]
  });

  const dbRoles = await prisma.role.findMany();
  const roleMap = Object.fromEntries(dbRoles.map((r) => [r.name, r.id]));
  const hash = await bcrypt.hash('Password123!', 10);

  const admin = await prisma.user.create({
    data: {
      fullName: 'Administrador Local',
      email: 'admin@local',
      passwordHash: hash,
      roleId: roleMap.ADMIN,
      phone: '+5215511111111'
    }
  });

  const lawyerA = await prisma.user.create({
    data: {
      fullName: 'Ana Torres',
      email: 'ana@local',
      passwordHash: hash,
      roleId: roleMap.CAPTURISTA,
      phone: '+5215522222222'
    }
  });

  const lawyerB = await prisma.user.create({
    data: {
      fullName: 'Luis Pineda',
      email: 'luis@local',
      passwordHash: hash,
      roleId: roleMap.CAPTURISTA,
      phone: '+5215533333333'
    }
  });

  await prisma.user.create({
    data: {
      fullName: 'Consultor Demo',
      email: 'consultor@local',
      passwordHash: hash,
      roleId: roleMap.CONSULTOR
    }
  });

  const clients = await Promise.all([
    prisma.clientCompany.create({ data: { name: 'Oficinas Delta', businessType: 'Oficinas', email: 'contacto@delta.com' } }),
    prisma.clientCompany.create({ data: { name: 'Empresa Atlas', businessType: 'Empresa', email: 'legal@atlas.com' } }),
    prisma.clientCompany.create({ data: { name: 'Gasolinera Norte', businessType: 'Gasolinera', email: 'admin@norte.com' } })
  ]);

  const caseTemplates = Array.from({ length: 10 }).map((_, idx) => {
    const dueDate = [12, 4, -2, 9, 1, -1, 20, 5, -4, 14][idx];
    return {
      folio: `EXP-${dayjs().format('YYYY')}-${String(idx + 1).padStart(4, '0')}`,
      clientId: clients[idx % clients.length].id,
      type: idx % 2 === 0 ? 'Seguridad e Higiene' : 'Capacitación',
      shortDescription: `Expediente demo ${idx + 1}`,
      responsibleLawyer: idx % 2 === 0 ? lawyerA.id : lawyerB.id,
      createdById: admin.id,
      startDate: dayjs().subtract(15 - idx, 'day').toDate(),
      dueDate: dayjs().add(dueDate, 'day').toDate(),
      progressPercent: Math.min(100, idx * 10),
      currentStage: `Etapa ${Math.min(5, idx + 1)}`,
      internalNotes: 'Caso generado por seed'
    };
  });

  for (const data of caseTemplates) {
    const created = await prisma.caseFile.create({ data });
    await prisma.caseStatusHistory.create({
      data: {
        caseId: created.id,
        updatedById: admin.id,
        progress: created.progressPercent,
        stage: created.currentStage,
        comment: 'Creación inicial de expediente'
      }
    });
  }

  await prisma.settings.upsert({
    where: { id: 1 },
    update: {},
    create: { id: 1, warningDays: 7, digestHour: 8, digestMinute: 0, alertHourly: true, cooldownHours: 24 }
  });

  console.log(`Seed completo. Roles creados: ${roles.count}`);
}

main().finally(async () => prisma.$disconnect());
