<?php
session_start();

// This is for REGULAR USERS ONLY - Admin login is handled separately
// You can add database-based user authentication here for regular customers

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // TODO: Add regular user authentication logic here
    // For now, this just redirects back with error since regular users
    // should not have access to admin functionality
    
    header("Location: index.php?login=failed&msg=invalid_user");
    exit;
} else {
    header("Location: index.php");
    exit;
}
