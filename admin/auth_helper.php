<?php
/**
 * Admin Authentication Helper
 * Provides secure authentication checks for admin pages
 */

function requireAdminAuth() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in AND is an admin
    if (!isset($_SESSION['logged_in']) || 
        $_SESSION['logged_in'] !== true || 
        !isset($_SESSION['is_admin']) || 
        $_SESSION['is_admin'] !== true) {
        
        // Clear any invalid session data
        session_destroy();
        
        // Redirect to admin login
        header("Location: " . getAdminLoginPath());
        exit;
    }
    
    // Optional: Check session timeout (2 hours)
    if (isset($_SESSION['login_time'])) {
        $timeout = 2 * 60 * 60; // 2 hours in seconds
        if (time() - $_SESSION['login_time'] > $timeout) {
            session_destroy();
            header("Location: " . getAdminLoginPath() . "?timeout=1");
            exit;
        }
        // Refresh login time
        $_SESSION['login_time'] = time();
    }
}

function getAdminLoginPath() {
    // Determine the correct path to admin login based on current directory
    $currentScript = $_SERVER['SCRIPT_NAME'];
    
    // Count how many levels deep we are from the admin folder
    if (strpos($currentScript, '/admin/') !== false) {
        $afterAdmin = substr($currentScript, strpos($currentScript, '/admin/') + 7);
        $levels = substr_count($afterAdmin, '/');
        
        if ($levels <= 0) {
            return "login.php";
        } else {
            return str_repeat("../", $levels) . "login.php";
        }
    }
    
    return "login.php";
}

function isAdmin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['logged_in']) && 
           $_SESSION['logged_in'] === true && 
           isset($_SESSION['is_admin']) && 
           $_SESSION['is_admin'] === true;
}

// Legacy function for backward compatibility
function checkAdminAuth() {
    requireAdminAuth();
}

// Get admin email safely
function getAdminEmail() {
    return isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : 'Unknown';
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Sanitize output
function sanitizeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>