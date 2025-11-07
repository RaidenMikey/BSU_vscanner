-- BSU Vehicle Scanner Database Initialization
-- Database: bsu_vscanner

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `bsu_vscanner` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Use the database
USE `bsu_vscanner`;

-- Create users table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `student_id` VARCHAR(50) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('student','guard') NOT NULL DEFAULT 'student',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`),
    UNIQUE KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create vehicles table
CREATE TABLE IF NOT EXISTS `vehicles` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `license_plate` VARCHAR(20) NOT NULL,
    `make` VARCHAR(100) NOT NULL,
    `model` VARCHAR(100) NOT NULL,
    `color` VARCHAR(50) NOT NULL,
    `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `license_plate` (`license_plate`),
    KEY `user_id` (`user_id`),
    KEY `status` (`status`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
