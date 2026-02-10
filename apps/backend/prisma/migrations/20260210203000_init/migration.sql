-- Create enums
CREATE TYPE "RoleName" AS ENUM ('ADMIN', 'CAPTURISTA', 'CONSULTOR');
CREATE TYPE "CaseState" AS ENUM ('ABIERTO', 'EN_RIESGO', 'VENCIDO', 'CERRADO');
CREATE TYPE "TrafficLight" AS ENUM ('VERDE', 'AMARILLO', 'ROJO', 'GRIS');
CREATE TYPE "NotificationChannel" AS ENUM ('EMAIL', 'WHATSAPP');

CREATE TABLE "Role" (
  "id" SERIAL PRIMARY KEY,
  "name" "RoleName" NOT NULL UNIQUE
);

CREATE TABLE "User" (
  "id" TEXT PRIMARY KEY,
  "fullName" TEXT NOT NULL,
  "email" TEXT NOT NULL UNIQUE,
  "passwordHash" TEXT NOT NULL,
  "phone" TEXT,
  "isActive" BOOLEAN NOT NULL DEFAULT true,
  "roleId" INTEGER NOT NULL REFERENCES "Role"("id"),
  "refreshToken" TEXT,
  "createdAt" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updatedAt" TIMESTAMP NOT NULL
);

CREATE TABLE "ClientCompany" (
  "id" TEXT PRIMARY KEY,
  "name" TEXT NOT NULL,
  "businessType" TEXT NOT NULL,
  "contactName" TEXT,
  "email" TEXT,
  "phone" TEXT,
  "address" TEXT,
  "isActive" BOOLEAN NOT NULL DEFAULT true,
  "createdAt" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updatedAt" TIMESTAMP NOT NULL
);
CREATE INDEX "ClientCompany_name_idx" ON "ClientCompany"("name");

CREATE TABLE "CaseFile" (
  "id" TEXT PRIMARY KEY,
  "folio" TEXT NOT NULL UNIQUE,
  "clientId" TEXT NOT NULL REFERENCES "ClientCompany"("id"),
  "type" TEXT NOT NULL,
  "shortDescription" TEXT NOT NULL,
  "responsibleLawyer" TEXT NOT NULL REFERENCES "User"("id"),
  "createdById" TEXT NOT NULL REFERENCES "User"("id"),
  "startDate" TIMESTAMP NOT NULL,
  "dueDate" TIMESTAMP NOT NULL,
  "closedAt" TIMESTAMP,
  "state" "CaseState" NOT NULL DEFAULT 'ABIERTO',
  "progressPercent" INTEGER NOT NULL DEFAULT 0,
  "currentStage" TEXT,
  "internalNotes" TEXT,
  "attachmentPath" TEXT,
  "createdAt" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updatedAt" TIMESTAMP NOT NULL
);
CREATE INDEX "CaseFile_dueDate_idx" ON "CaseFile"("dueDate");
CREATE INDEX "CaseFile_state_idx" ON "CaseFile"("state");
CREATE INDEX "CaseFile_responsibleLawyer_idx" ON "CaseFile"("responsibleLawyer");

CREATE TABLE "CaseStatusHistory" (
  "id" TEXT PRIMARY KEY,
  "caseId" TEXT NOT NULL REFERENCES "CaseFile"("id") ON DELETE CASCADE,
  "updatedById" TEXT NOT NULL REFERENCES "User"("id"),
  "progress" INTEGER,
  "stage" TEXT,
  "comment" TEXT NOT NULL,
  "createdAt" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "NotificationLog" (
  "id" TEXT PRIMARY KEY,
  "caseId" TEXT REFERENCES "CaseFile"("id") ON DELETE SET NULL,
  "userId" TEXT NOT NULL REFERENCES "User"("id"),
  "channel" "NotificationChannel" NOT NULL,
  "type" TEXT NOT NULL,
  "status" TEXT NOT NULL,
  "payload" JSONB NOT NULL,
  "error" TEXT,
  "sentAt" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX "NotificationLog_type_sentAt_idx" ON "NotificationLog"("type", "sentAt");
CREATE INDEX "NotificationLog_caseId_type_idx" ON "NotificationLog"("caseId", "type");

CREATE TABLE "AuditLog" (
  "id" TEXT PRIMARY KEY,
  "actorId" TEXT REFERENCES "User"("id") ON DELETE SET NULL,
  "entity" TEXT NOT NULL,
  "entityId" TEXT NOT NULL,
  "action" TEXT NOT NULL,
  "diff" JSONB NOT NULL,
  "ipAddress" TEXT,
  "createdAt" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX "AuditLog_entity_entityId_idx" ON "AuditLog"("entity", "entityId");

CREATE TABLE "Settings" (
  "id" INTEGER PRIMARY KEY,
  "warningDays" INTEGER NOT NULL DEFAULT 7,
  "digestHour" INTEGER NOT NULL DEFAULT 8,
  "digestMinute" INTEGER NOT NULL DEFAULT 0,
  "alertHourly" BOOLEAN NOT NULL DEFAULT true,
  "cooldownHours" INTEGER NOT NULL DEFAULT 24,
  "preferredChannel" TEXT NOT NULL DEFAULT 'EMAIL',
  "updatedAt" TIMESTAMP NOT NULL
);
