<?php
/**
 * Resend OTP Handler
 * Resends OTP verification code to user's email
 */

session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging (disable in production)
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
    
    // Basic validation
    if (empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email is required'
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
    
    // Initialize OTP Manager
    $otpManager = new OTPManager($conn);
    
    // Check if there's existing signup data for this email
    $existingData = getExistingSignupData($email);
    
    if (!$existingData) {
        echo json_encode([
            'success' => false,
            'message' => 'No pending signup found for this email. Please start the signup process again.',
            'error_code' => 'NO_PENDING_SIGNUP'
        ]);
        exit;
    }
    
    // Generate and store new OTP
    $otpResult = $otpManager->storeOTP($email, $existingData);
    
    if (!$otpResult['success']) {
        echo json_encode($otpResult);
        exit;
    }
    
    // Send OTP email
    $fullName = $existingData['full_name'] ?? '';
    $emailResult = $otpManager->sendOTP($email, $otpResult['otp'], $fullName);
    
    if (!$emailResult['success']) {
        echo json_encode($emailResult);
        exit;
    }
    
    // Log resend attempt
    logActivity('otp_resent', $email, $_SERVER['REMOTE_ADDR']);
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'New verification code sent to your email',
        'expires_in' => $otpResult['expires_in']
    ]);
    
} catch (Exception $e) {
    // Log error
    error_log("OTP resend error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while resending the code. Please try again.',
        'error_code' => 'INTERNAL_ERROR'
    ]);
}

/**
 * Get existing signup data for email
 */
function getExistingSignupData($email) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT signup_data 
        FROM otp_verifications 
        WHERE email = ? AND expires_at > NOW() AND is_verified = 0
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return json_decode($row['signup_data'], true);
    }
    
    return null;
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