import { prisma } from '../config/prisma.js';

export async function logAudit(input: {
  actorId?: string;
  entity: string;
  entityId: string;
  action: string;
  diff: unknown;
  ipAddress?: string;
}) {
  await prisma.auditLog.create({ data: input });
}
