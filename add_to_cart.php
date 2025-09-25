<?php
session_start();
require_once 'includes/db.php';

// Initialize response array
$response = [
    'success' => false,
    'cart_count' => 0
];

// Check if product_id is set
if(isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    
    // Query to get product details
    $product_query = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($product_query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Initialize cart if not exists
        if(!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Check if product already in cart
        $product_found = false;
        foreach($_SESSION['cart'] as &$item) {
            if($item['id'] == $product_id) {
                $item['quantity']++;
                $product_found = true;
                break;
            }
        }
        
        // If product not in cart, add it
        if(!$product_found) {
            $_SESSION['cart'][] = [
                'id' => $product_id,
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image_path'],
                'quantity' => 1
            ];
        }
        
        $response['success'] = true;
    }
    
    // Count items in cart
    $cart_count = 0;
    if(isset($_SESSION['cart'])) {
        foreach($_SESSION['cart'] as $item) {
            $cart_count += $item['quantity'];
        }
    }
    
    $response['cart_count'] = $cart_count;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>