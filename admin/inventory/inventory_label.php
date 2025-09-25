<?php
session_start();
require_once("../../includes/db.php");

// Check if logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../index.php");
    exit;
}

// Update label
if (isset($_POST['id'], $_POST['label'])) {
    $id = intval($_POST['id']);
    $label = $_POST['label'];

    $stmt = $conn->prepare("UPDATE inventory SET label = ? WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
    $stmt->bind_param("si", $label, $id);
    $stmt->execute();

    header("Location: inventory.php?status=label-updated");
    exit;
} else {
    header("Location: inventory.php");
    exit;
}
?>