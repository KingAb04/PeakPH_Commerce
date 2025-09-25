<?php
session_start();
require_once("../../db.php");

// ✅ Redirect if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../../index.php");
    exit;
}

// ✅ Check if product ID is provided via POST
if (isset($_POST['id']) && is_numeric($_POST['id'])) {
    $id = intval($_POST['id']);

    // Prepare and execute delete query
    $stmt = $conn->prepare("DELETE FROM inventory WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirect back to inventory with success message
        header("Location: inventory.php?status=deleted");
        exit;
    } else {
        echo "❌ Error deleting product: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "⚠️ Invalid request. No product ID provided.";
}
