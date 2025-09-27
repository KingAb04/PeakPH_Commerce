<?php
session_start();

// Admin credentials (same as your existing login.php)
$admin_email = "admin@peakph.com";
$admin_pass = "12345";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $remember_me = isset($_POST['remember_me']);

    // Check if login is correct
    if ($email === $admin_email && $password === $admin_pass) {
        $_SESSION['logged_in'] = true;
        $_SESSION['is_admin'] = true;
        $_SESSION['admin_email'] = $email;
        $_SESSION['login_time'] = time();
        
        // Set remember me cookie if requested (30 days)
        if ($remember_me) {
            $cookie_value = base64_encode($email . ':' . time());
            setcookie('admin_remember', $cookie_value, time() + (30 * 24 * 60 * 60), '/admin/');
        }
        
        // Redirect to admin dashboard
        header("Location: dashboard.php");
        exit;
    } else {
        // Back to admin login with error flag
        header("Location: login.php?login=failed");
        exit;
    }
} else {
    // Check for remember me cookie
    if (isset($_COOKIE['admin_remember']) && !isset($_SESSION['logged_in'])) {
        $cookie_data = base64_decode($_COOKIE['admin_remember']);
        if (strpos($cookie_data, ':') !== false) {
            list($stored_email, $timestamp) = explode(':', $cookie_data);
            
            // Check if cookie is still valid (30 days) and email matches
            if (time() - $timestamp < (30 * 24 * 60 * 60) && $stored_email === $admin_email) {
                $_SESSION['logged_in'] = true;
                $_SESSION['is_admin'] = true;
                $_SESSION['admin_email'] = $stored_email;
                $_SESSION['login_time'] = time();
                header("Location: dashboard.php");
                exit;
            } else {
                // Cookie expired or invalid, remove it
                setcookie('admin_remember', '', time() - 3600, '/admin/');
            }
        }
    }
    
    // Redirect to admin login page
    header("Location: login.php");
    exit;
}
?>