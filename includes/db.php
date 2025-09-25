<?php
$host = "localhost";   // or 127.0.0.1
$user = "root";        // default XAMPP user
$pass = "";            // leave empty for XAMPP
$dbname = "peakph_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
