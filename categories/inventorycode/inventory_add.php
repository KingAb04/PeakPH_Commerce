<?php
session_start();
require_once("../../db.php");

// Redirect if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../index.php");
    exit;
}

// Check if form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product_name = trim($_POST['product_name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $tag = !empty($_POST['tag']) ? trim($_POST['tag']) : NULL;

    // Handle image upload
    $image_path = NULL;
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Create uploads folder if missing
        }

        $file_name = time() . "_" . basename($_FILES['image']['name']);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = "uploads/" . $file_name; // store relative path
        }
    }

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO inventory (product_name, price, stock, tag, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sdiss", $product_name, $price, $stock, $tag, $image_path);

    if ($stmt->execute()) {
        header("Location: inventory.php?status=added");
        exit;
    } else {
        echo "Error adding product: " . $conn->error;
    }
} else {
    header("Location: inventory.php");
    exit;
}
