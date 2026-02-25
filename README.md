# ExpedientesApp (XAMPP local)

Aplicación web PHP + MySQL para control de expedientes/casos por cliente, con RBAC, semaforización, bitácora y notificaciones por email/WhatsApp.

## Stack
- PHP 8.1+ (probado en 8.2)
- Apache + MySQL en XAMPP
- PDO MySQL
- Bootstrap 5 UI estilo ofimática (sidebar + ribbon)
- Composer opcional/recomendado (`phpmailer/phpmailer`, `vlucas/phpdotenv`)

## Árbol del proyecto

```text
ExpedientesApp/
├─ app/
│  ├─ Controllers/
│  ├─ Core/
│  ├─ Models/
│  └─ Services/
├─ database/
│  ├─ schema.sql
│  └─ seed.sql
├─ public/
│  ├─ assets/
│  │  ├─ css/app.css
│  │  └─ js/app.js
│  ├─ uploads/
│  ├─ .htaccess
│  └─ index.php
├─ scripts/
│  └─ notify_runner.php
├─ storage/logs/app.log
├─ .env.example
├─ composer.json
└─ README.md
```

## Instalación exacta en XAMPP (Windows)

1. Copia la carpeta del proyecto a:
   - `C:\xampp\htdocs\ExpedientesApp`
2. Inicia **Apache** y **MySQL** desde panel de XAMPP.
3. Abre phpMyAdmin: `http://localhost/phpmyadmin`
4. Crea/selecciona base de datos `expedientes_app`.
5. Importa en orden:
   - `database/schema.sql`
   - `database/seed.sql`
6. Copia `.env.example` a `.env` y ajusta:
   - `DATABASE_HOST=127.0.0.1`
   - `DATABASE_NAME=expedientes_app`
   - `DATABASE_USER=root`
   - `DATABASE_PASS=`
   - SMTP y WhatsApp según proveedor.
7. (Recomendado) instala dependencias:
   ```bash
   cd C:\xampp\htdocs\ExpedientesApp
   composer install
   ```
8. Abre la app:
   - `http://localhost/ExpedientesApp/public`

### Credenciales demo
- **Admin**: `admin@local` / `Password123!`
- Capturista: `captura@local` / `Password123!`
- Consultor: `consultor@local` / `Password123!`

## Menú y módulos
- Inicio (Dashboard)
- Clientes (altas/cambios/listado)
- Expedientes (alta/cambios/cerrar + semáforo)
- Usuarios (admin)
- Configuración (warning days, cooldown, canales)
- Bitácoras (auditoría y notificaciones)

## RBAC implementado
- **ADMIN**: CRUD total + settings + logs.
- **CAPTURISTA**: CRUD expedientes, lectura clientes/dashboard.
- **CONSULTOR**: solo lectura.

Validación de permisos en backend (router guard) y ocultamiento de acciones en frontend.

## Semaforización
Reglas:
- Gray: expediente cerrado.
- Green: faltan más de `warning_days`.
- Yellow: entre 1 y `warning_days`.
- Red: vencido (0 o menos).

Se calcula en `TrafficLightService` y se refleja en `status`, `days_remaining`, `is_overdue`.

## Scheduler Windows (obligatorio)
Script: `scripts/notify_runner.php`

### Ejecución manual
```bash
php scripts/notify_runner.php --mode=alerts
php scripts/notify_runner.php --mode=digest
```

### Ejecución por navegador (token)
- `http://localhost/ExpedientesApp/scripts/notify_runner.php?token=TU_TOKEN&mode=alerts`
- `http://localhost/ExpedientesApp/scripts/notify_runner.php?token=TU_TOKEN&mode=digest`

### Programar en Task Scheduler
**Tarea 1 (cada 15 min, alertas):**
- Program/script: `C:\xampp\php\php.exe`
- Add arguments: `C:\xampp\htdocs\ExpedientesApp\scripts\notify_runner.php --mode=alerts`

**Tarea 2 (diaria 09:00, digest):**
- Program/script: `C:\xampp\php\php.exe`
- Add arguments: `C:\xampp\htdocs\ExpedientesApp\scripts\notify_runner.php --mode=digest`

## Notificaciones
- Email vía PHPMailer (SMTP configurable por `.env`).
- WhatsApp vía Twilio o WhatsApp Cloud API.
- Anti-spam: cooldown por tipo de alerta + expediente.
- Log en `notification_log`.

## Rutas principales
- `GET /login`, `POST /login`, `POST /logout`
- `GET /dashboard`
- `GET /clients`, `POST /clients/store`, `POST /clients/update`
- `GET /users`, `POST /users/store`, `POST /users/update`
- `GET /cases`, `GET /cases/create`, `GET /cases/edit?id=`, `POST /cases/store`, `POST /cases/update`
- `GET /settings`, `POST /settings/update`
- `GET /logs`

## Troubleshooting
1. **404 en rutas limpias**:
   - Verifica `mod_rewrite` habilitado en Apache.
   - Verifica `AllowOverride All` en `httpd.conf` para `htdocs`.
2. **No conecta MySQL**:
   - Revisa usuario/password en `.env`.
   - Si cambiaste puerto usa `DATABASE_PORT`.
3. **Errores de correo o WhatsApp**:
   - Revisa credenciales en `.env`.
   - Si no quieres envíos reales, desactiva `MAIL_ENABLED` y `WHATSAPP_ENABLED`.
4. **Extensiones PHP necesarias**:
   - `pdo_mysql`, `openssl`, `curl`, `mbstring`, `json`.
5. **Logs**:
   - Revisar `storage/logs/app.log`.

## Supuestos tomados
- Proyecto enfocado a ejecución local XAMPP sin Docker ni Node.
- Bootstrap CDN para acelerar UI (se puede reemplazar por assets locales).
- Exportación mínima incluida: CSV por query (placeholder en ribbon).
