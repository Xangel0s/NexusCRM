-- Create Nexus database and load schema
CREATE DATABASE IF NOT EXISTS `nexus` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `nexus`;

-- Core schema and seeds
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(120) NULL,
  password_hash VARCHAR(255) NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO roles (id,name) VALUES (1,'admin'),(2,'backdata_manager'),(3,'backdata'),(4,'seller');
INSERT INTO users (role_id,name,username,email,password_hash,active)
VALUES (1,'Administrador','admin','admin@nexus.local', '$2y$10$8PU4b9zYVqk0nZz7Qm3xUeQJ0wz5o1o8qX7u0xgI1k4n2m2q9vU2e',1)
ON DUPLICATE KEY UPDATE username=username;
-- password: admin12345

-- Leads core
CREATE TABLE IF NOT EXISTS leads (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NULL,
  phone VARCHAR(30) NOT NULL,
  phone_e164 VARCHAR(20) NULL,
  email VARCHAR(120) NULL,
  source_name VARCHAR(100) NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'new',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_leads_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS lead_assignments (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  lead_id BIGINT NOT NULL,
  seller_id INT NOT NULL,
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
  FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE RESTRICT,
  KEY idx_seller_assigned (seller_id, assigned_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS lead_activities (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  lead_id BIGINT NOT NULL,
  user_id INT NOT NULL,
  status VARCHAR(40) NOT NULL,
  note TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
  KEY idx_lead_time (lead_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS assign_operations (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  operator_id INT NOT NULL,
  seller_id INT NOT NULL,
  qty INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (operator_id) REFERENCES users(id) ON DELETE RESTRICT,
  FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Import batches (Bases)
CREATE TABLE IF NOT EXISTS import_batches (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  tags VARCHAR(200) NULL,
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  archived_at TIMESTAMP NULL,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
  KEY idx_created (created_at),
  KEY idx_archived (archived_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Alter leads to link with batches
ALTER TABLE leads
  ADD COLUMN batch_id BIGINT NULL,
  ADD COLUMN imported_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  ADD KEY idx_batch_created (batch_id, created_at);

ALTER TABLE leads
  ADD CONSTRAINT fk_leads_batch
  FOREIGN KEY (batch_id) REFERENCES import_batches(id)
  ON DELETE SET NULL;