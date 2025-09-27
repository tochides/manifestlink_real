-- ManifestLink Complete Database Dump
-- Generated: January 7, 2025
-- Database: manifestlink
-- Server: MariaDB 10.4.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `manifestlink`
--
CREATE DATABASE IF NOT EXISTS `manifestlink` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `manifestlink`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `age` int(11) NOT NULL,
  `sex` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `contact_number`, `email`, `address`, `created_at`, `age`, `sex`) VALUES
(12, 'Rei Jan Melocotones', '09956572301', 'rjmelocotones@gmail.com', 'Bugasong', '2025-06-23 13:48:21', 22, 'Male'),
(13, 'shadd reign gedaya', '09691019337', 'leysacreepers@gmail.com', 'illoilo', '2025-06-24 12:56:18', 22, 'Male'),
(14, 'gavin jame', '097465834674', 'wew@gmail.com', 'iloilo', '2025-06-24 14:19:32', 22, 'Male'),
(16, 'shadd reign gedaya', '09691019337', 'gedaya986@gmail.com', 'iloilo', '2025-06-25 01:31:23', 22, 'Male'),
(17, 'shadd reign gedaya', '09691019337', 'gierza.dave@icloud.com', 'ilolio', '2025-06-25 04:50:51', 22, 'Male'),
(18, 'xavier angelo', '09691534534', 'gedayakenjireh@gmail.com', 'iloilo', '2025-06-25 04:58:49', 22, 'Male'),
(21, 'plantiomer', '09691019337', 'srgedaya@usa.edu.ph', 'iloilo', '2025-06-25 06:41:33', 22, 'Male'),
(22, 'jamess bond', '09756478743', 'ben@gmail.com', 'iloilo', '2025-06-25 06:54:09', 22, 'Male'),
(23, 'reijan', '091256384565', 'reijanmelocotones15@gmail.com', 'iloilo', '2025-06-26 04:20:47', 22, 'Male'),
(24, 'john gabriel', '09691534534', 'jgjavier@usa.edu.ph', 'iloilo', '2025-06-26 04:25:25', 22, 'Female');

-- --------------------------------------------------------

--
-- Table structure for table `qr_codes`
--

CREATE TABLE `qr_codes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `qr_data` text NOT NULL,
  `qr_image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `qr_codes`
--

INSERT INTO `qr_codes` (`id`, `user_id`, `qr_data`, `qr_image_path`, `created_at`) VALUES
(7, 12, 'Full Name: Rei Jan Melocotones\nContact Number: 09956572301\nEmail: rjmelocotones@gmail.com\nAddress: Bugasong\nAge: 22\nSex: Male', 'qrcodes/user_12.png', '2025-06-23 13:48:21'),
(8, 13, 'Full Name: shadd reign gedaya\nContact Number: 09691019337\nEmail: leysacreepers@gmail.com\nAddress: illoilo\nAge: 22\nSex: Male', 'qrcodes/user_13.png', '2025-06-24 12:56:18'),
(9, 14, 'Full Name: gavin jame\nContact Number: 097465834674\nEmail: wew@gmail.com\nAddress: iloilo\nAge: 22\nSex: Male', 'qrcodes/user_14.png', '2025-06-24 14:19:32'),
(10, 16, 'Full Name: shadd reign gedaya\nContact Number: 09691019337\nEmail: gedaya986@gmail.com\nAddress: iloilo\nAge: 22\nSex: Male', 'qrcodes/user_16.png', '2025-06-25 01:31:23'),
(11, 17, 'Full Name: shadd reign gedaya\nContact Number: 09691019337\nEmail: gierza.dave@icloud.com\nAddress: ilolio\nAge: 22\nSex: Male', 'qrcodes/user_17.png', '2025-06-25 04:50:51'),
(12, 18, 'Full Name: xavier angelo\nContact Number: 09691534534\nEmail: gedayakenjireh@gmail.com\nAddress: iloilo\nAge: 22\nSex: Male', 'qrcodes/user_18.png', '2025-06-25 04:58:49'),
(13, 21, 'Full Name: plantiomer\nContact Number: 09691019337\nEmail: srgedaya@usa.edu.ph\nAddress: iloilo\nAge: 22\nSex: Male', 'qrcodes/user_21.png', '2025-06-25 06:41:33'),
(14, 22, 'Full Name: jamess bond\nContact Number: 09756478743\nEmail: ben@gmail.com\nAddress: iloilo\nAge: 22\nSex: Male', 'qrcodes/user_22.png', '2025-06-25 06:54:09'),
(15, 23, 'Full Name: reijan\nContact Number: 091256384565\nEmail: reijanmelocotones15@gmail.com\nAddress: iloilo\nAge: 22\nSex: Male', 'qrcodes/user_23.png', '2025-06-26 04:20:47'),
(16, 24, 'Full Name: john gabriel\nContact Number: 09691534534\nEmail: jgjavier@usa.edu.ph\nAddress: iloilo\nAge: 22\nSex: Female', 'qrcodes/user_24.png', '2025-06-26 04:25:25');

-- --------------------------------------------------------

--
-- Table structure for table `otp_verification`
--

CREATE TABLE `otp_verification` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otp_verification`
--

INSERT INTO `otp_verification` (`id`, `user_id`, `email`, `otp`, `expires_at`, `created_at`, `updated_at`) VALUES
(6, 21, 'srgedaya@usa.edu.ph', '489425', '2025-06-26 06:21:14', '2025-06-26 04:11:14', '2025-06-26 04:11:14');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password_hash`, `email`, `created_at`, `last_login`, `is_active`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@manifestlink.com', '2025-01-07 12:00:00', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'site_name', 'ManifestLink', 'Website name', '2025-01-07 12:00:00'),
(2, 'site_description', 'Maritime Passenger Management System', 'Website description', '2025-01-07 12:00:00'),
(3, 'email_from', 'noreply@manifestlink.com', 'Default sender email', '2025-01-07 12:00:00'),
(4, 'email_from_name', 'ManifestLink System', 'Default sender name', '2025-01-07 12:00:00'),
(5, 'qr_code_size', '8', 'QR code size (1-10)', '2025-01-07 12:00:00'),
(6, 'qr_code_error_level', 'L', 'QR code error correction level (L, M, Q, H)', '2025-01-07 12:00:00'),
(7, 'otp_expiry_minutes', '10', 'OTP expiration time in minutes', '2025-01-07 12:00:00'),
(8, 'max_login_attempts', '5', 'Maximum login attempts before lockout', '2025-01-07 12:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `username` varchar(50) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_full_name` (`full_name`);

--
-- Indexes for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `otp_verification`
--
ALTER TABLE `otp_verification`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_otp` (`email`,`otp`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `expires_at` (`expires_at`),
  ADD KEY `idx_otp_email_expires` (`email`,`expires_at`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip_attempt_time` (`ip_address`,`attempt_time`),
  ADD KEY `idx_username_attempt_time` (`username`,`attempt_time`);

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `idx_action_created` (`action`,`created_at`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `qr_codes`
--
ALTER TABLE `qr_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `otp_verification`
--
ALTER TABLE `otp_verification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD CONSTRAINT `qr_codes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `otp_verification`
--
ALTER TABLE `otp_verification`
  ADD CONSTRAINT `fk_otp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_activity_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */; 