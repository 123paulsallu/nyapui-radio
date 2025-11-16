-- ============================================
-- SQL for XAMPP Local MySQL Database
-- ============================================
-- Run this in phpMyAdmin (local XAMPP) to create the headers table

-- Step 1: Create the database (if it doesn't exist)
CREATE DATABASE IF NOT EXISTS `nyapui` 
  DEFAULT CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

-- Step 2: Select the database
USE `nyapui`;

-- Step 3: Create the headers table
CREATE TABLE IF NOT EXISTS `headers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 4 (optional): Create a users table for admin authentication
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 5 (optional): Insert a test admin user
-- username: admin
-- password: admin123
INSERT IGNORE INTO `users` (username, password, role) 
VALUES ('admin', 'admin123', 'super admin');

-- Done! You can now use the header management feature.
