import { NextFunction, Request, Response } from 'express';
import jwt from 'jsonwebtoken';
import { env } from '../config/env.js';
import { prisma } from '../config/prisma.js';
import { RoleName } from '@prisma/client';

export type AuthPayload = { userId: string; role: RoleName; email: string };

declare global {
  namespace Express {
    interface Request {
      user?: AuthPayload;
    }
  }
}

export async function authenticate(req: Request, res: Response, next: NextFunction) {
  const auth = req.headers.authorization;
  if (!auth?.startsWith('Bearer ')) return res.status(401).json({ message: 'No autorizado' });
  const token = auth.slice(7);
  try {
    const payload = jwt.verify(token, env.jwtSecret) as AuthPayload;
    const dbUser = await prisma.user.findUnique({ where: { id: payload.userId }, include: { role: true } });
    if (!dbUser || !dbUser.isActive) return res.status(401).json({ message: 'Usuario inactivo' });
    req.user = { userId: dbUser.id, role: dbUser.role.name, email: dbUser.email };
    next();
  } catch {
    res.status(401).json({ message: 'Token inválido' });
  }
}

export function authorize(...roles: RoleName[]) {
  return (req: Request, res: Response, next: NextFunction) => {
    if (!req.user || !roles.includes(req.user.role)) return res.status(403).json({ message: 'Sin permisos' });
    next();
  };
}
