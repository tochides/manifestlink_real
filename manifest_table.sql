-- Manifest Table for Boarding Records
-- This table tracks when passengers scan their QR codes at the port

CREATE TABLE `manifest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `scan_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `scan_location` varchar(100) DEFAULT 'Port Terminal',
  `scanned_by` varchar(100) DEFAULT 'Port Staff',
  `boarding_status` enum('scanned','boarded','departed') NOT NULL DEFAULT 'scanned',
  `vessel_name` varchar(100) DEFAULT NULL,
  `vessel_number` varchar(50) DEFAULT NULL,
  `destination` varchar(100) DEFAULT NULL,
  `departure_time` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `scan_time` (`scan_time`),
  KEY `boarding_status` (`boarding_status`),
  KEY `idx_user_scan_time` (`user_id`, `scan_time`),
  CONSTRAINT `fk_manifest_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample data for testing
INSERT INTO `manifest` (`user_id`, `scan_time`, `scan_location`, `scanned_by`, `boarding_status`, `vessel_name`, `vessel_number`, `destination`, `departure_time`, `notes`) VALUES
(12, '2025-01-07 08:30:00', 'Port Terminal 1', 'Staff Member 1', 'scanned', 'MV Guimaras Express', 'GE-001', 'Iloilo City', '2025-01-07 09:00:00', 'Passenger scanned successfully'),
(13, '2025-01-07 08:35:00', 'Port Terminal 1', 'Staff Member 1', 'boarded', 'MV Guimaras Express', 'GE-001', 'Iloilo City', '2025-01-07 09:00:00', 'Passenger boarded vessel'),
(14, '2025-01-07 08:40:00', 'Port Terminal 2', 'Staff Member 2', 'scanned', 'MV Guimaras Express', 'GE-001', 'Iloilo City', '2025-01-07 09:00:00', 'Passenger scanned successfully'),
(16, '2025-01-07 08:45:00', 'Port Terminal 1', 'Staff Member 1', 'boarded', 'MV Guimaras Express', 'GE-001', 'Iloilo City', '2025-01-07 09:00:00', 'Passenger boarded vessel'),
(17, '2025-01-07 08:50:00', 'Port Terminal 2', 'Staff Member 2', 'scanned', 'MV Guimaras Express', 'GE-001', 'Iloilo City', '2025-01-07 09:00:00', 'Passenger scanned successfully'),
(18, '2025-01-07 08:55:00', 'Port Terminal 1', 'Staff Member 1', 'boarded', 'MV Guimaras Express', 'GE-001', 'Iloilo City', '2025-01-07 09:00:00', 'Passenger boarded vessel'),
(21, '2025-01-07 09:00:00', 'Port Terminal 2', 'Staff Member 2', 'departed', 'MV Guimaras Express', 'GE-001', 'Iloilo City', '2025-01-07 09:00:00', 'Vessel departed with passenger'),
(22, '2025-01-07 09:05:00', 'Port Terminal 1', 'Staff Member 1', 'scanned', 'MV Guimaras Express', 'GE-001', 'Iloilo City', '2025-01-07 09:00:00', 'Passenger scanned successfully'),
(23, '2025-01-07 09:10:00', 'Port Terminal 2', 'Staff Member 2', 'boarded', 'MV Guimaras Express', 'GE-001', 'Iloilo City', '2025-01-07 09:00:00', 'Passenger boarded vessel'),
(24, '2025-01-07 09:15:00', 'Port Terminal 1', 'Staff Member 1', 'scanned', 'MV Guimaras Express', 'GE-001', 'Iloilo City', '2025-01-07 09:00:00', 'Passenger scanned successfully'); 