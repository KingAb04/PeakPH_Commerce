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

// Get current user info
$current_user = getCurrentUser();
$is_logged_in = isUserLoggedIn();

// Load Best Seller products from database
$bestSellerProducts = [];
if (isDatabaseConnected()) {
    try {
        $query = "SELECT id, product_name as name, price, image, tag, stock, label, created_at 
                  FROM inventory 
                  WHERE label LIKE '%Best%' OR label LIKE '%🏆%' OR label LIKE '%Bestseller%'
                  ORDER BY created_at DESC
                  LIMIT 10";
        $result = executeQuery($query);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $reviewCount = rand(100, 500);
                $rating = number_format(rand(35, 50) / 10, 1);
                $stars = str_repeat('⭐', floor($rating)) . (($rating - floor($rating)) >= 0.5 ? '☆' : '');
                
                $image_path = 'Assets/placeholder.svg';
                if (!empty($row['image'])) {
                    if (file_exists('admin/' . $row['image'])) {
                        $image_path = 'admin/' . $row['image'];
                    } elseif (file_exists($row['image'])) {
                        $image_path = $row['image'];
                    }
                }
                
                $bestSellerProducts[] = [
                    'link' => 'ProductView.php?id=' . $row['id'],
                    'image' => $image_path,
                    'alt' => $row['name'],
                    'badge' => $row['label'] ?? 'Best Seller',
                    'name' => $row['name'],
                    'desc' => $row['tag'] ? ucfirst($row['tag']) : 'Premium Product',
                    'rating' => $rating,
                    'reviews' => $reviewCount,
                    'price' => number_format($row['price'], 2),
                    'stock' => $row['stock']
                ];
            }
        }
    } catch (Exception $e) {
        error_log('Best Seller loading error: ' . $e->getMessage());
    }
}

// Load New Arrivals from database
$newArrivals = [];
if (isDatabaseConnected()) {
    try {
        $query = "SELECT id, product_name as name, price, image, tag, stock, label, created_at 
                  FROM inventory 
                  WHERE label LIKE '%New%' OR label LIKE '%🆕%' OR label LIKE '%Arrival%'
                  ORDER BY created_at DESC
                  LIMIT 10";
        $result = executeQuery($query);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $image_path = 'Assets/placeholder.svg';
                if (!empty($row['image'])) {
                    if (file_exists('admin/' . $row['image'])) {
                        $image_path = 'admin/' . $row['image'];
                    } elseif (file_exists($row['image'])) {
                        $image_path = $row['image'];
                    }
                }
                
                $newArrivals[] = [
                    'link' => 'ProductView.php?id=' . $row['id'],
                    'image' => $image_path,
                    'alt' => $row['name'],
                    'name' => $row['name'],
                    'price' => number_format($row['price'], 2)
                ];
            }
        }
    } catch (Exception $e) {
        error_log('New Arrivals loading error: ' . $e->getMessage());
    }
}

// Fallback to old data files if database is not available
if (empty($bestSellerProducts)) {
    $promoCard = @include __DIR__ . '/admin/content/bestseller_data.php';
    if (!empty($promoCard['products'])) {
        $bestSellerProducts = $promoCard['products'];
    }
}

if (empty($newArrivals)) {
    $arrivalsData = @include __DIR__ . '/admin/content/new_arrivals_data.php';
    if (!empty($arrivalsData['arrivals'])) {
        $newArrivals = $arrivalsData['arrivals'];
    }
}

// Promo card data (still using file for promo content)
$promoCard = @include __DIR__ . '/admin/content/bestseller_data.php';
if (!$promoCard) {
    $promoCard = [
        'title' => 'Adventure Awaits!',
        'subtitle' => 'Gear up for your next outdoor expedition',
        'offer_tag' => 'Special Offer',
        'offer' => '20% OFF',
        'offer_desc' => 'on all camping gear',
        'details' => 'Limited time offer',
        'button_link' => 'ProductCatalog.php',
        'button_text' => 'Shop Now →'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PeakPH: Camping Gears and More</title>

  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

  <!-- Global Styles -->
  <link rel="stylesheet" href="Css/Global.css" />
  <link rel="stylesheet" href="Css/landingcomponents.css" />
  <link rel="stylesheet" href="Css/carousel.css" />

  <!-- Google API -->
  <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>

<body>
  <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
    <div class="alert alert-success" id="logoutMessage">
      You have been successfully logged out.
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['login']) && $_GET['login'] === 'success'): ?>
    <div class="alert alert-success" id="loginMessage">
      Welcome back! You have been successfully logged in.
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['login']) && $_GET['login'] === 'failed'): ?>
    <div class="alert alert-error" id="loginErrorMessage">
      <?php echo htmlspecialchars($_GET['error'] ?? 'Login failed. Please try again.'); ?>
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['signup']) && $_GET['signup'] === 'success'): ?>
    <div class="alert alert-success" id="signupMessage">
      Account created successfully! Welcome to PeakPH!
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['signup']) && $_GET['signup'] === 'failed'): ?>
    <div class="alert alert-error" id="signupErrorMessage">
      <?php echo htmlspecialchars($_GET['error'] ?? 'Signup failed. Please try again.'); ?>
    </div>
  <?php endif; ?>

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

    <!-- Bottom Navbar -->
    <div class="bottom-navbar">
      <nav>
        <a href="ProductCatalog.php">Shop</a>
        <a href="pages/climbers-community.php">Community</a>
        <a href="#contact">Contact Us</a>
        <a href="#deals" class="best-deals">Best Deals</a>
        <a href="#about">About us</a>
      </nav>
    </div>
  </header>

  <!-- HERO -->
<?php
$carouselSlides = include __DIR__ . '/admin/content/carousel_data.php';
?>
<div class="hero">
    <div class="slides" id="slides">
        <?php foreach ($carouselSlides as $i => $slide): ?>
            <div class="slide <?= htmlspecialchars($slide['class']) ?>" style="background-image: url('<?= htmlspecialchars($slide['image']) ?>')">
                <?php if (!empty($slide['link']) && !empty($slide['button'])): ?>
                    <a href="<?= htmlspecialchars($slide['link']) ?>" class="shop-btn"><?= htmlspecialchars($slide['button']) ?></a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <button class="arrow prev" onclick="moveSlide(-1)">‹</button>
    <button class="arrow next" onclick="moveSlide(1)">›</button>
</div>
                  
  <!-- MID CONTAINER -->
  <section class="best-seller">
  <h2>Best Sellers</h2>
  <div class="best-seller-flex">
    <!-- Promotional Content -->
    <div class="promo-content">
      <h3><?= htmlspecialchars($promoCard['title'] ?? 'Adventure Awaits!') ?></h3>
      <p class="promo-text"><?= htmlspecialchars($promoCard['subtitle'] ?? 'Gear up for your next outdoor expedition with our top-rated camping essentials.') ?></p>
      <div class="special-offer">
        <span class="offer-tag"><?= htmlspecialchars($promoCard['offer_tag'] ?? 'Special Offer') ?></span>
        <h4><?= htmlspecialchars($promoCard['offer'] ?? '20% OFF') ?></h4>
        <p><?= htmlspecialchars($promoCard['offer_desc'] ?? 'on all camping gear') ?></p>
      </div>
      <p class="promo-details"><?= htmlspecialchars($promoCard['details'] ?? 'Limited time offer for outdoor enthusiasts. Quality equipment for unforgettable adventures.') ?></p>
      <a href="<?= htmlspecialchars($promoCard['button_link'] ?? 'ProductCatalog.php') ?>" class="promo-cta">
        <?= htmlspecialchars($promoCard['button_text'] ?? 'Shop Now →') ?>
      </a>
    </div>

    <!-- Best Seller Grid Slider -->
    <div class="seller-slider-container" style="position:relative; flex:1; min-width:0;">
      <button class="slider-arrow left" id="sellerSliderLeft" style="position:absolute; left:-18px; top:50%; transform:translateY(-50%); background:#fff; border:none; border-radius:50%; box-shadow:0 2px 8px rgba(0,0,0,0.08); width:36px; height:36px; display:none; align-items:center; justify-content:center; z-index:2; cursor:pointer;"><i class="bi bi-chevron-left"></i></button>
      <div class="seller-grid" id="sellerGrid" style="overflow-x:auto; display:flex; gap:20px; scroll-behavior:smooth; padding-bottom:8px; scrollbar-width:none; -ms-overflow-style:none;">
        <?php if (!empty($bestSellerProducts)): ?>
          <?php foreach ($bestSellerProducts as $product): ?>
            <div class="seller-card" style="min-width:270px; max-width:300px; flex:0 0 270px; position: relative;">
              <a href="<?= htmlspecialchars($product['link']) ?>" class="card-link">
                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['alt']) ?>" />
                <span class="badge"><?= htmlspecialchars($product['badge']) ?></span>
                <p class="product-name"><?= htmlspecialchars($product['name']) ?></p>
                <p class="product-desc"><?= htmlspecialchars($product['desc']) ?></p>
                <div class="rating">
                  <?php
                    $rating = isset($product['rating']) ? (float)$product['rating'] : 0;
                    $fullStars = floor($rating);
                    $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
                    $emptyStars = 5 - $fullStars - $halfStar;
                    echo str_repeat('⭐', $fullStars);
                    if ($halfStar) echo '☆';
                    echo str_repeat('☆', $emptyStars);
                  ?>
                  <span class="review-count">(<?= htmlspecialchars($product['reviews']) ?>)</span>
                </div>
                <span class="price">₱<?= htmlspecialchars($product['price']) ?></span>
              </a>
              
              <!-- Action Icons -->
              <div class="card-actions">
                <button class="wishlist-btn" onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist(<?= $product['link'] ? str_replace('ProductView.php?id=', '', $product['link']) : '0' ?>);" title="Add to Wishlist">
                  <i class="bi bi-heart"></i>
                </button>
                <button class="cart-btn" onclick="event.preventDefault(); event.stopPropagation(); addToCart(<?= $product['link'] ? str_replace('ProductView.php?id=', '', $product['link']) : '0' ?>);" title="Add to Cart">
                  <i class="bi bi-bag-plus"></i>
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div style="color:#888; font-size:1.1em; padding:30px 0;">
            No best seller products available. Go to <a href="admin/inventory/inventory.php">Inventory</a> to label products.
          </div>
        <?php endif; ?>
      </div>
      <button class="slider-arrow right" id="sellerSliderRight" style="position:absolute; right:-18px; top:50%; transform:translateY(-50%); background:#fff; border:none; border-radius:50%; box-shadow:0 2px 8px rgba(0,0,0,0.08); width:36px; height:36px; display:none; align-items:center; justify-content:center; z-index:2; cursor:pointer;"><i class="bi bi-chevron-right"></i></button>
    </div>
  <style>
    .seller-slider-container .slider-arrow { transition: background 0.2s; }
    .seller-slider-container .slider-arrow:active { background: #e0e0e0; }
    .seller-grid::-webkit-scrollbar { display: none; }
    @media (max-width: 900px) {
      .seller-slider-container .slider-arrow { display:none!important; }
    }
    
    /* Card Actions Styles */
    .card-actions {
      position: absolute;
      bottom: 10px;
      right: 10px;
      display: flex;
      flex-direction: row;
      gap: 8px;
      opacity: 1 !important;
      transform: translateX(0) !important;
      transition: all 0.3s ease;
      z-index: 10;
    }
    
    /* Always show icons for better visibility */
    .seller-card .card-actions,
    .arrival-card .card-actions {
      opacity: 1 !important;
      transform: translateX(0) !important;
    }
    
    .wishlist-btn,
    .cart-btn {
      width: 36px !important;
      height: 36px !important;
      border: none !important;
      border-radius: 50% !important;
      background: rgba(255, 255, 255, 0.95) !important;
      color: #666 !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      cursor: pointer !important;
      transition: all 0.3s ease !important;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important;
      backdrop-filter: blur(10px) !important;
      font-size: 16px !important;
      position: relative !important;
    }
    
    .wishlist-btn:hover {
      background: #ff6b6b !important;
      color: white !important;
      transform: scale(1.05) !important;
    }
    
    .wishlist-btn.active {
      background: #ff6b6b !important;
      color: white !important;
    }
    
    .cart-btn:hover {
      background: #2e765e !important;
      color: white !important;
      transform: scale(1.05) !important;
    }
    
    .cart-btn.added {
      background: #28a745 !important;
      color: white !important;
    }
    
    .card-link {
      display: block;
      text-decoration: none;
      color: inherit;
      padding-bottom: 50px; /* Make space for bottom icons */
    }
    
    /* Ensure cards have proper positioning */
    .seller-card,
    .arrival-card {
      position: relative !important;
      overflow: hidden !important;
      min-width: 270px !important;
      max-width: 300px !important;
      flex: 0 0 270px !important;
    }
    
    /* Adjust card content to make space for icons */
    .seller-card .price,
    .arrival-card .price {
      margin-bottom: 40px !important;
    }
    
    @media (max-width: 768px) {
      .card-actions {
        opacity: 1;
        transform: translateX(0);
        bottom: 5px;
        right: 5px;
      }
      
      .wishlist-btn,
      .cart-btn {
        width: 32px !important;
        height: 32px !important;
        font-size: 14px !important;
      }
    }
  </style>
  <script>
    // Card slider logic for best sellers
    document.addEventListener('DOMContentLoaded', function() {
      const grid = document.getElementById('sellerGrid');
      const leftBtn = document.getElementById('sellerSliderLeft');
      const rightBtn = document.getElementById('sellerSliderRight');
      function updateArrows() {
        if (!grid) return;
        // Show arrows only if more than 4 cards
        const cardCount = grid.querySelectorAll('.seller-card').length;
        if (cardCount > 4) {
          leftBtn.style.display = 'flex';
          rightBtn.style.display = 'flex';
        } else {
          leftBtn.style.display = 'none';
          rightBtn.style.display = 'none';
        }
      }
      function scrollGrid(dir) {
        if (!grid) return;
        // Scroll by the width of one card (plus gap)
        const card = grid.querySelector('.seller-card');
        if (card) {
          const scrollAmount = card.offsetWidth + 20; // 20px gap
          grid.scrollBy({ left: dir * scrollAmount, behavior: 'smooth' });
        }
      }
      if (leftBtn && rightBtn) {
        leftBtn.addEventListener('click', () => scrollGrid(-1));
        rightBtn.addEventListener('click', () => scrollGrid(1));
      }
      updateArrows();
      window.addEventListener('resize', updateArrows);
    });
  </script>
  </div>
</section>

  <!-- NEW ARRIVALS -->
  <section class="new-arrivals">
    <h2 style="color: white">New Arrivals</h2>
    <div class="arrivals-slider-container">
      <button class="arrivals-slider-arrow left" id="arrivalsSliderLeft">
        <i class="bi bi-chevron-left"></i>
      </button>
      <div class="arrivals-grid" id="arrivalsGrid">
        <?php if (!empty($newArrivals)): ?>
          <?php foreach ($newArrivals as $arrival): ?>
            <div class="arrival-card" style="position: relative;">
              <a href="<?= htmlspecialchars($arrival['link']) ?>" class="card-link">
                <img src="<?= htmlspecialchars($arrival['image']) ?>" alt="<?= htmlspecialchars($arrival['alt']) ?>" />
                <p class="product-name"><?= htmlspecialchars($arrival['name']) ?></p>
                <span class="price">₱<?= htmlspecialchars($arrival['price']) ?></span>
              </a>
              
              <!-- Action Icons -->
              <div class="card-actions">
                <button class="wishlist-btn" onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist(<?= $arrival['link'] ? str_replace('ProductView.php?id=', '', $arrival['link']) : '0' ?>);" title="Add to Wishlist">
                  <i class="bi bi-heart"></i>
                </button>
                <button class="cart-btn" onclick="event.preventDefault(); event.stopPropagation(); addToCart(<?= $arrival['link'] ? str_replace('ProductView.php?id=', '', $arrival['link']) : '0' ?>);" title="Add to Cart">
                  <i class="bi bi-bag-plus"></i>
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div style="color:#888; font-size:1.1em; padding:30px 0;">
            No new arrivals available. Go to <a href="admin/inventory/inventory.php">Inventory</a> to label products.
          </div>
        <?php endif; ?>
      </div>
      <button class="arrivals-slider-arrow right" id="arrivalsSliderRight">
        <i class="bi bi-chevron-right"></i>
      </button>
    </div>
  </section>

  <script>
    // New Arrivals slider logic
    document.addEventListener('DOMContentLoaded', function() {
      const arrivalsGrid = document.getElementById('arrivalsGrid');
      const leftBtn = document.getElementById('arrivalsSliderLeft');
      const rightBtn = document.getElementById('arrivalsSliderRight');

      function updateArrivalsSlider() {
        if (!arrivalsGrid) return;
        
        const cardCount = arrivalsGrid.querySelectorAll('.arrival-card').length;
        
        if (cardCount <= 4) {
          // Use grid layout for 4 or fewer cards
          arrivalsGrid.classList.add('grid-layout');
          leftBtn.style.display = 'none';
          rightBtn.style.display = 'none';
        } else {
          // Use slider layout for more than 4 cards
          arrivalsGrid.classList.remove('grid-layout');
          leftBtn.style.display = 'flex';
          rightBtn.style.display = 'flex';
        }
      }

      function scrollArrivalsGrid(direction) {
        if (!arrivalsGrid || arrivalsGrid.classList.contains('grid-layout')) return;
        
        const cardWidth = 250 + 32; // card width + gap
        arrivalsGrid.scrollBy({ 
          left: direction * cardWidth, 
          behavior: 'smooth' 
        });
      }

      if (leftBtn && rightBtn) {
        leftBtn.addEventListener('click', () => scrollArrivalsGrid(-1));
        rightBtn.addEventListener('click', () => scrollArrivalsGrid(1));
      }

      updateArrivalsSlider();
      window.addEventListener('resize', updateArrivalsSlider);
    });
  </script>

  
  <!-- NEWSLETTER SIGNUP -->
  <section class="newsletter">
    <div class="newsletter-content">
      <h2>Join Our Adventure Club</h2>
      <p>Get exclusive deals, camping tips, and updates straight to your inbox.</p>
      <form class="newsletter-form">
        <input type="email" placeholder="Enter your email" required />
        <button type="submit">Subscribe</button>
      </form>
    </div>
  </section>

  

  <!-- AUTH MODAL -->
  <?php include 'components/auth_modal.php'; ?>

  <!-- CHATBOT -->
  <div id="chatbot-icon">💬</div>
  <div id="chatbot-container" class="hidden">
    <div id="chatbot-header">
      <span>Peak Bot</span>
      <button id="close-btn">&times;</button>
    </div>
    <div id="chatbot-body">
      <div id="chatbot-messages"></div>
    </div>
    <div id="chatbot-input-container">
      <input type="text" id="chatbot-input" placeholder="Type a message" />
      <button id="send-btn">Send</button>
    </div>
  </div>

  <!-- FOOTER -->
  <?php
  require_once __DIR__ . '/admin/content/footer_functions.php';
  $footerData = getFooterData();
  ?>
  <footer class="site-footer">
    <div class="footer-top">
      <div class="social-section">
        <p class="follow-text">Follow Us</p>
        <div class="social-icons">
          <?php if (!empty($footerData['facebook_link'])): ?>
            <a href="<?= htmlspecialchars($footerData['facebook_link']) ?>" target="_blank" rel="noopener"><i class="bi bi-facebook"></i></a>
          <?php endif; ?>
          <?php if (!empty($footerData['instagram_link'])): ?>
            <a href="<?= htmlspecialchars($footerData['instagram_link']) ?>" target="_blank" rel="noopener"><i class="bi bi-instagram"></i></a>
          <?php endif; ?>
          <?php if (!empty($footerData['youtube_link'])): ?>
            <a href="<?= htmlspecialchars($footerData['youtube_link']) ?>" target="_blank" rel="noopener"><i class="bi bi-youtube"></i></a>
          <?php endif; ?>
          <?php if (!empty($footerData['tiktok_link'])): ?>
            <a href="<?= htmlspecialchars($footerData['tiktok_link']) ?>" target="_blank" rel="noopener"><i class="bi bi-tiktok"></i></a>
          <?php endif; ?>
          <?php if (!empty($footerData['twitter_link'])): ?>
            <a href="<?= htmlspecialchars($footerData['twitter_link']) ?>" target="_blank" rel="noopener"><i class="bi bi-twitter"></i></a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <hr />

    <div class="footer-links">
      <?php foreach ($footerData['footer_links'] as $category => $links): ?>
        <div>
          <h4><?= htmlspecialchars($category) ?></h4>
          <?php foreach ($links as $title => $url): ?>
            <a href="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($title) ?></a>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <hr />

    <div class="footer-bottom">
      <small><?= htmlspecialchars($footerData['copyright_text']) ?></small>
    </div>
  </footer>

  <!-- SCRIPTS -->
  <script src="Js/user_dropdown.js"></script>
  <script src="Js/cart.js"></script>
  <script src="Js/JavaScript.js"></script>
  <script src="components/auth_modal_otp.js"></script>
  <script src="Js/chatbot.js"></script>
  
  <script>
    // Debug: Check if cards and actions exist
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Cards found:', document.querySelectorAll('.seller-card, .arrival-card').length);
      console.log('Action buttons found:', document.querySelectorAll('.card-actions').length);
      console.log('Wishlist buttons found:', document.querySelectorAll('.wishlist-btn').length);
      console.log('Cart buttons found:', document.querySelectorAll('.cart-btn').length);
    });
    
    // Wishlist functionality for index page
    function toggleWishlist(productId) {
      console.log('Wishlist clicked for product:', productId);
      const btn = event.target.closest('.wishlist-btn');
      const icon = btn.querySelector('i');
      
      if (btn.classList.contains('active')) {
        // Remove from wishlist
        btn.classList.remove('active');
        icon.className = 'bi bi-heart';
        showMessage('Removed from wishlist', 'info');
      } else {
        // Add to wishlist
        btn.classList.add('active');
        icon.className = 'bi bi-heart-fill';
        showMessage('Added to wishlist!', 'success');
      }
    }
    
    // Add to cart functionality for index page
    function addToCart(productId) {
      console.log('Add to cart clicked for product:', productId);
      const btn = event.target.closest('.cart-btn');
      const icon = btn.querySelector('i');
      
      // Show adding state
      btn.classList.add('added');
      icon.className = 'bi bi-check-circle-fill';
      showMessage('Added to cart!', 'success');
      
      // Update cart count in header
      const cartCount = document.querySelector('.cart-count');
      if (cartCount) {
        const currentCount = parseInt(cartCount.textContent) || 0;
        cartCount.textContent = currentCount + 1;
      }
      
      // Reset button after 2 seconds
      setTimeout(() => {
        btn.classList.remove('added');
        icon.className = 'bi bi-bag-plus';
      }, 2000);
    }
    
    // Show message function (if not already available)
    function showMessage(message, type = 'info') {
      // Remove existing messages
      const existingMessages = document.querySelectorAll('.temp-message');
      existingMessages.forEach(msg => msg.remove());
      
      // Create message element
      const messageDiv = document.createElement('div');
      messageDiv.className = `temp-message temp-message-${type}`;
      messageDiv.textContent = message;
      
      // Style the message
      messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateX(100%);
        transition: transform 0.3s ease;
        max-width: 300px;
      `;
      
      // Set colors based on type
      if (type === 'success') {
        messageDiv.style.background = 'linear-gradient(135deg, #27ae60, #2ecc71)';
      } else if (type === 'error') {
        messageDiv.style.background = 'linear-gradient(135deg, #e74c3c, #c0392b)';
      } else {
        messageDiv.style.background = 'linear-gradient(135deg, #3498db, #2980b9)';
      }
      
      document.body.appendChild(messageDiv);
      
      // Animate in
      setTimeout(() => {
        messageDiv.style.transform = 'translateX(0)';
      }, 100);
      
      // Auto remove after 3 seconds
      setTimeout(() => {
        messageDiv.style.transform = 'translateX(100%)';
        setTimeout(() => {
          if (messageDiv.parentNode) {
            messageDiv.parentNode.removeChild(messageDiv);
          }
        }, 300);
      }, 3000);
    }
  </script>
  <script>
    // Logout functionality
    function handleLogout() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = 'logout.php';
        }
    }

    // User dropdown toggle
    function toggleUserDropdown() {
        const dropdown = document.getElementById('userDropdown');
        const userDropdownContainer = document.querySelector('.user-dropdown');
        
        if (dropdown) {
            dropdown.classList.toggle('show');
            if (userDropdownContainer) {
                userDropdownContainer.classList.toggle('active');
            }
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('userDropdown');
        const userDropdown = document.querySelector('.user-dropdown');
        
        if (dropdown && userDropdown && !userDropdown.contains(event.target)) {
            dropdown.classList.remove('show');
            userDropdown.classList.remove('active');
        }
    });

    // Prevent dropdown from closing when clicking inside it
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('userDropdown');
        if (dropdown && dropdown.contains(event.target) && !event.target.matches('a')) {
            event.stopPropagation();
        }
    });

    // Hide logout message after 3 seconds
    const logoutMessage = document.getElementById('logoutMessage');
    if (logoutMessage) {
        setTimeout(() => {
            logoutMessage.style.display = 'none';
        }, 3000);
    }
  </script>
</body>
</html>
          