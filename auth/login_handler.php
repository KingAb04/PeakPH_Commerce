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

$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

// Validate inputs
if (empty($email) || empty($password)) {
    $error_msg = 'Email and password are required';
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => $error_msg]);
    } else {
        header('Location: ../index.php?login=failed&error=' . urlencode($error_msg));
    }
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error_msg = 'Invalid email format';
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => $error_msg]);
    } else {
        header('Location: ../index.php?login=failed&error=' . urlencode($error_msg));
    }
    exit;
}

try {
    if (!isDatabaseConnected()) {
        $error_msg = 'Database connection failed';
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => $error_msg]);
        } else {
            header('Location: ../index.php?login=failed&error=' . urlencode($error_msg));
        }
        exit;
    }

    // Get user from database
    $query = "SELECT id, username, email, password, role, status FROM users WHERE email = ? AND role = 'User'";
    $result = executeQuery($query, [$email], 's');

    if (!$result || $result->num_rows === 0) {
        $error_msg = 'Invalid email or password';
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => $error_msg]);
        } else {
            header('Location: ../index.php?login=failed&error=' . urlencode($error_msg));
        }
        exit;
    }

    $user = $result->fetch_assoc();

    // Check if account is active
    if ($user['status'] !== 'Active') {
        $error_msg = 'Your account is inactive. Please contact support.';
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => $error_msg]);
        } else {
            header('Location: ../index.php?login=failed&error=' . urlencode($error_msg));
        }
        exit;
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        $error_msg = 'Invalid email or password';
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => $error_msg]);
        } else {
            header('Location: ../index.php?login=failed&error=' . urlencode($error_msg));
        }
        exit;
    }

    // Set session variables
    $_SESSION['user_logged_in'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['username'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    // Log successful login
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $log_query = "INSERT INTO audit_trail (table_name, record_id, action, new_values, user_id, user_email, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $log_values = json_encode(['login_time' => date('Y-m-d H:i:s'), 'ip_address' => $ip_address]);
    executeQuery($log_query, ['users', $user['id'], 'LOGIN', $log_values, $user['id'], $user['email'], $ip_address], 'sisssss');

    if ($isAjax) {
        echo json_encode([
            'success' => true, 
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'name' => $user['username'],
                'email' => $user['email']
            ]
        ]);
    } else {
        header('Location: ../index.php?login=success');
    }

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    $error_msg = 'Login failed. Please try again.';
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => $error_msg]);
    } else {
        header('Location: ../index.php?login=failed&error=' . urlencode($error_msg));
    }
}
?>