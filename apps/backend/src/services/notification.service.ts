import nodemailer from 'nodemailer';
import twilio from 'twilio';
import { NotificationChannel, Prisma, User } from '@prisma/client';
import { env } from '../config/env.js';
import { prisma } from '../config/prisma.js';

type SendInput = {
  user: User;
  caseId?: string;
  type: string;
  subject: string;
  html: string;
  whatsappText: string;
  cooldownHours: number;
};

const mailer = nodemailer.createTransport({
  host: env.smtpHost,
  port: env.smtpPort,
  secure: false,
  auth: env.smtpUser ? { user: env.smtpUser, pass: env.smtpPass } : undefined
});

const twilioClient = env.twilioSid && env.twilioToken ? twilio(env.twilioSid, env.twilioToken) : null;

export async function sendNotificationWithCooldown(input: SendInput) {
  const since = new Date(Date.now() - input.cooldownHours * 60 * 60 * 1000);
  const existing = await prisma.notificationLog.findFirst({
    where: { caseId: input.caseId, userId: input.user.id, type: input.type, sentAt: { gte: since }, status: 'SENT' }
  });
  if (existing) return;

  const logs: Prisma.NotificationLogCreateManyInput[] = [];

  if (input.user.email) {
    try {
      await mailer.sendMail({ from: env.smtpFrom, to: input.user.email, subject: input.subject, html: input.html });
      logs.push({ caseId: input.caseId, userId: input.user.id, type: input.type, status: 'SENT', channel: NotificationChannel.EMAIL, payload: { subject: input.subject } });
    } catch (error) {
      logs.push({ caseId: input.caseId, userId: input.user.id, type: input.type, status: 'FAILED', channel: NotificationChannel.EMAIL, payload: { subject: input.subject }, error: String(error) });
    }
  }

  if (twilioClient && input.user.phone && env.twilioFrom) {
    try {
      await twilioClient.messages.create({ from: env.twilioFrom, to: `whatsapp:${input.user.phone}`, body: input.whatsappText });
      logs.push({ caseId: input.caseId, userId: input.user.id, type: input.type, status: 'SENT', channel: NotificationChannel.WHATSAPP, payload: { body: input.whatsappText } });
    } catch (error) {
      logs.push({ caseId: input.caseId, userId: input.user.id, type: input.type, status: 'FAILED', channel: NotificationChannel.WHATSAPP, payload: { body: input.whatsappText }, error: String(error) });
    }
  }

  if (logs.length) await prisma.notificationLog.createMany({ data: logs });
}
