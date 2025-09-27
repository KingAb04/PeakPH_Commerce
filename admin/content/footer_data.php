<?php
session_start();
require_once("../../includes/db.php");

// Redirect if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // In a real implementation, you would save this to a database
    // For now, we'll just redirect back with a success message
    header("Location: footer.php?status=updated");
    exit;
}

// If accessed directly, redirect back
header("Location: footer.php");
exit;
?>