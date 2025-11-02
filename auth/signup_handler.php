<?php
session_start();
require_once '../includes/db.php';

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ||
          isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;

if ($isAjax) {
    header('Content-Type: application/json');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    } else {
        header('Location: ../index.php?error=method_not_allowed');
    }
    exit;
}

// Handle both JSON and form data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$full_name = trim($input['full_name'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$confirm_password = $input['confirm_password'] ?? '';

// Validate inputs
if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
    $error_msg = 'All fields are required';
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => $error_msg]);
    } else {
        header('Location: ../index.php?signup=failed&error=' . urlencode($error_msg));
    }
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error_msg = 'Invalid email format';
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => $error_msg]);
    } else {
        header('Location: ../index.php?signup=failed&error=' . urlencode($error_msg));
    }
    exit;
}

if (strlen($password) < 6) {
    $error_msg = 'Password must be at least 6 characters long';
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => $error_msg]);
    } else {
        header('Location: ../index.php?signup=failed&error=' . urlencode($error_msg));
    }
    exit;
}

if ($password !== $confirm_password) {
    $error_msg = 'Passwords do not match';
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => $error_msg]);
    } else {
        header('Location: ../index.php?signup=failed&error=' . urlencode($error_msg));
    }
    exit;
}

try {
    if (!isDatabaseConnected()) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    // Check if email already exists
    $check_query = "SELECT id FROM users WHERE email = ?";
    $result = executeQuery($check_query, [$email], 's');

    if ($result && $result->num_rows > 0) {
        $error_msg = 'Email already registered';
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => $error_msg]);
        } else {
            header('Location: ../index.php?signup=failed&error=' . urlencode($error_msg));
        }
        exit;
    }

    // Create user account directly (no OTP verification)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $insert_query = "INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, 'User', 'Active')";
    $success = executeQuery($insert_query, [$full_name, $email, $hashed_password], 'sss');

    if (!$success) {
        $error_msg = 'Failed to create account. Please try again.';
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => $error_msg]);
        } else {
            header('Location: ../index.php?signup=failed&error=' . urlencode($error_msg));
        }
        exit;
    }

    // Get the new user ID
    global $conn;
    if (!$conn) {
        $error_msg = 'Database connection error';
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => $error_msg]);
        } else {
            header('Location: ../index.php?signup=failed&error=' . urlencode($error_msg));
        }
        exit;
    }
    
    $user_id = $conn->insert_id;

    // Log successful registration
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $log_query = "INSERT INTO audit_trail (table_name, record_id, action, new_values, user_id, user_email, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $log_values = json_encode(['registration_time' => date('Y-m-d H:i:s'), 'ip_address' => $ip_address]);
    executeQuery($log_query, ['users', $user_id, 'REGISTER', $log_values, $user_id, $email, $ip_address], 'sisssss');

    // Auto-login the user
    $_SESSION['user_logged_in'] = true;
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $full_name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = 'User';

    if ($isAjax) {
        echo json_encode([
            'success' => true, 
            'message' => 'Account created successfully! Welcome to PeakPH!',
            'user' => [
                'id' => $user_id,
                'name' => $full_name,
                'email' => $email
            ]
        ]);
    } else {
        header('Location: ../index.php?signup=success');
    }

} catch (Exception $e) {
    error_log("Signup error: " . $e->getMessage());
    $error_msg = 'Registration failed. Please try again.';
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => $error_msg]);
    } else {
        header('Location: ../index.php?signup=failed&error=' . urlencode($error_msg));
    }
}
?>