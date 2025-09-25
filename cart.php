<?php
session_start();

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Handle cart actions
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'update':
            if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
                $product_id = $_POST['product_id'];
                $quantity = intval($_POST['quantity']);
                
                if ($quantity > 0) {
                    $_SESSION['cart'][$product_id]['quantity'] = $quantity;
                } else {
                    unset($_SESSION['cart'][$product_id]);
                }
            }
            break;
            
        case 'remove':
            if (isset($_POST['product_id'])) {
                unset($_SESSION['cart'][$_POST['product_id']]);
            }
            break;
            
        case 'clear':
            $_SESSION['cart'] = array();
            break;
    }
    
    // Redirect to prevent form resubmission
    header('Location: cart.php');
    exit;
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
                <button id="loginIcon" class="login-btn">
                    <i class="bi bi-person"></i>
                    <span>Login</span>
                </button>
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
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image">
                        
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
                
                <button class="checkout-btn">Proceed to Checkout</button>
                
                <form method="post" style="margin-top: 1rem;">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" style="background: #e74c3c; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;">Clear Cart</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

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
</body>
</html>