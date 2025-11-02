<?php
require_once('../auth_helper.php');
requireAdminAuth();
require_once("../../includes/db.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check database connection
    if (!isDatabaseConnected()) {
        die("Database connection error. Please try again later.");
    }
    
    $id = intval($_POST['id']);
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

        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_types)) {
            die("Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.");
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = "uploads/" . $file_name; // store relative path
        } else {
            die("Error uploading image.");
        }
    }

    // Update database with or without image using prepared statements
    if ($image_path) {
        $query = "UPDATE inventory SET product_name=?, price=?, stock=?, tag=?, image=? WHERE id=?";
        $params = [$product_name, $price, $stock, $tag, $image_path, $id];
        $types = "sdissi";
    } else {
        $query = "UPDATE inventory SET product_name=?, price=?, stock=?, tag=? WHERE id=?";
        $params = [$product_name, $price, $stock, $tag, $id];
        $types = "sdisi";
    }

    $result = executeQuery($query, $params, $types);
    
    if ($result === true) {
        header("Location: inventory.php?status=updated");
        exit;
    } else {
        error_log("Inventory update error: Database query failed for ID $id");
        die("Error updating product. Please try again.");
    }
} else {
    header("Location: inventory.php");
    exit;
}
