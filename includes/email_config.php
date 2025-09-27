<?php
/**
 * Email Configuration for PHPMailer
 * Update these settings with your actual SMTP credentials
 */

return [
    // SMTP Server Configuration
    'smtp_host' => 'smtp.gmail.com',           // Gmail SMTP server
    'smtp_port' => 587,                        // Gmail SMTP port for STARTTLS
    'smtp_secure' => 'tls',                    // Encryption type: 'tls' or 'ssl'
    
    // SMTP Authentication
    'smtp_username' => 'kinrequim@gmail.com', // ⚠️ CHANGE THIS to your Gmail address
    'smtp_password' => 'tpwk enfi dohd uwoh',    // ⚠️ CHANGE THIS to your Gmail App Password
    
    // Sender Information
    'from_email' => 'noreply@peakph.com',      // From email address
    'from_name' => 'PeakPH Commerce',          // From name
    'reply_to' => 'support@peakph.com',        // Reply-to email
    'reply_name' => 'PeakPH Support',          // Reply-to name
    
    // Email Settings
    'charset' => 'UTF-8',
    'timeout' => 30,                           // Connection timeout in seconds
        // Debug Configuration (only enable in development)
    'debug' => 0,                             // Debug level (0=off, 1=client, 2=server, 3=connection, 4=lowlevel)
    
    // Alternative SMTP Servers (uncomment and configure as needed)
    
    // For Outlook/Hotmail:
    // 'smtp_host' => 'smtp-mail.outlook.com',
    // 'smtp_port' => 587,
    // 'smtp_secure' => 'tls',
    
    // For Yahoo Mail:
    // 'smtp_host' => 'smtp.mail.yahoo.com',
    // 'smtp_port' => 587,
    // 'smtp_secure' => 'tls',
    
    // For custom SMTP (e.g., hosting provider):
    // 'smtp_host' => 'your-smtp-server.com',
    // 'smtp_port' => 587,
    // 'smtp_secure' => 'tls',
];

/*
📧 GMAIL SETUP INSTRUCTIONS:

1. Enable 2-Factor Authentication on your Gmail account
2. Generate an App Password:
   - Go to Google Account settings
   - Security → 2-Step Verification
   - App passwords → Generate
   - Select "Mail" and "Other (custom name)"
   - Copy the 16-character password
3. Update 'smtp_username' with your Gmail address
4. Update 'smtp_password' with the App Password (not your regular password)

⚠️ SECURITY NOTES:
- Never commit real credentials to version control
- Use environment variables in production
- Consider using a dedicated email service like SendGrid, Mailgun, or AWS SES for production
- Regularly rotate your App Passwords
*/
?>