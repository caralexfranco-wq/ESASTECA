USE expedientes_app;

INSERT INTO roles (`key`) VALUES ('ADMIN'),('CAPTURISTA'),('CONSULTOR');

INSERT INTO users (role_id,name,email,phone,password_hash,is_active,created_at,updated_at) VALUES
(1,'Administrador General','admin@local','+5215510000001','$2y$10$cbnqDE8RA8GmDYzYcoF8e.rWfA2jz8/3Pj5X6V3Smf6hHwL2uAAg2',1,NOW(),NOW()),
(1,'Abogada Laura Pérez','laura@local','+5215510000002','$2y$10$cbnqDE8RA8GmDYzYcoF8e.rWfA2jz8/3Pj5X6V3Smf6hHwL2uAAg2',1,NOW(),NOW()),
(1,'Abogado Carlos Ruiz','carlos@local','+5215510000003','$2y$10$cbnqDE8RA8GmDYzYcoF8e.rWfA2jz8/3Pj5X6V3Smf6hHwL2uAAg2',1,NOW(),NOW()),
(2,'Capturista Ana','captura@local','+5215510000004','$2y$10$cbnqDE8RA8GmDYzYcoF8e.rWfA2jz8/3Pj5X6V3Smf6hHwL2uAAg2',1,NOW(),NOW()),
(3,'Consultor Luis','consultor@local','+5215510000005','$2y$10$cbnqDE8RA8GmDYzYcoF8e.rWfA2jz8/3Pj5X6V3Smf6hHwL2uAAg2',1,NOW(),NOW());

INSERT INTO clients (type,razon_social,rfc,contacto,email,phone,address,is_active,created_at,updated_at) VALUES
('empresa','Industrias Acero del Centro','IAC120101AA1','María Soto','contacto@iac.local','5552000101','Parque Industrial 120, CDMX',1,NOW(),NOW()),
('gasolinera','Gasolinera Norte 24h','GNO150201BB2','Jorge Rivera','admin@gasnorte.local','5552000102','Av. Norte 442, Puebla',1,NOW(),NOW()),
('oficina','Corporativo RH Integral','CRI181212CC3','Sandra Vega','rh@cri.local','5552000103','Torre Ejecutiva Piso 9, Querétaro',1,NOW(),NOW());

INSERT INTO settings (id,warning_days,digest_time,cooldown_hours,whatsapp_enabled,email_enabled,updated_at)
VALUES (1,7,'09:00:00',24,0,1,NOW());

INSERT INTO cases (folio,client_id,asunto_tipo,descripcion,responsable_user_id,fecha_inicio,fecha_vencimiento,fecha_termino_real,porcentaje_avance,notas,status,last_traffic_light,created_at,updated_at) VALUES
('EXP-2026-000001',1,'SeguridadHigiene','Programa anual STPS',2,CURDATE()-INTERVAL 40 DAY,CURDATE()+INTERVAL 20 DAY,NULL,35,'Revisión documental','Abierto','green',NOW(),NOW()),
('EXP-2026-000002',1,'Capacitacion','Curso brigadas internas',2,CURDATE()-INTERVAL 30 DAY,CURDATE()+INTERVAL 4 DAY,NULL,70,'Pendiente evidencia','En Riesgo','yellow',NOW(),NOW()),
('EXP-2026-000003',2,'Otro','Renovación licencia municipal',3,CURDATE()-INTERVAL 45 DAY,CURDATE()-INTERVAL 1 DAY,NULL,55,'En espera de pago','Vencido','red',NOW(),NOW()),
('EXP-2026-000004',2,'SeguridadHigiene','Inspección NOM-030',3,CURDATE()-INTERVAL 10 DAY,CURDATE()+INTERVAL 2 DAY,NULL,20,'Preparación expediente','En Riesgo','yellow',NOW(),NOW()),
('EXP-2026-000005',3,'Capacitacion','Inducción personal nuevo',2,CURDATE()-INTERVAL 6 DAY,CURDATE()+INTERVAL 18 DAY,NULL,15,'Planificación','Abierto','green',NOW(),NOW()),
('EXP-2026-000006',3,'Otro','Acta administrativa',3,CURDATE()-INTERVAL 25 DAY,CURDATE()-INTERVAL 4 DAY,NULL,80,'Urgente','Vencido','red',NOW(),NOW()),
('EXP-2026-000007',1,'SeguridadHigiene','Evaluación riesgos ergonómicos',4,CURDATE()-INTERVAL 12 DAY,CURDATE()+INTERVAL 9 DAY,NULL,40,'Coordinar visita','Abierto','green',NOW(),NOW()),
('EXP-2026-000008',2,'Capacitacion','Capacitación NOM-019',4,CURDATE()-INTERVAL 15 DAY,CURDATE()+INTERVAL 1 DAY,NULL,65,'Última sesión','En Riesgo','yellow',NOW(),NOW()),
('EXP-2026-000009',3,'Otro','Cierre de investigación interna',2,CURDATE()-INTERVAL 70 DAY,CURDATE()-INTERVAL 2 DAY,CURDATE()-INTERVAL 1 DAY,100,'Concluido','Cerrado','gray',NOW(),NOW()),
('EXP-2026-000010',1,'SeguridadHigiene','Matriz de cumplimiento',3,CURDATE()-INTERVAL 5 DAY,CURDATE()+INTERVAL 30 DAY,NULL,10,'Inicio','Abierto','green',NOW(),NOW());

INSERT INTO case_history (case_id,actor_user_id,porcentaje_avance,comentario,created_at)
SELECT id,2,porcentaje_avance,'Carga inicial demo',NOW() FROM cases;
