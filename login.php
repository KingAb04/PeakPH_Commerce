<?php
session_start();

// --- ADMIN CREDENTIALS ---
// You can change these or later connect to a database
$admin_email = "admin@peakph.com";
$admin_pass = "12345";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check if login is correct
    if ($email === $admin_email && $password === $admin_pass) {
        $_SESSION['logged_in'] = true;
        $_SESSION['admin_email'] = $email;
        header("Location: admin.php");
        exit;
    } else {
        // Back to index with error flag
        header("Location: index.php?login=failed");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
