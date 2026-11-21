-- Inventory Management System schema (MySQL 8)
CREATE DATABASE IF NOT EXISTS inventory_management_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventory_management_system;

CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,              -- bcrypt hash (password_hash)
    email       VARCHAR(255) NOT NULL,
    role        ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    full_name   VARCHAR(100) NOT NULL DEFAULT '',
    created_at  DATETIME NOT NULL,
    updated_at  DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS products (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150)  NOT NULL,
    price       DECIMAL(10,2) NOT NULL DEFAULT 0,
    stock       INT           NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL,
    updated_at  DATETIME NOT NULL
);

-- Self-registration always creates 'staff' accounts. To make an admin:
-- UPDATE users SET role = 'admin' WHERE username = 'your-username';
