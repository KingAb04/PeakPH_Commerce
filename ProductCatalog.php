<?php
session_start();

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Calculate total items in cart
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
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
    <input type="search" placeholder="Search...">
  </div>

  <div class="top-icons">
    <button id="loginIcon" class="login-btn">
      <i class="bi bi-person"></i>
      <span>Login</span>
    </button>
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
          <p class="results-count"><span id="resultsCount">8</span> products found</p>
        </div>
        <div class="products-grid">
          <div class="product-card" data-category="emergency" data-price="950">
            <div class="product-image">
              <img src="Assets/Healthproducts/dental care images/Top Performance ProDental Finger Brushes.png" alt="...">
            </div>
            <div class="product-info">
              <div class="product-rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star-half"></i>
                <span class="count">(4.0k)</span>
              </div>
              <h3>Emergency First Aid Kit</h3>
              <div class="product-price">
                <span class="current-price">P 950.00</span>
                <span class="original-price">P 1250.00</span>
              </div>
              <button class="add-to-cart" 
                      data-product-id="1" 
                      data-product-name="Emergency First Aid Kit" 
                      data-product-price="950.00" 
                      data-product-image="Assets/Healthproducts/dental care images/Top Performance ProDental Finger Brushes.png">
                <i class="fa-solid fa-cart-shopping"></i> Add to Cart
              </button>
            </div>
          </div>

          <div class="product-card" data-category="tents" data-price="1200">
            <div class="product-image">
              <img src="Assets/Gallery_Images/TentSample.jpg" alt="">
            </div>
            <div class="product-info">
              <div class="product-rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <span class="count">(3.2k)</span>
              </div>
              <h3>4-Person Camping Tent</h3>
              <div class="product-price">
                <span class="current-price">P 1,200.00</span>
              </div>
              <button class="add-to-cart"
                      data-product-id="2" 
                      data-product-name="4-Person Camping Tent" 
                      data-product-price="1200.00" 
                      data-product-image="Assets/Gallery_Images/TentSample.jpg">
                <i class="fa-solid fa-cart-shopping"></i> Add to Cart
              </button>
            </div>
          </div>

          <div class="product-card" data-category="cooking" data-price="750">
            <div class="product-image">
              <img src="Assets/Gallery_Images/CookingGearSample.png" alt="">
            </div>
            <div class="product-info">
              <div class="product-rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <span class="count">(1.8k)</span>
              </div>
              <h3>Portable Cooking Set</h3>
              <div class="product-price">
                <span class="current-price">P 750.00</span>
              </div>
              <button class="add-to-cart"
                      data-product-id="3" 
                      data-product-name="Portable Cooking Set" 
                      data-product-price="750.00" 
                      data-product-image="Assets/Gallery_Images/CookingGearSample.png">
                <i class="fa-solid fa-cart-shopping"></i> Add to Cart
              </button>
            </div>
          </div>

          <div class="product-card" data-category="cooking" data-price="450">
            <div class="product-image">
              <img src="Assets/Gallery_Images/Camping Stove Sample.png" alt="">
            </div>
            <div class="product-info">
              <div class="product-rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star-half"></i>
                <span class="count">(2.1k)</span>
              </div>
              <h3>Camping Stove</h3>
              <div class="product-price">
                <span class="current-price">P 450.00</span>
              </div>
              <button class="add-to-cart"
                      data-product-id="4" 
                      data-product-name="Camping Stove" 
                      data-product-price="450.00" 
                      data-product-image="Assets/Gallery_Images/Camping Stove Sample.png">
                <i class="fa-solid fa-cart-shopping"></i> Add to Cart
              </button>
            </div>
          </div>

          <div class="product-card" data-category="emergency" data-price="320">
            <div class="product-image">
              <img src="Assets/Gallery_Images/Survival Kit Sample.png" alt="">
            </div>
            <div class="product-info">
              <div class="product-rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <span class="count">(1.5k)</span>
              </div>
              <h3>Survival Kit</h3>
              <div class="product-price">
                <span class="current-price">P 320.00</span>
              </div>
              <button class="add-to-cart"
                      data-product-id="5" 
                      data-product-name="Survival Kit" 
                      data-product-price="320.00" 
                      data-product-image="Assets/Gallery_Images/Survival Kit Sample.png">
                <i class="fa-solid fa-cart-shopping"></i> Add to Cart
              </button>
            </div>
          </div>

          <div class="product-card" data-category="tents" data-price="890">
            <div class="product-image">
              <img src="Assets/Gallery_Images/HikingBackpackSample.png" alt="">
            </div>
            <div class="product-info">
              <div class="product-rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <span class="count">(2.3k)</span>
              </div>
              <h3>Hiking Backpack with Tent</h3>
              <div class="product-price">
                <span class="current-price">P 890.00</span>
              </div>
              <button class="add-to-cart"
                      data-product-id="6" 
                      data-product-name="Hiking Backpack with Tent" 
                      data-product-price="890.00" 
                      data-product-image="Assets/Gallery_Images/HikingBackpackSample.png">
                <i class="fa-solid fa-cart-shopping"></i> Add to Cart
              </button>
            </div>
          </div>

          <div class="product-card" data-category="emergency" data-price="180">
            <div class="product-image">
              <img src="Assets/Gallery_Images/TravelBootsSample.png" alt="">
            </div>
            <div class="product-info">
              <div class="product-rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <span class="count">(987)</span>
              </div>
              <h3>Emergency Travel Boots</h3>
              <div class="product-price">
                <span class="current-price">P 180.00</span>
              </div>
              <button class="add-to-cart"
                      data-product-id="7" 
                      data-product-name="Emergency Travel Boots" 
                      data-product-price="180.00" 
                      data-product-image="Assets/Gallery_Images/TravelBootsSample.png">
                <i class="fa-solid fa-cart-shopping"></i> Add to Cart
              </button>
            </div>
          </div>

          <div class="product-card" data-category="cooking" data-price="680">
            <div class="product-image">
              <img src="Assets/Gallery_Images/CookingGearSample.png" alt="">
            </div>
            <div class="product-info">
              <div class="product-rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star-half"></i>
                <span class="count">(1.4k)</span>
              </div>
              <h3>Complete Cooking Kit</h3>
              <div class="product-price">
                <span class="current-price">P 680.00</span>
              </div>
              <button class="add-to-cart"
                      data-product-id="8" 
                      data-product-name="Complete Cooking Kit" 
                      data-product-price="680.00" 
                      data-product-image="Assets/Gallery_Images/CookingGearSample.png">
                <i class="fa-solid fa-cart-shopping"></i> Add to Cart
              </button>
            </div>
          </div>

        </div>
      </section>
    </div>
  </main>

<script>
// JavaScript for add to cart functionality and filtering
document.addEventListener('DOMContentLoaded', function() {
  const addToCartButtons = document.querySelectorAll('.add-to-cart');
  const categoryFilters = document.querySelectorAll('input[name="category"]');
  const priceRange = document.getElementById('priceRange');
  const priceValue = document.getElementById('priceValue');
  const clearFiltersBtn = document.querySelector('.clear-filters');
  const resultsCount = document.getElementById('resultsCount');
  
  // Add to cart functionality
  addToCartButtons.forEach(button => {
    button.addEventListener('click', function() {
      const productId = this.getAttribute('data-product-id') || 'demo_' + Date.now();
      const productName = this.getAttribute('data-product-name') || 'Sample Product';
      const productPrice = this.getAttribute('data-product-price') || '850.00';
      const productImage = this.getAttribute('data-product-image') || 'Assets/placeholder.jpg';
      
      // Create form data
      const formData = new FormData();
      formData.append('product_id', productId);
      formData.append('product_name', productName);
      formData.append('product_price', productPrice);
      formData.append('product_image', productImage);
      formData.append('quantity', 1);
      
      // Send AJAX request to add item to cart
      fetch('add_to_cart.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if(data.success) {
          // Update cart count
          const cartCount = document.querySelector('.cart-count');
          if (cartCount) {
            cartCount.textContent = data.cart_count;
          }
          
          // Show success message
          alert(`${data.product_name || productName} added to cart!`);
        } else {
          alert('Failed to add product to cart: ' + (data.message || 'Unknown error'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error adding product to cart');
      });
    });
  });
  
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
  
  // Initial filter
  filterProducts();
});
</script>
</body>
</html>