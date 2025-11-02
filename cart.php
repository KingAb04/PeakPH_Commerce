<?php
require_once 'includes/user_auth.php';

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

$message = '';
$error = '';

// Handle cart actions
if (isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'update':
                if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
                    $product_id = $_POST['product_id'];
                    $quantity = intval($_POST['quantity']);
                    
                    if (isset($_SESSION['cart'][$product_id])) {
                        if ($quantity > 0) {
                            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
                            $message = 'Cart updated successfully!';
                        } else {
                            unset($_SESSION['cart'][$product_id]);
                            $message = 'Item removed from cart!';
                        }
                    } else {
                        $error = 'Product not found in cart.';
                    }
                }
                break;
                
            case 'remove':
                if (isset($_POST['product_id'])) {
                    $product_id = $_POST['product_id'];
                    if (isset($_SESSION['cart'][$product_id])) {
                        $product_name = $_SESSION['cart'][$product_id]['name'];
                        unset($_SESSION['cart'][$product_id]);
                        $message = $product_name . ' removed from cart!';
                    } else {
                        $error = 'Product not found in cart.';
                    }
                }
                break;
                
            case 'clear':
                $_SESSION['cart'] = array();
                $message = 'Cart cleared successfully!';
                break;
                
            default:
                $error = 'Invalid action.';
        }
    } catch (Exception $e) {
        $error = 'An error occurred while updating your cart. Please try again.';
        error_log('Cart error: ' . $e->getMessage());
    }
    
    // Redirect to prevent form resubmission
    $redirect_url = 'cart.php';
    if ($message) $redirect_url .= '?message=' . urlencode($message);
    if ($error) $redirect_url .= '?error=' . urlencode($error);
    
    header('Location: ' . $redirect_url);
    exit;
}

// Get messages from URL parameters
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}

// Calculate totals
$total = 0;
$item_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
    $item_count += $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - PeakPH</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="Css/Global.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .cart-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .cart-header h1 {
            color: #2e765e;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .cart-items {
            background: #fff;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            padding: 1.5rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 1rem;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }
        
        .item-price {
            color: #2e765e;
            font-weight: 600;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            margin: 0 1rem;
        }
        
        .quantity-btn {
            background: #2e765e;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .quantity-btn:hover {
            background: #245d4b;
        }
        
        .quantity-input {
            width: 60px;
            text-align: center;
            border: 1px solid #ddd;
            padding: 0.5rem;
            margin: 0 0.5rem;
            border-radius: 4px;
        }
        
        .remove-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .remove-btn:hover {
            background: #c0392b;
        }
        
        .cart-summary {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        .total-row {
            border-top: 2px solid #2e765e;
            padding-top: 1rem;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .checkout-btn {
            width: 100%;
            background: #2e765e;
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
        }
        
        .checkout-btn:hover {
            background: #245d4b;
        }
        
        .empty-cart {
            text-align: center;
            padding: 3rem;
        }
        
        .empty-cart i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 1rem;
        }
        
        .continue-shopping {
            background: #2e765e;
            color: white;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            display: inline-block;
            margin-top: 1rem;
        }
        
        .continue-shopping:hover {
            background: #245d4b;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header>
        <div class="top-navbar">
            <div class="brand">
                <a href="index.php" class="logo-btn">
                    <img src="Assets/Carousel_Picts/Logo.png" alt="Brand Logo" />
                </a>
            </div>

            <div class="search-wrapper">
                <i class="bi bi-search"></i>
                <input type="search" placeholder="Search..." />
            </div>

            <div class="top-icons">
                <?php echo getAuthNavigationHTML(); ?>
                <a href="cart.php" class="cart-link">
                    <i class="bi bi-cart">
                        <span class="cart-count"><?php echo $item_count; ?></span>
                    </i>
                </a>
            </div>
        </div>

        <!-- Bottom Navbar -->
        <div class="bottom-navbar">
            <nav>
                <a href="index.php">Home</a>
                <a href="ProductCatalog.php">Shop</a>
                <a href="#contact">Contact Us</a>
                <a href="#deals" class="best-deals">Best Deals</a>
                <a href="#about">About us</a>
            </nav>
        </div>
    </header>

    <div class="cart-container">
        <div class="cart-header">
            <h1>Shopping Cart</h1>
            <p><?php echo $item_count; ?> item(s) in your cart</p>
        </div>

        <?php if ($message): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                ✅ <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                ❌ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="empty-cart">
                <i class="bi bi-cart-x"></i>
                <h2>Your cart is empty</h2>
                <p>Start adding some items to your cart!</p>
                <a href="ProductCatalog.php" class="continue-shopping">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                    <div class="cart-item">
                        <?php
                        error_log('Cart item image path: ' . print_r($item['image'], true));
                        
                        $image_path = $item['image'];
                        
                        // Debug output
                        $debug_paths = [
                            'Original path' => $image_path,
                            'Physical path 1' => $_SERVER['DOCUMENT_ROOT'] . $image_path,
                            'Physical path 2' => $_SERVER['DOCUMENT_ROOT'] . '/admin/' . ltrim($image_path, '/'),
                            'Physical path 3' => __DIR__ . $image_path,
                        ];
                        
                        error_log('Checking image paths in cart:');
                        foreach ($debug_paths as $desc => $path) {
                            error_log($desc . ': ' . $path . ' - Exists: ' . (file_exists($path) ? 'Yes' : 'No'));
                        }
                        
                        // Define base64 placeholder image
                        $placeholder_image = 'data:image/svg+xml;base64,' . base64_encode('<?xml version="1.0" encoding="UTF-8"?><svg width="200" height="200" xmlns="http://www.w3.org/2000/svg"><rect width="200" height="200" fill="#f0f0f0"/><text x="100" y="100" font-family="Arial" font-size="14" text-anchor="middle" fill="#999">No Image</text></svg>');
                        
                        // Handle different path scenarios
                        if (!empty($image_path)) {
                            error_log('Original image path: ' . $image_path);
                            
                            // If path doesn't start with admin or /admin, check if it needs to be added
                            if (!preg_match('~^/?admin/~', $image_path)) {
                                if (strpos($image_path, 'uploads/') === 0) {
                                    $image_path = '/admin/' . $image_path;
                                } else {
                                    $image_path = '/admin/uploads/' . basename($image_path);
                                }
                            } else if (strpos($image_path, 'admin/') === 0) {
                                // Ensure there's a leading slash
                                $image_path = '/' . $image_path;
                            }
                            
                            error_log('Processed image path: ' . $image_path);
                            
                            // Double check the file exists physically
                            $physical_path = $_SERVER['DOCUMENT_ROOT'] . $image_path;
                            if (!file_exists($physical_path)) {
                                error_log('Warning: Image file not found at: ' . $physical_path);
                                $image_path = $placeholder_image;
                            }
                        } else {
                            $image_path = $placeholder_image;
                            error_log('Using placeholder image - no path provided');
                        }
                        
                        error_log('Final image path in cart: ' . $image_path);
                        ?>
                        <img src="<?php echo htmlspecialchars($image_path); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                             class="item-image" 
                             onerror="this.src='/Assets/placeholder.svg'">
                        
                        <div class="item-details">
                            <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="item-price">₱<?php echo number_format($item['price'], 2); ?></div>
                        </div>
                        
                        <div class="quantity-controls">
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                <button type="submit" name="quantity" value="<?php echo $item['quantity'] - 1; ?>" class="quantity-btn">-</button>
                            </form>
                            
                            <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" 
                                   onchange="updateQuantity('<?php echo $product_id; ?>', this.value)" min="1">
                            
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                <button type="submit" name="quantity" value="<?php echo $item['quantity'] + 1; ?>" class="quantity-btn">+</button>
                            </form>
                        </div>
                        
                        <div style="margin-left: 1rem; font-weight: 600;">
                            ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                        
                        <form method="post" style="margin-left: 1rem;">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                            <button type="submit" class="remove-btn">Remove</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>₱<?php echo number_format($total, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>₱50.00</span>
                </div>
                <div class="summary-row total-row">
                    <span>Total:</span>
                    <span>₱<?php echo number_format($total + 50, 2); ?></span>
                </div>
                
                <a href="checkout.php" class="checkout-btn" style="text-decoration: none; display: block; text-align: center;">Proceed to Checkout</a>
                
                <form method="post" style="margin-top: 1rem;">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" style="background: #e74c3c; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;">Clear Cart</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- AUTH MODAL -->
    <?php include 'components/auth_modal.php'; ?>

    <script>
        function updateQuantity(productId, quantity) {
            if (quantity < 1) return;
            
            const form = document.createElement('form');
            form.method = 'post';
            form.innerHTML = `
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="product_id" value="${productId}">
                <input type="hidden" name="quantity" value="${quantity}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    </script>
    <script src="Js/user_dropdown.js"></script>
    <script src="Js/cart.js"></script>
    <script src="components/auth_modal_otp.js"></script>
    <script src="Js/JavaScript.js"></script>
</body>
</html>