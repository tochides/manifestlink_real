-- OTP Verification Table
-- This table stores temporary verification codes for existing users to access their QR codes

CREATE TABLE IF NOT EXISTS `otp_verification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_otp` (`email`, `otp`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`),
  CONSTRAINT `fk_otp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index for faster OTP lookups
CREATE INDEX idx_otp_email_expires ON otp_verification (email, expires_at);

-- Clean up expired OTPs (optional - you can run this periodically)
-- DELETE FROM otp_verification WHERE expires_at < NOW(); 