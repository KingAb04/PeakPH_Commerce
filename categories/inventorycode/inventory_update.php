<?php
session_start();
require_once("../../db.php");

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST['id']);
    $product_name = trim($_POST['product_name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $tag = !empty($_POST['tag']) ? trim($_POST['tag']) : NULL;

    // Handle new image (if uploaded)
    $image_sql = "";
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . "_" . basename($_FILES['image']['name']);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = "uploads/" . $file_name;
            $image_sql = ", image='$image_path'";
        }
    }

    $sql = "UPDATE inventory SET 
            product_name='$product_name',
            price=$price,
            stock=$stock,
            tag=" . ($tag ? "'$tag'" : "NULL") . "
            $image_sql
            WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: inventory.php?status=updated");
        exit;
    } else {
        echo "Error updating record: " . $conn->error;
    }
} else {
    header("Location: inventory.php");
    exit;
}
