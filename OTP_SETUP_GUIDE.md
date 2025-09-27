# 🏔️ PeakPH Commerce - OTP Verification System Setup Guide

## 📋 Overview

This guide will help you configure the complete OTP (One-Time Password) verification system for user signups in your PeakPH Commerce platform. The system provides enterprise-level security with email verification, rate limiting, and comprehensive error handling.

## 🚀 Quick Start

### 1. Email Configuration (REQUIRED)

**Edit `includes/email_config.php`** with your email provider settings:

```php
'smtp_username' => 'your_actual_email@gmail.com',  // Your Gmail address
'smtp_password' => 'your_16_char_app_password',    // Gmail App Password
```

#### For Gmail Setup:
1. Enable 2-Factor Authentication on your Gmail account
2. Generate an App Password:
   - Go to [Google Account Settings](https://myaccount.google.com)
   - Security → 2-Step Verification
   - App passwords → Generate
   - Select "Mail" and "Other (custom name)"
   - Copy the 16-character password
3. Update the config file with your credentials

### 2. Database Setup

The system automatically creates required tables:
- `otp_verifications` - Stores OTP codes and signup data
- `users` - Main user accounts table
- `rate_limits` - IP-based rate limiting
- `activity_logs` - Security audit logs

**No manual database setup required!** ✅

### 3. Test the System

1. Visit your website
2. Click the login/signup button
3. Switch to "Sign Up" 
4. Fill out the form and submit
5. Check your email for the OTP code
6. Enter the code to complete registration

## 📁 File Structure

### New Files Created:
```
📦 PeakPH_Commerce/
├── 📁 vendor/                          # PHPMailer installation
├── 📁 includes/
│   ├── 📄 OTPManager.php               # Core OTP management class
│   └── 📄 email_config.php             # Email SMTP configuration
├── 📁 components/
│   └── 📄 auth_modal_otp.js            # Enhanced JavaScript with OTP
├── 📄 signup_handler.php               # Step 1: Process signup, send OTP
├── 📄 verify_otp.php                   # Step 2: Verify OTP, create user
├── 📄 resend_otp.php                   # Resend OTP functionality
└── 📄 get_csrf_token.php               # CSRF token endpoint
```

### Modified Files:
```
📦 Modified Files/
├── 📄 components/auth_modal.php         # Added OTP verification form
├── 📄 Css/Global.css                   # Added OTP styling
└── 📄 index.php                        # Updated JavaScript reference
```

## 🔧 Configuration Options

### Email Providers

**Gmail (Default):**
```php
'smtp_host' => 'smtp.gmail.com',
'smtp_port' => 587,
'smtp_secure' => 'tls',
```

**Outlook/Hotmail:**
```php
'smtp_host' => 'smtp-mail.outlook.com',
'smtp_port' => 587,
'smtp_secure' => 'tls',
```

**Yahoo Mail:**
```php
'smtp_host' => 'smtp.mail.yahoo.com',
'smtp_port' => 587,
'smtp_secure' => 'tls',
```

### Security Settings

**OTP Expiration:** 5 minutes (300 seconds)
```php
private $otpExpiry = 300; // in OTPManager.php
```

**Rate Limiting:** 
- 5 signup attempts per 15 minutes per IP
- 3 OTP requests per 15 minutes per email
- 3 OTP verification attempts per code

**Session Security:** CSRF protection, secure sessions

## 🎨 User Experience Flow

### 1. Signup Form
- Enhanced form with loading states
- Real-time validation
- Secure AJAX submission

### 2. OTP Verification
- Beautiful countdown timer
- Auto-format 6-digit input
- Auto-submit when complete
- Resend functionality (30-second cooldown)
- Clear error/success messages

### 3. Account Creation
- Secure password hashing (Argon2ID)
- Automatic login after verification
- Activity logging for security

## 🛡️ Security Features

### ✅ Input Validation
- Email format validation
- Password strength requirements
- SQL injection prevention
- XSS protection

### ✅ Rate Limiting
- IP-based signup limits
- Email-based OTP limits
- Progressive delays

### ✅ CSRF Protection
- Token-based request validation
- Session security
- Secure headers

### ✅ Audit Logging
- All signup attempts logged
- IP address tracking
- User agent logging
- Failed attempt monitoring

## 🐛 Troubleshooting

### Common Issues:

**1. "Failed to send OTP email"**
- Check email credentials in `email_config.php`
- Verify Gmail App Password is correct
- Check server firewall allows port 587

**2. "Database connection error"**
- Ensure `includes/db.php` is properly configured
- Check MySQL service is running
- Verify database credentials

**3. "CSRF token invalid"**
- Clear browser cache/cookies
- Check session configuration
- Verify server time is correct

**4. OTP not received**
- Check spam/junk folders
- Verify email address is correct
- Check server logs for PHPMailer errors

### Debug Mode:
Enable debug in `email_config.php`:
```php
'debug' => 2, // Enable detailed SMTP debugging
```

## 📊 Database Schema

### `otp_verifications` Table:
```sql
- id (Primary Key)
- email (VARCHAR 255)
- otp_code (VARCHAR 6) 
- signup_data (TEXT JSON)
- attempts (INT)
- created_at (TIMESTAMP)
- expires_at (TIMESTAMP)
- is_verified (BOOLEAN)
- ip_address (VARCHAR 45)
- user_agent (TEXT)
```

### `users` Table:
```sql
- id (Primary Key)
- full_name (VARCHAR 100)
- email (VARCHAR 255 UNIQUE)
- password (VARCHAR 255) - Argon2ID hashed
- email_verified (BOOLEAN)
- is_active (BOOLEAN)
- created_at (TIMESTAMP)
```

## 🎯 Production Deployment

### Security Checklist:
- [ ] Update email credentials
- [ ] Set `'debug' => 0` in email config
- [ ] Use environment variables for secrets
- [ ] Enable HTTPS
- [ ] Set proper PHP error reporting
- [ ] Configure database backups
- [ ] Set up monitoring/alerts

### Performance Optimization:
- [ ] Enable database indexing
- [ ] Set up email queue for high volume
- [ ] Implement caching if needed
- [ ] Monitor rate limiting thresholds

## 🤝 Support

### Need Help?
1. Check the troubleshooting section
2. Review server error logs
3. Test with debug mode enabled
4. Verify all configuration files

### System Requirements:
- PHP 7.4+ (8.0+ recommended)
- MySQL 5.7+ / MariaDB 10.3+
- SMTP email service
- SSL/TLS support

## 🏆 Features Summary

✅ **Professional OTP System** - 6-digit codes with 5-minute expiration  
✅ **Beautiful UI/UX** - Animated modals with loading states  
✅ **Enterprise Security** - Rate limiting, CSRF protection, audit logs  
✅ **Email Templates** - Branded HTML emails with fallback text  
✅ **Auto-Configuration** - Database tables created automatically  
✅ **Production Ready** - Comprehensive error handling and logging  
✅ **Mobile Friendly** - Responsive design with touch support  
✅ **Developer Friendly** - Clean, documented, maintainable code  

Your OTP verification system is now ready to provide secure, professional user registration for PeakPH Commerce! 🎉