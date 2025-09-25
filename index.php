<?php
session_start();

// If already logged in, redirect to admin dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: admin/index.php");
    exit;
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

  <!-- Google API -->
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
        <button id="loginIcon" class="login-btn">
          <i class="bi bi-person"></i>
          <span>Login</span>
        </button>
        <a href="cart.php" class="cart-link">
          <i class="bi bi-cart">
            <span class="cart-count">
              <?php 
                echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : '0'; 
              ?>
            </span>
          </i>
        </a>
      </div>
    </div>

    <!-- Bottom Navbar -->
    <div class="bottom-navbar">
      <nav>
  <a href="ProductCatalog.php">Shop</a>
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
  <?php
    $promoCard = include __DIR__ . '/admin/content/bestseller_data.php';
  ?>
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
      <a href="<?= htmlspecialchars($promoCard['button_link'] ?? 'shop-all.php') ?>" class="promo-cta">
        <?= htmlspecialchars($promoCard['button_text'] ?? 'Shop Now →') ?>
      </a>
    </div>

    <!-- Best Seller Grid Slider -->
    <div class="seller-slider-container" style="position:relative; flex:1; min-width:0;">
      <button class="slider-arrow left" id="sellerSliderLeft" style="position:absolute; left:-18px; top:50%; transform:translateY(-50%); background:#fff; border:none; border-radius:50%; box-shadow:0 2px 8px rgba(0,0,0,0.08); width:36px; height:36px; display:none; align-items:center; justify-content:center; z-index:2; cursor:pointer;"><i class="bi bi-chevron-left"></i></button>
      <div class="seller-grid" id="sellerGrid" style="overflow-x:auto; display:flex; gap:20px; scroll-behavior:smooth; padding-bottom:8px; scrollbar-width:none; -ms-overflow-style:none;">
        <?php if (!empty($promoCard['products'])): ?>
          <?php foreach ($promoCard['products'] as $product): ?>
            <a href="<?= htmlspecialchars($product['link']) ?>" class="seller-card" style="min-width:220px; max-width:240px; flex:0 0 220px;">
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
          <?php endforeach; ?>
        <?php else: ?>
          <div style="color:#888; font-size:1.1em; padding:30px 0;">No best seller products available.</div>
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
  <?php
  $arrivalsData = include __DIR__ . '/admin/content/new_arrivals_data.php';
  ?>
  <section class="new-arrivals">
    <h2 style="color: white">New Arrivals</h2>
    <div class="arrivals-slider-container">
      <button class="arrivals-slider-arrow left" id="arrivalsSliderLeft">
        <i class="bi bi-chevron-left"></i>
      </button>
      <div class="arrivals-grid" id="arrivalsGrid">
        <?php if (!empty($arrivalsData['arrivals'])) {
          foreach ($arrivalsData['arrivals'] as $arrival) { ?>
            <div class="arrival-card">
              <?php if (!empty($arrival['link'])) { ?>
                <a href="<?php echo htmlspecialchars($arrival['link']); ?>" class="logo-btn">
                  <img src="<?php echo htmlspecialchars($arrival['image']); ?>" alt="<?php echo htmlspecialchars($arrival['alt']); ?>" />
                </a>
              <?php } else { ?>
                <img src="<?php echo htmlspecialchars($arrival['image']); ?>" alt="<?php echo htmlspecialchars($arrival['alt']); ?>" />
              <?php } ?>
              <p class="product-name"><?php echo htmlspecialchars($arrival['name']); ?></p>
              <span class="price">₱<?php echo htmlspecialchars($arrival['price']); ?></span>
            </div>
          <?php }
        } else { ?>
          <div style="color:#888; font-size:1.1em; padding:30px 0;">No new arrivals available.</div>
        <?php } ?>
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

  

  <!-- LOGIN MODAL -->
  <div id="authModal" class="login-modal">
    <div class="login-card">
      <div class="login-left">
        <h2>Log In</h2>

        <?php if (isset($_GET['login']) && $_GET['login'] === 'failed'): ?>
          <p style="color: red;">Invalid email or password</p>
        <?php endif; ?>

        <p class="welcome-text">Welcome back! Please enter your details</p>

        <form id="emailLoginForm" method="POST" action="login.php">
          <label>Email</label>
          <input type="email" name="email" placeholder="Enter your email" required />

          <label>Password</label>
          <div class="password-field">
            <input type="password" name="password" placeholder="Enter your password" required />
            <i class="bi bi-eye"></i>
          </div>

          <a href="#" class="forgot-password">Forgot password?</a>
          <button type="submit" class="login-btn-main">Log in</button>

          <div class="or-divider"><span>Or Continue With</span></div>

          <div class="social-login">
            <button type="button" class="google-btn">
              <i class="bi bi-google"></i> Google
            </button>
            <button type="button" class="facebook-btn">
              <i class="bi bi-facebook"></i> Facebook
            </button>
          </div>
        </form>

        <p class="signup-text">
          Don't have an account? <a href="#">Sign up</a>
        </p>
      </div>

      <button class="close-btn" id="closeModal">
        <i class="bi bi-x-lg"></i>
      </button>

      <div class="login-right">
        <div class="overlay"></div>
      </div>
    </div>
  </div>

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
  <footer class="site-footer">
    <div class="footer-top">
      <div class="social-section">
        <p class="follow-text">Follow Us</p>
        <div class="social-icons">
          <a href="https://facebook.com/yourpage" target="_blank" rel="noopener"><i class="bi bi-facebook"></i></a>
          <a href="https://instagram.com/yourpage" target="_blank" rel="noopener"><i class="bi bi-instagram"></i></a>
          <a href="https://youtube.com/yourpage" target="_blank" rel="noopener"><i class="bi bi-youtube"></i></a>
          <a href="https://tiktok.com/@yourpage" target="_blank" rel="noopener"><i class="bi bi-tiktok"></i></a>
        </div>
      </div>
    </div>

    <hr />

    <div class="footer-links">
      <div>
        <h4>CUSTOMER SERVICE</h4>
        <a href="#">Contact Us</a>
        <a href="#">Return and Exchange</a>
        <a href="#">Payment Methods</a>
      </div>
      <div>
        <h4>SHOP AT SCOUT AND SHOUT</h4>
        <a href="#">Our Stores</a>
        <a href="#">Delivery</a>
        <a href="#">Business Inquiries</a>
        <a href="#">Terms and Conditions</a>
        <a href="#">Privacy Policy</a>
      </div>
      <div>
        <h4>SERVICES</h4>
        <a href="#">Repairs</a>
        <a href="#">Buy Back</a>
        <a href="#">Click & Collect</a>
      </div>
      <div>
        <h4>ABOUT US</h4>
        <a href="#">Sustainability</a>
        <a href="#">Certificate of Registration</a>
      </div>
      <div>
        <h4>MORE</h4>
        <a href="#">Membership</a>
        <a href="#">Share Your Ideas</a>
        <a href="#">Product Recall</a>
      </div>
      <div>
        <h4>JOIN US</h4>
        <a href="#">climbers</a>
      </div>
    </div>

    <hr />

    <div class="footer-bottom">
      <small>© 2025 Peak. All rights reserved.</small>
    </div>
  </footer>

  <!-- SCRIPTS -->
  <script src="Js/JavaScript.js"></script>
  <script src="Js/chatbot.js"></script>
</body>
</html>
