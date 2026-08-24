-- ============================================================
--  Construction Material Management System - Database Schema
--  Import this file in phpMyAdmin, or let config/db.php build
--  the database automatically on first run.
-- ============================================================

CREATE DATABASE IF NOT EXISTS `construction_db`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE `construction_db`;

-- ------------------------------------------------------------
--  Table: users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `full_name`     VARCHAR(50)  NOT NULL,
    `email`         VARCHAR(100) NOT NULL UNIQUE,
    `password`      VARCHAR(255) NOT NULL,
    `phone`         VARCHAR(11)  NOT NULL,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
--  Table: suppliers  (the prototype uses one predefined demo supplier)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `suppliers` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `supplier_name` VARCHAR(100) NOT NULL,
    `contact_email` VARCHAR(100) DEFAULT NULL,
    `phone`         VARCHAR(11)  DEFAULT NULL,
    `address`       VARCHAR(255) DEFAULT NULL,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
--  Table: materials
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `materials` (
    `id`                 INT AUTO_INCREMENT PRIMARY KEY,
    `supplier_id`        INT            NOT NULL,
    `material_name`      VARCHAR(100)   NOT NULL,
    `category`           VARCHAR(50)    NOT NULL,
    `unit_price`         DECIMAL(12,2)  NOT NULL,
    `available_quantity` DECIMAL(12,2)  NOT NULL DEFAULT 0,
    `unit_of_measurement` VARCHAR(30)   NOT NULL,
    `description`        VARCHAR(500)   DEFAULT NULL,
    `created_by`         INT            DEFAULT NULL,
    `created_at`         DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_material_name` (`material_name`),
    CONSTRAINT `fk_material_supplier` FOREIGN KEY (`supplier_id`)
        REFERENCES `suppliers`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_material_user` FOREIGN KEY (`created_by`)
        REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
--  Seed: the predefined demo supplier (id = 1)
-- ------------------------------------------------------------
INSERT INTO `suppliers` (`id`, `supplier_name`, `contact_email`, `phone`, `address`)
SELECT 1, 'Demo Construction Suppliers Pvt. Ltd.', 'sales@demosuppliers.com',
       '03001234567', 'Plot 21, Industrial Area, Lahore'
WHERE NOT EXISTS (SELECT 1 FROM `suppliers` WHERE `id` = 1);
