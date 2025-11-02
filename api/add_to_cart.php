<?php
session_start();
require_once '../includes/db.php';

// Initialize response array
$response = [
    'success' => false,
    'cart_count' => 0,
    'message' => '',
    'product_name' => ''
];

// Initialize cart if not exists
if(!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check database connection
if(isset($db_connection_error) && $db_connection_error) {
    $response['message'] = 'Database connection error. Please try again later.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Handle both database and demo products
if(isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'] ?? '';
    $product_price = floatval($_POST['product_price'] ?? 0);
    $product_image = $_POST['product_image'] ?? '';
    
    // Try to get from database first
    $product = null;
    if(is_numeric($product_id) && isset($conn)) {
        $product_query = "SELECT * FROM inventory WHERE id = ?";
        $stmt = $conn->prepare($product_query);
        if($stmt) {
            $product_id_int = intval($product_id);
            $stmt->bind_param("i", $product_id_int);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows > 0) {
                $product = $result->fetch_assoc();
            }
        }
    }
    
    // Use database product if found, otherwise use form data
    if($product) {
        // Check stock availability for database products
        if($product['stock'] <= 0) {
            $response['message'] = 'Product is out of stock';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        
        // Define base64 placeholder image
        $placeholder_image = 'data:image/svg+xml;base64,' . base64_encode('<?xml version="1.0" encoding="UTF-8"?><svg width="200" height="200" xmlns="http://www.w3.org/2000/svg"><rect width="200" height="200" fill="#f0f0f0"/><text x="100" y="100" font-family="Arial" font-size="14" text-anchor="middle" fill="#999">No Image</text></svg>');
        
        // Fix image path for database products
        $image_path = $placeholder_image;
        if (!empty($product['image'])) {
            // Store original image path for debugging
            error_log('Original image path from DB: ' . $product['image']);
            
            // If path doesn't start with admin or /admin, check if it needs to be added
            if (!preg_match('~^/?admin/~', $product['image'])) {
                if (strpos($product['image'], 'uploads/') === 0) {
                    $image_path = '/admin/' . $product['image'];
                } else {
                    $image_path = '/admin/uploads/' . basename($product['image']);
                }
            } else if (strpos($product['image'], 'admin/') === 0) {
                // Ensure there's a leading slash
                $image_path = '/' . $product['image'];
            } else {
                $image_path = $product['image']; // Keep as is if it starts with /admin/
            }
            
            // Validate the physical file exists
            $physical_path = $_SERVER['DOCUMENT_ROOT'] . $image_path;
            if (!file_exists($physical_path)) {
                error_log('Warning: Image file not found at: ' . $physical_path);
                $image_path = $placeholder_image;
            }
            
            error_log('Final image path set to: ' . $image_path);
        }
        
        $final_product = [
            'id' => $product['id'],
            'name' => $product['product_name'],
            'price' => floatval($product['price']),
            'image' => $image_path,
            'quantity' => 1,
            'stock' => $product['stock'],
            'is_database' => true
        ];
    } else {
        // Use form data for demo/hardcoded products
        // Make sure image path is absolute
        if (!empty($product_image) && !str_starts_with($product_image, '/')) {
            $product_image = '/' . ltrim($product_image, '/');
        }
        
        $final_product = [
            'id' => $product_id,
            'name' => $product_name,
            'price' => $product_price,
            'image' => $product_image,
            'quantity' => 1,
            'stock' => 999, // Demo products have unlimited stock
            'is_database' => false
        ];
    }
    
    // Validate product data
    if(empty($final_product['name']) || $final_product['price'] <= 0) {
        $response['message'] = 'Invalid product data';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Check if product already in cart
    $product_found = false;
    foreach($_SESSION['cart'] as $key => $item) {
        if($item['id'] == $product_id) {
            // Check stock before increasing quantity
            $new_quantity = $item['quantity'] + 1;
            if($final_product['is_database'] && $new_quantity > $final_product['stock']) {
                $response['message'] = 'Not enough stock available. Only ' . $final_product['stock'] . ' items in stock.';
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
            $_SESSION['cart'][$key]['quantity'] = $new_quantity;
            $product_found = true;
            break;
        }
    }
    
    // If product not in cart, add it using product_id as key
    if(!$product_found) {
        $_SESSION['cart'][$product_id] = $final_product;
    }
    
    $response['success'] = true;
    $response['product_name'] = $final_product['name'];
    
    // Count items in cart
    $cart_count = 0;
    foreach($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
    
    $response['cart_count'] = $cart_count;
} else {
    $response['message'] = 'No product ID provided';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>