# OTP Verification System Setup Guide

## Overview
This OTP (One-Time Password) verification system allows existing users to securely access their QR codes via email verification. The system includes:

- **Email-based OTP verification**
- **Secure 6-digit codes with 10-minute expiration**
- **Professional email templates**
- **Database security with automatic cleanup**

## Files Created/Modified

### New Files:
1. `otp_verification.php` - Main OTP verification page
2. `email_config.php` - Email configuration and helper functions
3. `otp_table.sql` - Database table for OTP storage
4. `OTP_SETUP.md` - This setup guide

### Modified Files:
1. `register.php` - Updated to redirect to OTP verification
2. `generatedqr.php` - Added verification success message

## Setup Instructions

### 1. Database Setup
Run the SQL commands in `otp_table.sql` to create the OTP verification table:

```sql
-- Execute this in your MySQL database
SOURCE otp_table.sql;
```

Or manually create the table:
```sql
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
```

### 2. Email Configuration

#### Option A: Using PHP mail() function (Default)
The system uses PHP's built-in `mail()` function. Ensure your server is configured to send emails:

**For XAMPP:**
1. Open `php.ini` file
2. Find the `[mail function]` section
3. Configure SMTP settings:
```ini
[mail function]
SMTP=localhost
smtp_port=25
sendmail_from = srgedaya@usa.edu.ph
```

**For Production Servers:**
- Contact your hosting provider for SMTP configuration
- Or use a service like SendGrid, Mailgun, or AWS SES

#### Option B: Using SMTP (Recommended for Production)
1. Install PHPMailer: `composer require phpmailer/phpmailer`
2. Uncomment the SMTP function in `email_config.php`
3. Configure your SMTP settings in the function

### 3. Email Configuration File
Edit `email_config.php` to customize email settings:

```php
// Update these constants
define('EMAIL_FROM', 'noreply@yourdomain.com');
define('EMAIL_FROM_NAME', 'Your Company Name');
define('EMAIL_SUBJECT_PREFIX', 'Your App - ');
```

### 4. Testing the System

1. **Register a new user** through `register.php`
2. **Go to the registration page** and use the "Already Registered?" section
3. **Enter the email** of an existing user
4. **Click "Secure Access"** - this will redirect to OTP verification
5. **Enter the email** and click "Send Verification Code"
6. **Check the email** for the 6-digit code
7. **Enter the code** and click "Verify & Access QR Code"

### 5. Security Features

#### Automatic Cleanup
The system automatically:
- Expires OTPs after 10 minutes
- Deletes used OTPs after verification
- Prevents OTP reuse

#### Manual Cleanup (Optional)
Run this SQL periodically to clean expired OTPs:
```sql
DELETE FROM otp_verification WHERE expires_at < NOW();
```

#### Rate Limiting (Recommended)
Add rate limiting to prevent abuse:
```php
// Add to otp_verification.php before OTP generation
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM otp_verification WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_assoc()['count'];

if ($count >= 5) {
    $message = '<div class="alert error"><i class="fas fa-exclamation-circle"></i> Too many attempts. Please try again later.</div>';
    // Don't generate OTP
}
```

### 6. Customization

#### Email Template
Edit the HTML template in `email_config.php` function `sendOTPEmail()` to match your branding.

#### OTP Expiration Time
Change the expiration time in `otp_verification.php`:
```php
$expires = date('Y-m-d H:i:s', strtotime('+10 minutes')); // Change 10 to desired minutes
```

#### OTP Length
Change OTP length in `otp_verification.php`:
```php
$otp = sprintf("%06d", mt_rand(100000, 999999)); // Change 6 to desired length
```

### 7. Troubleshooting

#### Emails Not Sending
1. Check server mail configuration
2. Verify `email_config.php` settings
3. Check server error logs
4. Test with a simple `mail()` function

#### OTP Not Working
1. Verify database table exists
2. Check database connection
3. Ensure session is working
4. Verify email format and validation

#### Database Errors
1. Check table structure matches `otp_table.sql`
2. Verify foreign key constraints
3. Check database permissions

### 8. Production Considerations

#### Security
- Use HTTPS in production
- Implement rate limiting
- Monitor failed attempts
- Use environment variables for sensitive data

#### Performance
- Add database indexes for large datasets
- Implement caching if needed
- Monitor email delivery rates

#### Monitoring
- Log OTP generation and verification attempts
- Monitor email delivery success rates
- Track user access patterns

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Verify all files are properly uploaded
3. Test with a fresh database
4. Check server error logs

## Files Structure
```
manifestlink/
├── otp_verification.php      # Main OTP page
├── email_config.php          # Email configuration
├── otp_table.sql            # Database setup
├── register.php             # Modified registration
├── generatedqr.php          # Modified QR display
├── connect.php              # Database connection
└── OTP_SETUP.md            # This guide
```

The OTP system is now ready to use! Users can securely access their QR codes through email verification. 