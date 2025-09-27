<?php
/**
 * Signup Handler with OTP Verification
 * Step 1: Validate signup data and send OTP
 */

session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/OTPManager.php';

// CSRF Protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Verify CSRF token
$headers = getallheaders();
$csrfToken = $headers['X-CSRF-Token'] ?? $_POST['csrf_token'] ?? '';

if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

try {
    // Get and validate input
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    $errors = [];
    
    // Full name validation
    if (empty($fullName)) {
        $errors[] = 'Full name is required';
    } elseif (strlen($fullName) < 2) {
        $errors[] = 'Full name must be at least 2 characters long';
    } elseif (strlen($fullName) > 50) {
        $errors[] = 'Full name cannot exceed 50 characters';
    } elseif (!preg_match('/^[a-zA-Z\s\'.-]+$/', $fullName)) {
        $errors[] = 'Full name contains invalid characters';
    }
    
    // Email validation
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    } elseif (strlen($email) > 100) {
        $errors[] = 'Email cannot exceed 100 characters';
    }
    
    // Password validation
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    } elseif (strlen($password) > 128) {
        $errors[] = 'Password cannot exceed 128 characters';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter, one uppercase letter, and one number';
    }
    
    // Confirm password
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }
    
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => implode('. ', $errors),
            'errors' => $errors
        ]);
        exit;
    }
    
    // Check if email already exists
    if (emailAlreadyExists($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'An account with this email already exists. Please login instead.',
            'error_code' => 'EMAIL_EXISTS'
        ]);
        exit;
    }
    
    // Rate limiting check
    if (isRateLimited($_SERVER['REMOTE_ADDR'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Too many signup attempts. Please wait 15 minutes before trying again.',
            'error_code' => 'RATE_LIMITED'
        ]);
        exit;
    }
    
    // Prepare signup data (don't hash password yet - will be done after OTP verification)
    $signupData = [
        'full_name' => $fullName,
        'email' => $email,
        'password' => $password, // Store temporarily, will hash after OTP verification
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'signup_timestamp' => time()
    ];
    
    // Initialize OTP Manager
    $otpManager = new OTPManager($conn);
    
    // Generate and store OTP
    $otpResult = $otpManager->storeOTP($email, $signupData);
    
    if (!$otpResult['success']) {
        echo json_encode($otpResult);
        exit;
    }
    
    // Send OTP email
    $emailResult = $otpManager->sendOTP($email, $otpResult['otp'], $fullName);
    
    if (!$emailResult['success']) {
        echo json_encode($emailResult);
        exit;
    }
    
    // Log signup attempt
    logActivity('signup_otp_sent', $email, $_SERVER['REMOTE_ADDR']);
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Verification code sent to your email',
        'expires_in' => $otpResult['expires_in'],
        'email' => $email
    ]);
    
} catch (Exception $e) {
    // Log error
    error_log("Signup error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during signup. Please try again.',
        'error_code' => 'INTERNAL_ERROR'
    ]);
}

/**
 * Check if email already exists in database
 */
function emailAlreadyExists($email) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

/**
 * Simple rate limiting based on IP address
 */
function isRateLimited($ipAddress) {
    global $conn;
    
    // Create rate limit table if it doesn't exist
    $conn->query("CREATE TABLE IF NOT EXISTS rate_limits (
        ip_address VARCHAR(45) PRIMARY KEY,
        attempts INT DEFAULT 1,
        last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Clean old entries (older than 15 minutes)
    $conn->query("DELETE FROM rate_limits WHERE last_attempt < DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    
    // Check current attempts
    $stmt = $conn->prepare("SELECT attempts FROM rate_limits WHERE ip_address = ?");
    $stmt->bind_param("s", $ipAddress);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['attempts'] >= 5) { // Max 5 attempts per 15 minutes
            return true;
        }
        // Increment attempts
        $updateStmt = $conn->prepare("UPDATE rate_limits SET attempts = attempts + 1 WHERE ip_address = ?");
        $updateStmt->bind_param("s", $ipAddress);
        $updateStmt->execute();
    } else {
        // First attempt
        $insertStmt = $conn->prepare("INSERT INTO rate_limits (ip_address, attempts) VALUES (?, 1)");
        $insertStmt->bind_param("s", $ipAddress);
        $insertStmt->execute();
    }
    
    return false;
}

/**
 * Log activity for security monitoring
 */
function logActivity($action, $email, $ipAddress) {
    global $conn;
    
    // Create activity log table if it doesn't exist
    $conn->query("CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        action VARCHAR(50) NOT NULL,
        email VARCHAR(255),
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $stmt = $conn->prepare("INSERT INTO activity_logs (action, email, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $action, $email, $ipAddress, $userAgent);
    $stmt->execute();
}
?>