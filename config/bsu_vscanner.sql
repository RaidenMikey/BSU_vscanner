-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 14, 2025 at 12:15 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bsu_vscanner`
--

-- --------------------------------------------------------

--
-- Table structure for table `entry_logs`
--

CREATE TABLE `entry_logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `vehicle_id` int(11) UNSIGNED NOT NULL,
  `guard_id` int(11) UNSIGNED NOT NULL,
  `action` enum('allowed','denied') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `entry_logs`
--

INSERT INTO `entry_logs` (`id`, `vehicle_id`, `guard_id`, `action`, `created_at`) VALUES
(1, 1, 3, 'allowed', '2025-11-11 15:35:11'),
(2, 1, 3, 'allowed', '2025-11-11 15:35:45'),
(3, 2, 3, 'allowed', '2025-11-11 15:41:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','guard','admin') NOT NULL DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `student_id`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Michael', '22-75021@g.batstate-u.edu.ph', '22-75021', '$2y$10$NbzZRbkBnMZd70sP2xppYORopsKq3RPlAR4Vd1ZC3f1sySv1Q7P1O', 'student', '2025-11-07 13:29:00', '2025-11-07 13:29:00'),
(2, 'Michael', 'mikey.ramos2000@gmail.com', '22-75022', '$2y$10$FkyubroDWe732YSYcwszSOfdPRyxwnxdwHkAO27jBPBleDE6AcJfe', 'guard', '2025-11-07 15:12:38', '2025-11-07 15:12:38'),
(3, 'Michael', '22-75023@g.batstate-u.edu.ph', '22-75023', '$2y$10$sOMUFz/t0hGZyp3jkEzl2u8IvzClXep6zyl7bpQJbikmudRVyJeha', 'guard', '2025-11-07 15:15:06', '2025-11-07 15:15:06'),
(4, 'Joanna Mae Perez', 'joannaperez292003@gmail.com', '12345', '$2y$10$rzdbxFCUzJxPUYATxSUeJulml7wWpVGxvSM95YtMyJGEkK153/URO', 'guard', '2025-11-07 15:33:33', '2025-11-07 15:33:33'),
(5, 'Joanna Mae Perez', '22-73989@g.batstate-u.edu.ph', '73989', '$2y$10$YDapC6hzE8TZZ2nkN3EB5.RwaN7bcz1qg1M0HNcW3MjgqF8IerZ9e', 'guard', '2025-11-07 15:36:21', '2025-11-07 15:36:21'),
(6, 'Administrator', 'admin', 'ADMIN001', '$2y$10$sKOW/o.yL7JDw92cbs7LAeVySA.8cML2DBOMYfD7ZbnQ4YdV2Sgzm', 'admin', '2025-11-11 15:23:19', '2025-11-11 15:23:19');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `vehicle_type` varchar(50) NOT NULL DEFAULT '',
  `license_plate` varchar(20) NOT NULL,
  `make` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  `color` varchar(50) NOT NULL,
  `driver_license_no` varchar(100) NOT NULL DEFAULT '',
  `driver_license_image` varchar(255) DEFAULT NULL,
  `or_image` varchar(255) DEFAULT NULL,
  `cr_image` varchar(255) DEFAULT NULL,
  `qr_code_path` varchar(255) DEFAULT NULL,
  `qr_code_data` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `user_id`, `vehicle_type`, `license_plate`, `make`, `model`, `color`, `driver_license_no`, `driver_license_image`, `or_image`, `cr_image`, `qr_code_path`, `qr_code_data`, `status`, `registered_at`, `updated_at`) VALUES
(1, 1, 'motorcycle', 'ASD-1234', 'Honda', 'Dio', 'Orange', 'ASD-123123', 'uploads/vehicles/1/driver_license_1762874288_7ed18524.png', 'uploads/vehicles/1/official_receipt_1762874288_edba0f14.png', 'uploads/vehicles/1/certificate_registration_1762874288_66e2f09d.png', 'uploads/vehicles/1/qr_vehicle_1_1762874288.png', 'VEH-2025-0001|STU-2275021|269782ece2f3bad120ac9a620bac598e0a186ffe384d37a85f067694929a0985', 'approved', '2025-11-11 15:18:08', '2025-11-11 15:39:08'),
(2, 1, 'motorcycle', 'LKJ-12345', 'Honda', 'Winner X', 'Red', 'ASD-123123', 'uploads/vehicles/1/driver_license_1762875520_6a60cde9.png', 'uploads/vehicles/1/official_receipt_1762875520_1e56fd41.png', 'uploads/vehicles/1/certificate_registration_1762875520_5af21cd6.png', 'uploads/vehicles/1/qr_vehicle_2_1762875520.png', 'VEH-2025-0002|STU-2275021|84cb17ee7d95cc6e12de91a39841c7c213f4a40d09abeaa2263c326289a4b036', 'approved', '2025-11-11 15:38:40', '2025-11-12 01:16:48'),
(3, 1, 'motorcycle', 'QWE-1234', 'Honda', 'Click', 'Red', 'ASD-123123', 'uploads/vehicles/1/driver_license_1762911250_e31ba037.png', 'uploads/vehicles/1/official_receipt_1762911250_041f9ee4.png', 'uploads/vehicles/1/certificate_registration_1762911250_e80fa3fe.png', 'uploads/vehicles/1/qr_vehicle_3_1762911250.png', 'VEH-2025-0003|STU-2275021', 'approved', '2025-11-12 01:34:10', '2025-11-12 02:07:53'),
(5, 1, 'car', 'CVB-12345', 'Honda', 'Winner X', 'Orange', 'ASD-123123', 'uploads/vehicles/1/driver_license_1762913580_329f8617.png', 'uploads/vehicles/1/official_receipt_1762913580_1781eee8.png', 'uploads/vehicles/1/certificate_registration_1762913580_583f4e65.png', 'uploads/vehicles/1/qr_vehicle_5_1762913581.png', 'VEH-2025-0005|STU-2275021', 'pending', '2025-11-12 02:13:00', '2025-11-12 02:13:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `entry_logs`
--
ALTER TABLE `entry_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `guard_id` (`guard_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `license_plate` (`license_plate`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `entry_logs`
--
ALTER TABLE `entry_logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
