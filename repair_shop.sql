-- Ready-to-import schema for phpMyAdmin
-- Creates the DB (if allowed), selects it, then creates tables.
CREATE DATABASE IF NOT EXISTS repair_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE repair_shop;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS devices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(190) NOT NULL,
  device_type VARCHAR(190) NOT NULL,
  serial_number VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(40) NOT NULL,
  problem TEXT,
  status VARCHAR(60) DEFAULT 'جاري الإصلاح',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  receipt_date DATE NULL,
  public_token VARCHAR(64) UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Note: No admin is seeded here. On first login, the app will auto-create:
-- username: admin, password: admin123
