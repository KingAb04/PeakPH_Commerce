<?php
/**
 * OTP Verification Handler
 * Step 2: Verify OTP and complete user registration
 */

session_start();
header('Content-Type: application/json')} catch (Exception $e) {
    // Log error with detailed information for debugging
    $errorDetails = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
        'email' => $email ?? 'unknown',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    error_log("OTP Verification Error: " . json_encode($errorDetails));
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during verification. Please try again.',
        'error_code' => 'INTERNAL_ERROR',
        'debug' => [
            'error' => $e->getMessage(),
            'line' => $e->getLine()
        ]
    ]);
}able error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/OTPManager.php';

// CSRF Protection - Generate token if not exists
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
    $email = trim(strtolower($_POST['email'] ?? ''));
    $otpCode = trim($_POST['otp_code'] ?? '');
    
    // Basic validation
    if (empty($email) || empty($otpCode)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email and OTP code are required'
        ]);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email format'
        ]);
        exit;
    }
    
    if (!preg_match('/^\d{6}$/', $otpCode)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid OTP format. Please enter 6 digits.'
        ]);
        exit;
    }
    
    // Initialize OTP Manager
    $otpManager = new OTPManager($conn);
    
    // Verify OTP
    $verificationResult = $otpManager->verifyOTP($email, $otpCode);
    
    if (!$verificationResult['success']) {
        // Log failed verification attempt
        logActivity('otp_verification_failed', $email, $_SERVER['REMOTE_ADDR']);
        echo json_encode($verificationResult);
        exit;
    }
    
    // OTP verified successfully, get signup data
    $signupData = $verificationResult['signup_data'];
    
    // Double-check that the signup data is valid and not too old
    if (!$signupData || !isset($signupData['email']) || $signupData['email'] !== $email) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid signup data. Please try signing up again.',
            'error_code' => 'INVALID_SIGNUP_DATA'
        ]);
        exit;
    }
    
    // Check if signup data is too old (older than 1 hour)
    if (isset($signupData['signup_timestamp']) && (time() - $signupData['signup_timestamp']) > 3600) {
        echo json_encode([
            'success' => false,
            'message' => 'Signup session expired. Please sign up again.',
            'error_code' => 'SIGNUP_EXPIRED'
        ]);
        exit;
    }
    
    // Check if email already exists (double-check)
    if (emailAlreadyExists($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'An account with this email already exists.',
            'error_code' => 'EMAIL_EXISTS'
        ]);
        exit;
    }
    
    // Hash password securely
    $hashedPassword = password_hash($signupData['password'], PASSWORD_ARGON2ID, [
        'memory_cost' => 65536, // 64 MB
        'time_cost' => 4,       // 4 iterations
        'threads' => 3,         // 3 threads
    ]);
    
    // Create user in database
    $userId = createUser([
        'full_name' => $signupData['full_name'],
        'email' => $signupData['email'],
        'password' => $hashedPassword,
        'ip_address' => $signupData['ip_address'],
        'user_agent' => $signupData['user_agent']
    ]);
    
    if (!$userId) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create user account. Please try again.',
            'error_code' => 'USER_CREATION_FAILED'
        ]);
        exit;
    }
    
    // Log successful registration
    logActivity('user_registered', $email, $_SERVER['REMOTE_ADDR']);
    
    // Set user session (optional - you might want to require login)
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $signupData['full_name'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Account created successfully! Welcome to PeakPH!',
        'user' => [
            'id' => $userId,
            'name' => $signupData['full_name'],
            'email' => $email
        ],
        'redirect' => 'index.php' // Where to redirect after successful signup
    ]);
    
} catch (Exception $e) {
    // Log error
    error_log("OTP verification error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during verification. Please try again.',
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
 * Create user in database
 */
function createUser($userData) {
    global $conn;
    
    // Create users table if it doesn't exist
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email_verified TINYINT(1) DEFAULT 1,
        is_active TINYINT(1) DEFAULT 1,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email)
    )");
    
    $stmt = $conn->prepare("
        INSERT INTO users (full_name, email, password, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param(
        "sssss", 
        $userData['full_name'],
        $userData['email'],
        $userData['password'],
        $userData['ip_address'],
        $userData['user_agent']
    );
    
    if ($stmt->execute()) {
        return $conn->insert_id;
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