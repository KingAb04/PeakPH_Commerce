<?php
require_once('../auth_helper.php');
requireAdminAuth();
require_once("../../includes/db.php");

// Check if form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check database connection
    if (!isDatabaseConnected()) {
        die("Database connection error. Please try again later.");
    }
    
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
            // Store the path relative to admin directory
            $image_path = "uploads/" . $file_name;
            
            // Log the file info for debugging
            error_log("Image upload successful:");
            error_log("Target file: " . $target_file);
            error_log("Stored path: " . $image_path);
            error_log("File exists: " . (file_exists($target_file) ? "Yes" : "No"));
        } else {
            error_log("Image upload failed: " . print_r($_FILES['image']['error'], true));
            die("Error uploading image.");
        }
    }

    // Insert into DB using safe query execution
    $query = "INSERT INTO inventory (product_name, price, stock, tag, image) VALUES (?, ?, ?, ?, ?)";
    $params = [$product_name, $price, $stock, $tag, $image_path];
    $types = "sdiss"; // string, double, integer, string, string
    
    $result = executeQuery($query, $params, $types);
    
    if ($result === true) {
        header("Location: inventory.php?status=added");
        exit;
    } else {
        error_log("Inventory add error: Database query failed");
        die("Error adding product. Please try again.");
    }
} else {
    header("Location: inventory.php");
    exit;
}
