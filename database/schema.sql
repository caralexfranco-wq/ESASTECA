CREATE DATABASE IF NOT EXISTS expedientes_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE expedientes_app;

CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(180) NOT NULL UNIQUE,
  phone VARCHAR(40) NULL,
  password_hash VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id),
  INDEX idx_users_role (role_id)
) ENGINE=InnoDB;

CREATE TABLE clients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('oficina','empresa','gasolinera') NOT NULL DEFAULT 'empresa',
  razon_social VARCHAR(180) NOT NULL,
  rfc VARCHAR(20) NULL,
  contacto VARCHAR(150) NULL,
  email VARCHAR(180) NULL,
  phone VARCHAR(40) NULL,
  address VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  INDEX idx_clients_active (is_active)
) ENGINE=InnoDB;

CREATE TABLE cases (
  id INT AUTO_INCREMENT PRIMARY KEY,
  folio VARCHAR(30) NOT NULL UNIQUE,
  client_id INT NOT NULL,
  asunto_tipo ENUM('SeguridadHigiene','Capacitacion','Otro') NOT NULL,
  descripcion VARCHAR(255) NOT NULL,
  responsable_user_id INT NOT NULL,
  fecha_inicio DATE NOT NULL,
  fecha_vencimiento DATE NOT NULL,
  fecha_termino_real DATE NULL,
  porcentaje_avance INT NOT NULL DEFAULT 0,
  notas TEXT NULL,
  status ENUM('Abierto','En Riesgo','Vencido','Cerrado') NOT NULL DEFAULT 'Abierto',
  last_traffic_light ENUM('green','yellow','red','gray') NOT NULL DEFAULT 'green',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_cases_client FOREIGN KEY (client_id) REFERENCES clients(id),
  CONSTRAINT fk_cases_user FOREIGN KEY (responsable_user_id) REFERENCES users(id),
  INDEX idx_cases_due (fecha_vencimiento),
  INDEX idx_cases_status (status)
) ENGINE=InnoDB;

CREATE TABLE case_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  case_id INT NOT NULL,
  actor_user_id INT NOT NULL,
  porcentaje_avance INT NOT NULL DEFAULT 0,
  comentario VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_hist_case FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
  CONSTRAINT fk_hist_user FOREIGN KEY (actor_user_id) REFERENCES users(id),
  INDEX idx_hist_case (case_id)
) ENGINE=InnoDB;

CREATE TABLE settings (
  id INT PRIMARY KEY,
  warning_days INT NOT NULL DEFAULT 7,
  digest_time TIME NOT NULL DEFAULT '09:00:00',
  cooldown_hours INT NOT NULL DEFAULT 24,
  whatsapp_enabled TINYINT(1) NOT NULL DEFAULT 0,
  email_enabled TINYINT(1) NOT NULL DEFAULT 1,
  updated_at DATETIME NOT NULL
) ENGINE=InnoDB;

CREATE TABLE audit_log (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  actor_user_id INT NULL,
  entity VARCHAR(50) NOT NULL,
  entity_id INT NOT NULL,
  action VARCHAR(40) NOT NULL,
  diff_json JSON NULL,
  ip VARCHAR(64) NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_audit_user FOREIGN KEY (actor_user_id) REFERENCES users(id),
  INDEX idx_audit_entity (entity, entity_id)
) ENGINE=InnoDB;

CREATE TABLE notification_log (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  case_id INT NULL,
  user_id INT NULL,
  channel ENUM('email','whatsapp') NOT NULL,
  to_address VARCHAR(200) NOT NULL,
  message_summary VARCHAR(255) NOT NULL,
  status VARCHAR(30) NOT NULL,
  provider_response TEXT NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_n_case FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE SET NULL,
  CONSTRAINT fk_n_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_n_case_created (case_id, created_at)
) ENGINE=InnoDB;
