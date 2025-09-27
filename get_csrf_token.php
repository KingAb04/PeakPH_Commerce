<?php
/**
 * CSRF Token Generator
 * Provides CSRF tokens for client-side requests
 */

session_start();
header('Content-Type: application/json');

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo json_encode([
    'success' => true,
    'token' => $_SESSION['csrf_token']
]);
?>