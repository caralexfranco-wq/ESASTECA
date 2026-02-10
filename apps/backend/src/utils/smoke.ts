import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

async function run() {
  const users = await prisma.user.count();
  const clients = await prisma.clientCompany.count();
  const cases = await prisma.caseFile.count();
  console.log({ users, clients, cases, smoke: 'ok' });
}

run().finally(async () => prisma.$disconnect());
