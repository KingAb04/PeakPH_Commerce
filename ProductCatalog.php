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

// Get products from database
$products = [];
$use_database = false;

if (isDatabaseConnected()) {
    try {
        $query = "SELECT id, product_name as name, price, image, tag, label, stock FROM inventory WHERE stock > 0 ORDER BY created_at DESC";
        $result = executeQuery($query);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Fix image path for database products
                $image_path = 'Assets/placeholder.svg';
                if (!empty($row['image'])) {
                    // Check if image exists, if not use placeholder
                    if (file_exists('admin/' . $row['image'])) {
                        $image_path = 'admin/' . $row['image'];
                    } elseif (file_exists($row['image'])) {
                        $image_path = $row['image'];
                    }
                }
                
                $products[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'price' => number_format($row['price'], 2),
                    'price_raw' => $row['price'],
                    'image' => $image_path,
                    'category' => strtolower($row['tag'] ?? 'other'),
                    'badge' => $row['label'] ?? 'In Stock',
                    'rating' => '⭐⭐⭐⭐☆',
                    'reviews' => '(' . rand(50, 500) . ')',
                    'stock' => $row['stock'],
                    'is_database' => true
                ];
            }
            $use_database = true;
        }
    } catch (Exception $e) {
        error_log('ProductCatalog database error: ' . $e->getMessage());
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>PeakPH: Browse Items</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="Css/Global.css" />
  <link rel="stylesheet" href="Css/prod.css" />
</head>
<body>
   <header>
  <div class="top-navbar">
  <div class="brand">
    <a href="index.php" class="logo-btn">
      <img src="Assets/Carousel_Picts/Logo.png" alt="Brand Logo">
    </a>
  </div>

  <div class="search-wrapper">
    <i class="bi bi-search"></i>
    <input type="search" id="productSearch" placeholder="Search products...">
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
	

  <main class="main-content">
    <div class="catalog-container">
      <!-- Filter Sidebar -->
      <aside class="filter-sidebar">
        <div class="filter-section">
          <h3>Filter by Category</h3>
          <div class="filter-options">
            <label class="filter-option">
              <input type="radio" name="category" value="all" checked>
              <span class="checkmark"></span>
              All Products
            </label>
            <label class="filter-option">
              <input type="radio" name="category" value="tents">
              <span class="checkmark"></span>
              Tents
            </label>
            <label class="filter-option">
              <input type="radio" name="category" value="cooking">
              <span class="checkmark"></span>
              Cooking Equipment
            </label>
            <label class="filter-option">
              <input type="radio" name="category" value="emergency">
              <span class="checkmark"></span>
              Emergency Kits/Tools
            </label>
          </div>
        </div>
        
        <div class="filter-section">
          <h3>Price Range</h3>
          <div class="price-range">
            <input type="range" id="priceRange" min="0" max="2000" value="2000" step="50">
            <div class="price-display">
              <span>₱0 - ₱<span id="priceValue">2000</span></span>
            </div>
          </div>
        </div>
        
        <button class="clear-filters">Clear All Filters</button>
      </aside>

      <!-- Products Section -->
      <section class="products-section">
        <div class="section-title">
          <h2>Product Catalog</h2>
          <?php if (!$use_database && !empty($products)): ?>
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 5px; margin: 10px 0; color: #856404;">
              <i class="bi bi-info-circle"></i> <strong>Demo Mode:</strong> Showing sample products. Add real products in the <a href="admin/inventory/inventory.php" target="_blank">admin panel</a>.
            </div>
          <?php endif; ?>
          <p class="results-count"><span id="resultsCount"><?php echo count($products); ?></span> products found</p>
        </div>
        <div class="products-grid">
          <?php foreach ($products as $product): ?>
            <div class="product-card" data-category="<?php echo htmlspecialchars($product['category']); ?>" data-price="<?php echo $product['price_raw']; ?>">
              <a href="ProductView.php?id=<?php echo urlencode($product['id']); ?>" class="product-link">
                <div class="product-image">
                  <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                  
                  <?php 
                  // Add product badges
                  $badge_class = '';
                  $badge_text = '';
                  
                  if (isset($product['badge']) && strtolower($product['badge']) === 'new arrival') {
                    $badge_class = 'new';
                    $badge_text = 'New';
                  } elseif (isset($product['category']) && strpos(strtolower($product['name']), 'blue') !== false) {
                    $badge_class = 'blue-product';
                    $badge_text = 'BLUE PRODUCT';
                  }
                  
                  if ($badge_text): ?>
                    <div class="product-badge <?php echo $badge_class; ?>">
                      <?php echo $badge_text; ?>
                    </div>
                  <?php endif; ?>
                  
                  <?php if ($product['stock'] < 10): ?>
                    <div style="position: absolute; top: 5px; right: 5px; background: #e74c3c; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.8em;">
                      Low Stock!
                    </div>
                  <?php endif; ?>
                </div>
                <div class="product-info">
                  <div class="product-rating">
                    <span style="color: #ffc107;"><?php echo $product['rating']; ?></span>
                    <span class="count"><?php echo $product['reviews']; ?></span>
                  </div>
                  <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                  <div class="product-price">
                    <span class="current-price">₱ <?php echo $product['price']; ?></span>
                  </div>
                  <div class="product-meta">
                    <?php echo htmlspecialchars($product['badge']); ?>
                    Stock: <?php echo $product['stock']; ?>
                  </div>
                </div>
              </a>
              
              <!-- Product Action Icons -->
              <div class="product-actions">
                <button class="add-to-wishlist" 
                        data-product-id="<?php echo htmlspecialchars($product['id']); ?>"
                        title="Add to Wishlist">
                  <i class="bi bi-heart"></i>
                </button>
                <button class="add-to-cart" 
                        data-product-id="<?php echo htmlspecialchars($product['id']); ?>" 
                        data-product-name="<?php echo htmlspecialchars($product['name']); ?>" 
                        data-product-price="<?php echo $product['price_raw']; ?>" 
                        data-product-image="<?php echo htmlspecialchars($product['image']); ?>"
                        <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>
                        title="<?php echo ($product['stock'] <= 0) ? 'Out of Stock' : 'Add to Cart'; ?>">
                  <i class="fa-solid fa-cart-shopping"></i>
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    </div>
  </main>

  <!-- AUTH MODAL -->
  <?php include 'components/auth_modal.php'; ?>

<script>
// JavaScript for add to cart functionality and filtering
document.addEventListener('DOMContentLoaded', function() {
  const addToCartButtons = document.querySelectorAll('.add-to-cart');
  const categoryFilters = document.querySelectorAll('input[name="category"]');
  const priceRange = document.getElementById('priceRange');
  const priceValue = document.getElementById('priceValue');
  const clearFiltersBtn = document.querySelector('.clear-filters');
  const resultsCount = document.getElementById('resultsCount');
  const searchInput = document.getElementById('productSearch');
  
  // Add to cart functionality is now handled by the global cart.js file
  
  // Filter functionality
  function filterProducts() {
    const selectedCategory = document.querySelector('input[name="category"]:checked').value;
    const maxPrice = parseInt(priceRange.value);
    const productCards = document.querySelectorAll('.product-card');
    let visibleCount = 0;
    
    productCards.forEach(card => {
      const category = card.getAttribute('data-category');
      const price = parseInt(card.getAttribute('data-price'));
      
      const categoryMatch = selectedCategory === 'all' || category === selectedCategory;
      const priceMatch = price <= maxPrice;
      
      if (categoryMatch && priceMatch) {
        card.style.display = 'block';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });
    
    resultsCount.textContent = visibleCount;
  }
  
  // Category filter listeners
  categoryFilters.forEach(filter => {
    filter.addEventListener('change', filterProducts);
  });
  
  // Price range listener
  priceRange.addEventListener('input', function() {
    priceValue.textContent = this.value;
    filterProducts();
  });
  
  // Clear filters
  clearFiltersBtn.addEventListener('click', function() {
    document.querySelector('input[value="all"]').checked = true;
    priceRange.value = 2000;
    priceValue.textContent = 2000;
    filterProducts();
  });
  
  // Search functionality
  let searchTimeout;
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        const searchTerm = this.value.trim();
        if (searchTerm.length >= 2) {
          performSearch(searchTerm);
        } else if (searchTerm.length === 0) {
          // Reset to show all products
          location.reload();
        }
      }, 300);
    });
  }
  
  function performSearch(searchTerm) {
    const selectedCategory = document.querySelector('input[name="category"]:checked').value;
    const maxPrice = parseInt(priceRange.value);
    
    fetch(`search_products.php?q=${encodeURIComponent(searchTerm)}&category=${selectedCategory}&max_price=${maxPrice}`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          updateProductGrid(data.products);
          resultsCount.textContent = data.count;
        }
      })
      .catch(error => {
        console.error('Search error:', error);
      });
  }
  
  function updateProductGrid(products) {
    const productsGrid = document.querySelector('.products-grid');
    productsGrid.innerHTML = '';
    
    products.forEach(product => {
      const productCard = createProductCard(product);
      productsGrid.appendChild(productCard);
    });
    
    // Reattach event listeners to new add-to-cart buttons
    refreshCartButtons();
  }
  
  function createProductCard(product) {
    const card = document.createElement('div');
    card.className = 'product-card';
    card.setAttribute('data-category', product.category);
    card.setAttribute('data-price', product.price_raw);
    
    card.innerHTML = `
      <a href="ProductView.php?id=${encodeURIComponent(product.id)}" class="product-link">
        <div class="product-image">
          <img src="${product.image}" alt="${product.name}">
          ${product.stock < 10 ? '<div style="position: absolute; top: 5px; right: 5px; background: #e74c3c; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.8em;">Low Stock!</div>' : ''}
        </div>
        <div class="product-info">
          <div class="product-rating">
            <span style="color: #ffc107;">${product.rating}</span>
            <span class="count">${product.reviews}</span>
          </div>
          <h3>${product.name}</h3>
          <div class="product-price">
            <span class="current-price">₱ ${product.price}</span>
          </div>
          <div class="product-meta">
            ${product.badge} Stock: ${product.stock}
          </div>
        </div>
      </a>
      <div class="product-actions">
        <button class="add-to-wishlist" 
                data-product-id="${product.id}"
                title="Add to Wishlist">
          <i class="bi bi-heart"></i>
        </button>
        <button class="add-to-cart" 
                data-product-id="${product.id}" 
                data-product-name="${product.name}" 
                data-product-price="${product.price_raw}" 
                data-product-image="${product.image}"
                ${product.stock <= 0 ? 'disabled' : ''}
                title="${product.stock <= 0 ? 'Out of Stock' : 'Add to Cart'}">
          <i class="fa-solid fa-cart-shopping"></i>
        </button>
      </div>
    `;
    
    return card;
  }
  
  // Cart functionality is now handled by the global cart.js file
  
  // Initial filter
  filterProducts();
  
  // Update initial count
  resultsCount.textContent = document.querySelectorAll('.product-card').length;
  
  // Wishlist functionality
  function initWishlistButtons() {
    const wishlistButtons = document.querySelectorAll('.add-to-wishlist');
    const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    
    // Mark already wishlisted items
    wishlistButtons.forEach(btn => {
      const productId = btn.getAttribute('data-product-id');
      if (wishlist.includes(productId)) {
        btn.classList.add('active');
        btn.querySelector('i').classList.remove('bi-heart');
        btn.querySelector('i').classList.add('bi-heart-fill');
      }
      
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleWishlist(this);
      });
    });
  }
  
  function toggleWishlist(button) {
    const productId = button.getAttribute('data-product-id');
    let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    const icon = button.querySelector('i');
    
    if (wishlist.includes(productId)) {
      // Remove from wishlist
      wishlist = wishlist.filter(id => id !== productId);
      button.classList.remove('active');
      icon.classList.remove('bi-heart-fill');
      icon.classList.add('bi-heart');
      showNotification('Removed from wishlist', 'info');
    } else {
      // Add to wishlist
      wishlist.push(productId);
      button.classList.add('active');
      icon.classList.remove('bi-heart');
      icon.classList.add('bi-heart-fill');
      showNotification('Added to wishlist!', 'success');
    }
    
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
  }
  
  function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      background: ${type === 'success' ? '#10b981' : '#6b7280'};
      color: white;
      padding: 12px 20px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 10000;
      font-size: 14px;
      animation: slideIn 0.3s ease;
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remove after 2 seconds
    setTimeout(() => {
      notification.style.animation = 'slideOut 0.3s ease';
      setTimeout(() => notification.remove(), 300);
    }, 2000);
  }
  
  // Initialize wishlist buttons
  initWishlistButtons();
  
  // Re-initialize wishlist buttons after filtering/searching
  const originalRefreshCartButtons = window.refreshCartButtons || function() {};
  window.refreshCartButtons = function() {
    originalRefreshCartButtons();
    initWishlistButtons();
  };
});
</script>

<!-- Scripts -->
<script src="Js/user_dropdown.js"></script>
<script src="Js/cart.js"></script>
<script src="components/auth_modal_otp.js"></script>
</body>
</html>