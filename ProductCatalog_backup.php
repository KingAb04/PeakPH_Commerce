<!DOCTYPE html>
<html lang="en">
<he  <div class="top-icons">
    <button id="loginIcon" class="login-btn">
      <i class="bi bi-person"></i>
      <span>Login</span>
    </button>
    <a href="cart.php" class="cart-link">
      <i class="bi bi-cart">
        <span class="cart-count"><?php echo $cart_count; ?></span>
      </i>
    </a>
  </div>eta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>PeakPH: Browse Items</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="Css/prod.css" />
</head>
<body><?php
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
    </button>s
    <i class="bi bi-cart">
      <span class="cart-count">0</span>
    </i>
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
      <!-- Best Seller Products -->
      <section class="products-section">
        <div class="section-title">
          <h2>Best Seller</h2>
        </div>
        <div class="products-grid">
          <div class="product-card">
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
              <h3>Top Performance ProDental Finger Brushes</h3>
              <div class="product-price">
                <span class="current-price">P 950.00</span>
                <span class="original-price">P 1250.00</span>
              </div>
              <button class="add-to-cart" 
                      data-product-id="1" 
                      data-product-name="Top Performance ProDental Finger Brushes" 
                      data-product-price="950.00" 
                      data-product-image="Assets/Healthproducts/dental care images/Top Performance ProDental Finger Brushes.png">
                <i class="fa-solid fa-cart-shopping"></i> Add to Cart
              </button>
            </div>
          </div>

          <div class="product-card">
            <div class="product-image">
              <img src="Assets/Healthproducts/Vitamins images/PET EYEZ Freeze Dried Vitamin Cat Treats.png" alt="">
            </div>
            <div class="product-info">
              <div class="product-rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <span class="count">(2.0k)</span>
              </div>
              <h3>PET EYEZ Freeze Dried Vitamin Cat Treats</h3>
              <div class="product-price">
                <span class="current-price">P 850</span>
              </div>
              <button class="add-to-cart">
                <i class="fa-solid fa-cart-shopping"></i> Add to Cart
              </button>
            </div>
          </div>

          <div class="product-card">
            <div class="product-image">
              <img src="Assets/Healthproducts/Vitamins images/PET EYEZ Freeze Dried Vitamin Cat Treats.png" alt="">
            </div>
            <div class="product-info">
              <div class="product-rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <span class="count">(2.0k)</span>
              </div>
              <h3>PET EYEZ Freeze Dried Vitamin Cat Treats</h3>
              <div class="product-price">
                <span class="current-price">P 850</span>
              </div>
              <button class="add-to-cart">
                <i class="fa-solid fa-cart-shopping"></i> Add to Cart
              </button>
            </div>
          </div>

          <div class="product-card">
            <div class="product-image">
              <img src="Assets/Healthproducts/Vitamins images/PET EYEZ Freeze Dried Vitamin Cat Treats.png" alt="">
            </div>
            <div class="product-info">
              <div class="product-rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <span class="count">(2.0k)</span>
              </div>
              <h3>PET EYEZ Freeze Dried Vitamin Cat Treats</h3>
              <div class="product-price">
                <span class="current-price">P 850</span>
              </div>
              <button class="add-to-cart">
                <i class="fa-solid fa-cart-shopping"></i> Add to Cart
              </button>
            </div>
          </div>

          <div class="product-card">
            <div class="product-image">
              <img src="Assets/Healthproducts/Vitamins images/PET EYEZ Freeze Dried Vitamin Cat Treats.png" alt="">
            </div>
            <div class="product-info">
              <div class="product-rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <span class="count">(2.0k)</span>
              </div>
              <h3>PET EYEZ Freeze Dried Vitamin Cat Treats</h3>
              <div class="product-price">
                <span class="current-price">P 850</span>
              </div>
              <button class="add-to-cart">
                <i class="fa-solid fa-cart-shopping"></i> Add to Cart
              </button>
            </div>
          </div>

          <div class="product-card">
            <div class="product-image">
              <img src="Assets/Healthproducts/Vitamins images/PET EYEZ Freeze Dried Vitamin Cat Treats.png" alt="">
            </div>
            <div class="product-info">
              <div class="product-rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <span class="count">(2.0k)</span>
              </div>
              <h3>PET EYEZ Freeze Dried Vitamin Cat Treats</h3>
              <div class="product-price">
                <span class="current-price">P 850</span>
              </div>
              <button class="add-to-cart">
                <i class="fa-solid fa-cart-shopping"></i> Add to Cart
              </button>
            </div>
          </div>

          <div class="product-card">
            <div class="product-image">
              <img src="Assets/Healthproducts/Vitamins images/PET EYEZ Freeze Dried Vitamin Cat Treats.png" alt="">
            </div>
            <div class="product-info">
              <div class="product-rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <span class="count">(2.0k)</span>
              </div>
              <h3>PET EYEZ Freeze Dried Vitamin Cat Treats</h3>
              <div class="product-price">
                <span class="current-price">P 850</span>
              </div>
              <button class="add-to-cart">
                <i class="fa-solid fa-cart-shopping"></i> Add to Cart
              </button>
            </div>
          </div>

          <div class="product-card">
            <div class="product-image">
              <img src="Assets/Healthproducts/Vitamins images/PET EYEZ Freeze Dried Vitamin Cat Treats.png" alt="">
            </div>
            <div class="product-info">
              <div class="product-rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <span class="count">(2.0k)</span>
              </div>
              <h3>PET EYEZ Freeze Dried Vitamin Cat Treats</h3>
              <div class="product-price">
                <span class="current-price">P 850</span>
              </div>
              <button class="add-to-cart">
                <i class="fa-solid fa-cart-shopping"></i> Add to Cart
              </button>
            </div>
          </div>



        </div>
            
            
<script>
// JavaScript for add to cart functionality
document.addEventListener('DOMContentLoaded', function() {
  const addToCartButtons = document.querySelectorAll('.add-to-cart');
  
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
});
</script>
</body>
</html>
