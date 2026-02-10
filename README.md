# ESASTECA - Plataforma Legal para Control de Expedientes

Aplicación full-stack para despacho de abogados con control de expedientes por cliente, semaforización configurable, notificaciones automáticas (Email + WhatsApp), RBAC estricto y auditoría.

## 1) Estructura del proyecto

```text
ESASTECA/
├─ apps/
│  ├─ backend/
│  │  ├─ prisma/
│  │  │  ├─ schema.prisma
│  │  │  └─ seed.ts
│  │  ├─ src/
│  │  │  ├─ app.ts
│  │  │  ├─ server.ts
│  │  │  ├─ config/
│  │  │  ├─ middleware/
│  │  │  ├─ jobs/
│  │  │  ├─ services/
│  │  │  └─ utils/
│  │  ├─ package.json
│  │  └─ tsconfig.json
│  └─ frontend/
│     ├─ src/
│     │  ├─ App.tsx
│     │  ├─ main.tsx
│     │  ├─ api/
│     │  ├─ auth/
│     │  ├─ layout/
│     │  ├─ pages/
│     │  └─ utils/
│     ├─ index.html
│     ├─ package.json
│     ├─ tsconfig.json
│     └─ vite.config.ts
├─ .env.example
├─ docker-compose.yml
└─ package.json
```

## 2) Requisitos

- Node.js 20+
- npm 10+
- Docker + Docker Compose

## 3) Instalación y ejecución local

```bash
cp .env.example .env
npm install
docker compose up -d
npm run migrate
npm run seed
npm run dev
```

- Backend: `http://localhost:4000`
- Frontend: `http://localhost:5173`

Credenciales seed local:
- Admin: `admin@local` / `Password123!`
- Capturista: `ana@local` / `Password123!`
- Consultor: `consultor@local` / `Password123!`

## 4) Variables de entorno

Usar `.env.example` como base:
- `DATABASE_URL`
- `JWT_SECRET`, `JWT_REFRESH_SECRET`
- `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASS`, `SMTP_FROM`
- `TWILIO_ACCOUNT_SID`, `TWILIO_AUTH_TOKEN`, `TWILIO_WHATSAPP_FROM`
- `APP_BASE_URL`
- `REDIS_URL`
- `PORT`, `CORS_ORIGIN`

## 5) Modelo de datos (3FN)

Entidades principales:
- `Role` (ADMIN, CAPTURISTA, CONSULTOR)
- `User`
- `ClientCompany`
- `CaseFile`
- `CaseStatusHistory`
- `NotificationLog`
- `AuditLog`
- `Settings`

La semaforización se deriva por fecha de vencimiento y estado/cierre:
- Verde: días restantes > `warningDays`
- Amarillo: 1..`warningDays`
- Rojo: <= 0
- Gris: cerrado

## 6) API principal

### Auth
- `POST /auth/login`
- `POST /auth/refresh`
- `POST /auth/logout`
- `GET /auth/me`

### Users (admin)
- `GET /users`
- `POST /users`
- `PATCH /users/:id`

### Clients
- `GET /clients`
- `POST /clients` (admin)
- `PATCH /clients/:id` (admin)
- `DELETE /clients/:id` (admin, baja lógica)

### Cases
- `GET /cases`
- `GET /cases/:id`
- `POST /cases` (admin/capturista)
- `PATCH /cases/:id` (admin/capturista)
- `POST /cases/:id/close` (admin/capturista)
- `POST /cases/:id/reassign` (admin/capturista)

### Historial de expediente
- `GET /cases/:id/history`
- `POST /cases/:id/history`

### Semáforo
- `GET /semaphore`

### Notificaciones
- `GET /notifications/logs` (admin)
- `POST /notifications/retry/:id` (admin)

### Settings
- `GET /settings` (admin)
- `PATCH /settings` (admin)

### Dashboard
- `GET /dashboard`

## 7) Ejemplos de payloads

Login:
```json
{
  "email": "admin@local",
  "password": "Password123!"
}
```

Crear cliente:
```json
{
  "name": "Oficinas Central",
  "businessType": "Oficinas",
  "contactName": "María Perez",
  "email": "legal@cliente.com",
  "phone": "+525512345678",
  "address": "CDMX"
}
```

Crear expediente:
```json
{
  "clientId": "clx...",
  "type": "Seguridad e Higiene",
  "shortDescription": "Inspección STPS 2026",
  "responsibleLawyer": "usr...",
  "startDate": "2026-01-10",
  "dueDate": "2026-01-25",
  "progressPercent": 35,
  "currentStage": "Recolección documental",
  "internalNotes": "Prioridad alta"
}
```

Actualizar historial:
```json
{
  "progress": 60,
  "stage": "Revisión legal",
  "comment": "Se cargaron evidencias y acta interna"
}
```

## 8) Frontend (rutas / pantallas)

- `/` Dashboard (cards de estado)
- `/clientes` grid con búsqueda y exportación
- `/expedientes` grid con filtros por semáforo
- `/semaforo` vista de semaforización
- `/usuarios` grid de usuarios (solo admin)

UI orientada a ofimática estilo Windows:
- Sidebar lateral
- Ribbon superior con acciones (Nuevo, Editar, Exportar, Buscar)
- DataGrids con paginación/ordenación
- Roles reflejados en visibilidad de pantallas

## 9) Scheduler + Notificaciones

Se usa BullMQ + Redis:
- Job `alerts`: horario configurable (cada hora o diario)
- Job `digest`: resumen diario por abogado

Cooldown anti-spam:
- `cooldownHours` evita repetir la misma alerta por expediente y tipo dentro del periodo

Canales:
- Email (Nodemailer)
- WhatsApp (Twilio, si credenciales configuradas)

## 10) Scripts

Root:
- `npm run dev`
- `npm run build`
- `npm run migrate`
- `npm run seed`
- `npm run smoke`

Backend:
- `npm run dev -w apps/backend`
- `npm run migrate -w apps/backend`
- `npm run seed -w apps/backend`

Frontend:
- `npm run dev -w apps/frontend`
- `npm run build -w apps/frontend`

## 11) Despliegue (producción)

Supuestos documentados:
- API y UI se despliegan como servicios separados.
- Se utiliza PostgreSQL administrado + Redis administrado.
- Variables sensibles se inyectan por secret manager.
- CORS restringido al dominio frontal.
- Reverse proxy TLS al frente (Nginx/Traefik).

Flujo recomendado:
1. `npm ci`
2. `npm run build`
3. `npm run migrate -w apps/backend`
4. `npm run seed -w apps/backend` (solo ambiente inicial)
5. `npm run start -w apps/backend`
6. Servir `apps/frontend/dist` en CDN/Nginx.
