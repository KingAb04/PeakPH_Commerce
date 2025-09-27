<?php
/**
 * OTP Management System
 * Handles OTP generation, storage, validation, and email sending
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class OTPManager {
    private $conn;
    private $otpExpiry = 300; // 5 minutes in seconds
    private $maxAttempts = 3; // Maximum OTP attempts
    
    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->createOTPTable();
    }
    
    /**
     * Create OTP table if it doesn't exist
     */
    private function createOTPTable() {
        $sql = "CREATE TABLE IF NOT EXISTS otp_verifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            otp_code VARCHAR(6) NOT NULL,
            signup_data TEXT NOT NULL,
            attempts INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME NOT NULL,
            is_verified TINYINT(1) DEFAULT 0,
            ip_address VARCHAR(45),
            user_agent TEXT,
            INDEX idx_email (email),
            INDEX idx_otp (otp_code),
            INDEX idx_expires (expires_at)
        )";
        
        $this->conn->query($sql);
    }
    
    /**
     * Generate a secure 6-digit OTP
     */
    public function generateOTP() {
        return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Store OTP and signup data temporarily
     */
    public function storeOTP($email, $signupData) {
        // Clean expired OTPs first
        $this->cleanExpiredOTPs();
        
        // Check if user has too many recent attempts
        if ($this->hasExceededRateLimit($email)) {
            return [
                'success' => false,
                'message' => 'Too many attempts. Please wait 15 minutes before trying again.',
                'error_code' => 'RATE_LIMIT_EXCEEDED'
            ];
        }
        
        // Generate new OTP
        $otp = $this->generateOTP();
        $signupDataJson = json_encode($signupData);
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Delete any existing OTP for this email
        $deleteStmt = $this->conn->prepare("DELETE FROM otp_verifications WHERE email = ?");
        $deleteStmt->bind_param("s", $email);
        $deleteStmt->execute();
        
        // Insert new OTP using database time functions for consistency
        $stmt = $this->conn->prepare("
            INSERT INTO otp_verifications 
            (email, otp_code, signup_data, expires_at, ip_address, user_agent) 
            VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE), ?, ?)
        ");
        
        $stmt->bind_param("sssss", $email, $otp, $signupDataJson, $ipAddress, $userAgent);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'otp' => $otp,
                'expires_in' => $this->otpExpiry,
                'message' => 'OTP generated successfully'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to generate OTP. Please try again.',
                'error_code' => 'DATABASE_ERROR'
            ];
        }
    }
    
    /**
     * Verify OTP and return signup data
     */
    public function verifyOTP($email, $otpCode) {
        // Clean expired OTPs first
        $this->cleanExpiredOTPs();
        
        // First check if there's any OTP for this email (regardless of verification status)
        $checkStmt = $this->conn->prepare("
            SELECT id, otp_code, signup_data, attempts, expires_at, is_verified 
            FROM otp_verifications 
            WHERE email = ?
            ORDER BY created_at DESC LIMIT 1
        ");
        
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if (!$checkResult->num_rows) {
            return [
                'success' => false,
                'message' => 'No OTP found for this email. Please request a new one.',
                'error_code' => 'OTP_NOT_FOUND'
            ];
        }
        
        $checkRow = $checkResult->fetch_assoc();
        
        // Check if OTP is already verified
        if ($checkRow['is_verified'] == 1) {
            return [
                'success' => false,
                'message' => 'This OTP has already been used. Please request a new one.',
                'error_code' => 'OTP_ALREADY_USED'
            ];
        }
        
        // Check if OTP is expired
        $expiresTimestamp = strtotime($checkRow['expires_at']);
        $currentTimestamp = time();
        
        if ($expiresTimestamp <= $currentTimestamp) {
            return [
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.',
                'error_code' => 'OTP_EXPIRED'
            ];
        }
        
        // Now get the valid, unverified OTP
        $stmt = $this->conn->prepare("
            SELECT id, otp_code, signup_data, attempts, expires_at, is_verified 
            FROM otp_verifications 
            WHERE email = ? AND expires_at > NOW() AND is_verified = 0
            ORDER BY created_at DESC LIMIT 1
        ");
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result->num_rows) {
            return [
                'success' => false,
                'message' => 'No valid OTP found. Please request a new one.',
                'error_code' => 'OTP_NOT_VALID'
            ];
        }
        
        $row = $result->fetch_assoc();
        
        // Check if max attempts exceeded
        if ($row['attempts'] >= $this->maxAttempts) {
            return [
                'success' => false,
                'message' => 'Maximum OTP attempts exceeded. Please request a new one.',
                'error_code' => 'MAX_ATTEMPTS_EXCEEDED'
            ];
        }
        
        // Increment attempts
        $updateStmt = $this->conn->prepare("
            UPDATE otp_verifications 
            SET attempts = attempts + 1 
            WHERE id = ?
        ");
        $updateStmt->bind_param("i", $row['id']);
        $updateStmt->execute();
        
        // Verify OTP
        if (hash_equals($row['otp_code'], $otpCode)) {
            // Mark as verified
            $verifyStmt = $this->conn->prepare("
                UPDATE otp_verifications 
                SET is_verified = 1 
                WHERE id = ?
            ");
            $verifyStmt->bind_param("i", $row['id']);
            $verifyStmt->execute();
            
            $signupData = json_decode($row['signup_data'], true);
            
            return [
                'success' => true,
                'signup_data' => $signupData,
                'message' => 'OTP verified successfully'
            ];
        } else {
            $attemptsLeft = $this->maxAttempts - ($row['attempts'] + 1);
            return [
                'success' => false,
                'message' => "Invalid OTP. {$attemptsLeft} attempts remaining.",
                'error_code' => 'INVALID_OTP',
                'attempts_left' => $attemptsLeft
            ];
        }
    }
    
    /**
     * Send OTP via email using PHPMailer
     */
    public function sendOTP($email, $otp, $fullName = '') {
        // Load email configuration
        $config = include __DIR__ . '/email_config.php';
        
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $config['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['smtp_username'];
            $mail->Password   = $config['smtp_password'];
            $mail->SMTPSecure = $config['smtp_secure'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $config['smtp_port'];
            $mail->CharSet    = $config['charset'];
            $mail->Timeout    = $config['timeout'];
            
        // Enable debug only in development
        if (isset($config['debug']) && $config['debug'] > 0 && defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
            $mail->SMTPDebug = $config['debug'];
            $mail->Debugoutput = function($str, $level) {
                error_log("PHPMailer Debug (Level $level): $str");
            };
        }            // Recipients
            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($email, $fullName);
            $mail->addReplyTo($config['reply_to'], $config['reply_name']);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = '🏔️ Your PeakPH Adventure Verification Code';
            
            $mail->Body = $this->getEmailTemplate($otp, $fullName);
            $mail->AltBody = "Your PeakPH verification code is: {$otp}. This code will expire in 5 minutes. If you didn't request this code, please ignore this email.";
            
            $mail->send();
            return [
                'success' => true,
                'message' => 'OTP sent successfully'
            ];
            
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $mail->ErrorInfo . " | Exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send OTP email. Please try again.',
                'error_code' => 'EMAIL_SEND_FAILED',
                'debug_info' => [
                    'phpmailer_error' => $mail->ErrorInfo,
                    'exception_message' => $e->getMessage(),
                    'smtp_host' => $config['smtp_host'],
                    'smtp_port' => $config['smtp_port'],
                    'smtp_secure' => $config['smtp_secure'],
                    'from_email' => $config['from_email']
                ]
            ];
        }
    }
    
    /**
     * Get HTML email template for OTP
     */
    private function getEmailTemplate($otp, $fullName) {
        $greeting = $fullName ? "Hi {$fullName}," : "Hi there,";
        
        return "
        <html>
        <head>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f5f5f5; }
                .container { max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { text-align: center; padding: 20px 0; background: linear-gradient(135deg, #245d4b, #2e765e); border-radius: 10px; color: white; margin-bottom: 30px; }
                .otp-box { background: #f8f9fa; border: 2px dashed #245d4b; border-radius: 10px; padding: 20px; text-align: center; margin: 20px 0; }
                .otp-code { font-size: 32px; font-weight: bold; color: #245d4b; letter-spacing: 5px; margin: 10px 0; }
                .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 14px; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 20px 0; color: #856404; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🏔️ PeakPH Adventure Awaits!</h1>
                    <p>Your verification code is ready</p>
                </div>
                
                <p>{$greeting}</p>
                <p>Welcome to the PeakPH outdoor community! You're just one step away from joining thousands of adventure enthusiasts.</p>
                
                <div class='otp-box'>
                    <h3>Your Verification Code:</h3>
                    <div class='otp-code'>{$otp}</div>
                    <p><strong>This code expires in 5 minutes</strong></p>
                </div>
                
                <p>Enter this code in the verification window to complete your account setup and start exploring our premium camping and hiking gear collection.</p>
                
                <div class='warning'>
                    <strong>⚠️ Security Notice:</strong> If you didn't request this verification code, please ignore this email. Your account remains secure.
                </div>
                
                <div class='footer'>
                    <p>🏕️ Happy Camping!<br>The PeakPH Team</p>
                    <p><small>This is an automated message. Please do not reply to this email.</small></p>
                </div>
            </div>
        </body>
        </html>";
    }
    

    
    /**
     * Clean expired OTPs
     */
    private function cleanExpiredOTPs() {
        $stmt = $this->conn->prepare("DELETE FROM otp_verifications WHERE expires_at < NOW()");
        $stmt->execute();
    }
    
    /**
     * Update stored OTP with new code and reset attempts
     */
    public function updateStoredOTP($email, $newOTP) {
        try {
            // Update the OTP code and reset attempts, extend expiration
            $stmt = $this->conn->prepare("
                UPDATE otp_verifications 
                SET otp_code = ?, 
                    attempts = 0, 
                    expires_at = DATE_ADD(NOW(), INTERVAL 5 MINUTE),
                    created_at = NOW()
                WHERE email = ? 
                AND is_verified = 0 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            
            $stmt->bind_param("ss", $newOTP, $email);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                return ['success' => true, 'message' => 'OTP updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update OTP'];
            }
            
        } catch (Exception $e) {
            error_log("Update OTP Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Mark OTP as used/verified
     */
    public function markOTPAsUsed($email) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE otp_verifications 
                SET is_verified = 1 
                WHERE email = ? 
                AND is_verified = 0
            ");
            
            $stmt->bind_param("s", $email);
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Mark OTP Used Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user has exceeded rate limit (make public)
     */
    public function hasExceededRateLimit($email) {
        // Create rate_limits table if it doesn't exist
        $this->conn->query("DROP TABLE IF EXISTS rate_limits");
        
        $sql = "CREATE TABLE rate_limits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            attempt_type VARCHAR(50) NOT NULL,
            attempts INT DEFAULT 1,
            last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME NOT NULL,
            ip_address VARCHAR(45),
            INDEX idx_email_type (email, attempt_type),
            INDEX idx_expires (expires_at)
        )";
        $this->conn->query($sql);
        
        // Clean expired rate limit records
        $this->conn->prepare("DELETE FROM rate_limits WHERE expires_at < NOW()")->execute();
        
        // Check current rate limit for OTP requests
        $stmt = $this->conn->prepare("
            SELECT attempts 
            FROM rate_limits 
            WHERE email = ? 
            AND attempt_type = 'otp_request' 
            AND expires_at > NOW()
        ");
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // First attempt, create record
            $stmt = $this->conn->prepare("
                INSERT INTO rate_limits (email, attempt_type, attempts, expires_at, ip_address) 
                VALUES (?, 'otp_request', 1, DATE_ADD(NOW(), INTERVAL 15 MINUTE), ?)
            ");
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $stmt->bind_param("ss", $email, $ipAddress);
            $stmt->execute();
            
            return false; // Not exceeded
        }
        
        $row = $result->fetch_assoc();
        
        if ($row['attempts'] >= 5) {
            return true; // Exceeded limit
        }
        
        // Increment attempts
        $stmt = $this->conn->prepare("
            UPDATE rate_limits 
            SET attempts = attempts + 1, last_attempt = NOW() 
            WHERE email = ? AND attempt_type = 'otp_request' AND expires_at > NOW()
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        return false; // Not exceeded yet
    }
    
    /**
     * Get configuration for email setup
     */
    public static function getEmailConfig() {
        return [
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_username' => 'your_email@gmail.com', // Change this
            'smtp_password' => 'your_app_password',    // Change this
            'from_email' => 'noreply@peakph.com',
            'from_name' => 'PeakPH Commerce',
            'reply_to' => 'support@peakph.com'
        ];
    }
}
?>