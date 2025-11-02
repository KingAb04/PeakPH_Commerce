<?php
require_once 'includes/user_auth.php';
require_once 'includes/db.php';

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Calculate total items in cart
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
}

// Get product ID from URL parameter
$product_id = isset($_GET['id']) ? $_GET['id'] : null;
$product = null;
$related_products = [];

if ($product_id && isDatabaseConnected()) {
    // Get main product from database
    $query = "SELECT * FROM inventory WHERE id = ? AND stock > 0";
    $result = executeQuery($query, [$product_id]);
    
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Fix image path
        if (!empty($product['image'])) {
            if (file_exists('admin/' . $product['image'])) {
                $product['image'] = 'admin/' . $product['image'];
            } elseif (!file_exists($product['image'])) {
                $product['image'] = 'Assets/placeholder.svg';
            }
        } else {
            $product['image'] = 'Assets/placeholder.svg';
        }
        
        // Get related products (same category, excluding current product)
        $related_query = "SELECT id, product_name, price, image, stock FROM inventory WHERE tag = ? AND id != ? AND stock > 0 LIMIT 4";
        $related_result = executeQuery($related_query, [$product['tag'], $product_id]);
        
        if ($related_result) {
            while ($row = $related_result->fetch_assoc()) {
                // Fix image path for related products
                if (!empty($row['image']) && file_exists('admin/' . $row['image'])) {
                    $row['image'] = 'admin/' . $row['image'];
                } else {
                    $row['image'] = 'Assets/placeholder.svg';
                }
                $related_products[] = $row;
            }
        }
    }
}

// Fallback to demo product if not found or no ID provided
if (!$product) {
    $product = [
        'id' => 'demo_1',
        'product_name' => 'Large Camping Folding Armchair - XL',
        'price' => 1290.00,
        'stock' => 15,
        'tag' => 'camping',
        'label' => 'Popular',
        'image' => 'Assets/Gallery_Images/TentSample.jpg',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Demo related products
    $related_products = [
        ['id' => 'demo_2', 'product_name' => '4-Person Camping Tent', 'price' => 1200.00, 'image' => 'Assets/Gallery_Images/TentSample.jpg', 'stock' => 25],
        ['id' => 'demo_3', 'product_name' => 'Portable Cooking Set', 'price' => 750.00, 'image' => 'Assets/Gallery_Images/CookingGearSample.png', 'stock' => 30],
        ['id' => 'demo_4', 'product_name' => 'Camping Stove', 'price' => 450.00, 'image' => 'Assets/Gallery_Images/Camping Stove Sample.png', 'stock' => 40]
    ];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PeakPH - Product Detail</title>

  <!-- Google Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

  <!-- Custom CSS -->
  <link rel="stylesheet" href="Css/Global.css">
  <link rel="stylesheet" href="Css/productview.css">

  <!-- Google Identity Services -->
  <script src="https://accounts.google.com/gsi/client" async defer></script>
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
            <span class="cart-count"><?php echo $cart_count; ?></span>
          </i>
        </a>
      </div>
    </div>

    <!-- BOTTOM NAVBAR -->
    <div class="bottom-navbar">
      <nav>
        <a href="ProductCatalog.php">Shop</a>
        <a href="#contact">Contact Us</a>
        <a href="#deals" class="best-deals">Best Deals</a>
        <a href="#about">About us</a>
      </nav>
    </div>
  </header>

  <!-- MAIN PRODUCT DETAIL -->
  <main>
    <div class="product-container">
      <div class="product-gallery">
        <div class="main-image">
          <img id="mainProductImage" src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" />
        </div>
        <div class="thumbnail-images">
          <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Main View" class="thumbnail active" onclick="changeImage(this.src)" />
          <!-- Additional thumbnails would be here if multiple images available -->
        </div>
      </div>

      <div class="product-info">
        <nav class="breadcrumb">
          <a href="index.php">Home</a> > 
          <a href="ProductCatalog.php">Products</a> > 
          <span><?php echo htmlspecialchars($product['product_name']); ?></span>
        </nav>
        
        <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
        
        <div class="product-rating">
          <span class="stars">⭐⭐⭐⭐☆</span>
          <span class="rating-text">(4.<?php echo rand(2, 8); ?> / 5)</span>
          <span class="review-count">- <?php echo rand(150, 2500); ?> reviews</span>
        </div>
        
        <div class="price-section">
          <span class="current-price">₱<?php echo number_format($product['price'], 2); ?></span>
          <?php if (rand(0, 1)): ?>
            <span class="old-price">₱<?php echo number_format($product['price'] * 1.3, 2); ?></span>
            <span class="discount">-<?php echo rand(15, 35); ?>%</span>
          <?php endif; ?>
        </div>

        <div class="product-meta">
          <p><strong>Category:</strong> <?php echo ucfirst($product['tag'] ?? 'General'); ?></p>
          <p><strong>SKU:</strong> PK-<?php echo str_pad($product['id'], 6, '0', STR_PAD_LEFT); ?></p>
          <p class="stock-info <?php echo ($product['stock'] < 10) ? 'low-stock' : 'in-stock'; ?>">
            <strong>Stock:</strong> 
            <?php if ($product['stock'] > 0): ?>
              <?php echo $product['stock']; ?> items available
              <?php if ($product['stock'] < 10): ?>
                <span class="low-stock-warning">⚠️ Low Stock!</span>
              <?php endif; ?>
            <?php else: ?>
              <span class="out-of-stock">❌ Out of Stock</span>
            <?php endif; ?>
          </p>
        </div>

        <div class="product-description">
          <h3>Product Description</h3>
          <p>Experience the ultimate in outdoor comfort with our premium <?php echo htmlspecialchars($product['product_name']); ?>. 
          Designed for adventurers who demand quality and durability, this product combines functionality with comfort.</p>
          
          <ul class="features">
            <li>✅ High-quality materials for long-lasting durability</li>
            <li>✅ Ergonomic design for maximum comfort</li>
            <li>✅ Easy to use and maintain</li>
            <li>✅ Perfect for outdoor activities and camping</li>
            <li>✅ Compact and portable design</li>
          </ul>
        </div>

        <div class="purchase-section">
          <div class="quantity-selector">
            <label>Quantity:</label>
            <div class="quantity-controls">
              <button type="button" id="decreaseQty" onclick="updateQuantity(-1)">-</button>
              <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" readonly />
              <button type="button" id="increaseQty" onclick="updateQuantity(1)">+</button>
            </div>
          </div>

          <div class="action-buttons">
            <button class="add-to-cart-btn" 
                    data-product-id="<?php echo htmlspecialchars($product['id']); ?>"
                    data-product-name="<?php echo htmlspecialchars($product['product_name']); ?>"
                    data-product-price="<?php echo $product['price']; ?>"
                    data-product-image="<?php echo htmlspecialchars($product['image']); ?>"
                    <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>>
              <i class="bi bi-cart-plus"></i>
              <?php echo ($product['stock'] <= 0) ? 'Out of Stock' : 'Add to Cart'; ?>
            </button>
            
            <button class="buy-now-btn"
                    data-product-id="<?php echo htmlspecialchars($product['id']); ?>"
                    data-product-name="<?php echo htmlspecialchars($product['product_name']); ?>"
                    data-product-price="<?php echo $product['price']; ?>"
                    data-product-image="<?php echo htmlspecialchars($product['image']); ?>"
                    <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>>
              <i class="bi bi-lightning-fill"></i>
              <?php echo ($product['stock'] <= 0) ? 'Unavailable' : 'Buy Now'; ?>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- RELATED PRODUCTS SECTION -->
    <?php if (!empty($related_products)): ?>
    <section class="related-products">
      <h2>You Might Also Like</h2>
      <div class="related-grid">
        <?php foreach ($related_products as $related): ?>
          <div class="related-card">
            <a href="ProductView.php?id=<?php echo $related['id']; ?>">
              <img src="<?php echo htmlspecialchars($related['image']); ?>" alt="<?php echo htmlspecialchars($related['product_name']); ?>">
              <h3><?php echo htmlspecialchars($related['product_name']); ?></h3>
              <p class="related-price">₱<?php echo number_format($related['price'], 2); ?></p>
              <p class="related-stock">Stock: <?php echo $related['stock']; ?></p>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>
  </main>

  <!-- AUTH MODAL -->
  <?php include 'components/auth_modal.php'; ?>

  <!-- FOOTER -->
  <footer class="site-footer">
    <!-- footer content here (unchanged) -->
  </footer>

  <!-- JAVASCRIPT -->
  <script src="Js/JavaScript.js"></script>
  <script>
    // PRODUCT VIEW FUNCTIONALITY
    const maxStock = <?php echo $product['stock']; ?>;
    
    // Image gallery functionality
    function changeImage(newSrc) {
      document.getElementById('mainProductImage').src = newSrc;
      
      // Update active thumbnail
      document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
      });
      event.target.classList.add('active');
    }
    
    // Quantity controls
    function updateQuantity(change) {
      const quantityInput = document.getElementById('quantity');
      let currentQty = parseInt(quantityInput.value);
      let newQty = currentQty + change;
      
      if (newQty < 1) newQty = 1;
      if (newQty > maxStock) newQty = maxStock;
      
      quantityInput.value = newQty;
      
      // Update button states
      document.getElementById('decreaseQty').disabled = (newQty <= 1);
      document.getElementById('increaseQty').disabled = (newQty >= maxStock);
    }
    
    // Add to Cart functionality
    document.addEventListener('DOMContentLoaded', function() {
      const addToCartBtn = document.querySelector('.add-to-cart-btn');
      const buyNowBtn = document.querySelector('.buy-now-btn');
      
      // Add to cart functionality is now handled by the global cart.js file
      
      if (buyNowBtn) {
        buyNowBtn.addEventListener('click', function() {
          const productId = this.getAttribute('data-product-id');
          const productName = this.getAttribute('data-product-name');
          const productPrice = this.getAttribute('data-product-price');
          const productImage = this.getAttribute('data-product-image');
          const quantity = parseInt(document.getElementById('quantity').value);
          
          buyNow(productId, productName, productPrice, productImage, quantity);
        });
      }
    });
    
    // addToCart function is now handled by the global cart.js file
    
    function buyNow(productId, productName, productPrice, productImage, quantity) {
      // Add to cart first using the global function, then redirect to checkout
      if (quantity > 1) {
        bulkAddToCart(productId, productName, productPrice, productImage, quantity);
      } else {
        addToCart(productId, productName, productPrice, productImage, quantity);
      }
      
      // Wait a moment for cart to update, then redirect
      setTimeout(() => {
        window.location.href = 'cart.php?checkout=1';
      }, 1500);
    }

    // MODAL FUNCTIONALITY (if login modal exists)
    const loginIcon = document.getElementById("loginIcon");
    const authModal = document.getElementById("authModal");
    const closeModalBtn = document.getElementById("closeModal");

    if (loginIcon && authModal) {
      loginIcon.addEventListener("click", () => {
        authModal.classList.add("active");
      });

      if (closeModalBtn) {
        closeModalBtn.addEventListener("click", () => {
          authModal.classList.remove("active");
        });
      }

      window.addEventListener("click", (e) => {
        if (e.target === authModal) authModal.classList.remove("active");
      });

      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") authModal.classList.remove("active");
      });
    }

    // Initialize quantity controls
    document.getElementById('decreaseQty').disabled = true; // Start disabled at qty 1
    if (maxStock <= 1) {
      document.getElementById('increaseQty').disabled = true;
    }
  </script>
  
  <!-- Scripts -->
  <script src="Js/cart.js"></script>
  <script src="components/auth_modal_otp.js"></script>
</body>
</html>
