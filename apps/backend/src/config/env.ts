import dotenv from 'dotenv';

dotenv.config({ path: '../../.env' });
dotenv.config();

export const env = {
  port: Number(process.env.PORT ?? 4000),
  databaseUrl: process.env.DATABASE_URL ?? '',
  jwtSecret: process.env.JWT_SECRET ?? 'dev_secret',
  jwtRefreshSecret: process.env.JWT_REFRESH_SECRET ?? 'dev_refresh',
  jwtExpiresIn: process.env.JWT_EXPIRES_IN ?? '15m',
  jwtRefreshExpiresIn: process.env.JWT_REFRESH_EXPIRES_IN ?? '7d',
  corsOrigin: process.env.CORS_ORIGIN ?? 'http://localhost:5173',
  smtpHost: process.env.SMTP_HOST,
  smtpPort: Number(process.env.SMTP_PORT ?? 587),
  smtpUser: process.env.SMTP_USER,
  smtpPass: process.env.SMTP_PASS,
  smtpFrom: process.env.SMTP_FROM ?? 'no-reply@example.com',
  twilioSid: process.env.TWILIO_ACCOUNT_SID,
  twilioToken: process.env.TWILIO_AUTH_TOKEN,
  twilioFrom: process.env.TWILIO_WHATSAPP_FROM,
  appBaseUrl: process.env.APP_BASE_URL ?? 'http://localhost:5173',
  redisUrl: process.env.REDIS_URL ?? 'redis://localhost:6379'
};
