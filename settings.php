<?php
require_once 'includes/user_auth.php';

// Require login for this page
requireLogin();

$current_user = getCurrentUser();

// Initialize cart count
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - PeakPH Commerce</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="Css/Global.css">
    <style>
        .settings-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .settings-header {
            background: linear-gradient(135deg, #2e765e, #3da180);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .settings-content {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 2rem;
        }
        
        .settings-sidebar {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .settings-main {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .profile-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .profile-nav li {
            margin-bottom: 0.5rem;
        }
        
        .profile-nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: #333;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .profile-nav a:hover, .profile-nav a.active {
            background: linear-gradient(135deg, #2e765e, #3da180);
            color: white;
        }
        
        .settings-grid {
            display: grid;
            gap: 2rem;
        }
        
        .setting-card {
            padding: 1.5rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background: #f9f9f9;
        }
        
        .setting-card h3 {
            color: #2e765e;
            margin-bottom: 1rem;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .setting-card p {
            color: #666;
            margin-bottom: 1rem;
        }
        
        .setting-btn {
            background: linear-gradient(135deg, #2e765e, #3da180);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.3s ease;
        }
        
        .setting-btn:hover {
            transform: translateY(-2px);
        }
        
        .setting-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        @media (max-width: 768px) {
            .settings-content {
                grid-template-columns: 1fr;
            }
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
                        <span class="cart-count"><?php echo $cart_count; ?></span>
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

    <div class="settings-container">
        <div class="settings-header">
            <h1><i class="bi bi-gear"></i> Account Settings</h1>
            <p>Manage your account preferences and security</p>
        </div>

        <div class="settings-content">
            <div class="settings-sidebar">
                <ul class="profile-nav">
                    <li><a href="profile.php"><i class="bi bi-person"></i> Profile Information</a></li>
                    <li><a href="orders.php"><i class="bi bi-box"></i> My Orders</a></li>
                    <li><a href="settings.php" class="active"><i class="bi bi-gear"></i> Account Settings</a></li>
                </ul>
                <div style="margin-top:2rem;">
                  <?php echo getAuthNavigationHTML(); ?>
                </div>
            </div>

            <div class="settings-main">
                <h2>Settings & Preferences</h2>
                
                <div class="settings-grid">
                    <div class="setting-card">
                        <h3><i class="bi bi-shield-lock"></i> Password & Security</h3>
                        <p>Update your password and manage account security settings.</p>
                        <button class="setting-btn" disabled>
                            <i class="bi bi-key"></i> Change Password
                        </button>
                    </div>

                    <div class="setting-card">
                        <h3><i class="bi bi-envelope"></i> Email Preferences</h3>
                        <p>Manage your email notifications and communication preferences.</p>
                        <button class="setting-btn" disabled>
                            <i class="bi bi-gear"></i> Manage Notifications
                        </button>
                    </div>

                    <div class="setting-card">
                        <h3><i class="bi bi-person-gear"></i> Profile Settings</h3>
                        <p>Update your personal information and profile details.</p>
                        <button class="setting-btn" disabled>
                            <i class="bi bi-pencil"></i> Edit Profile
                        </button>
                    </div>

                    <div class="setting-card">
                        <h3><i class="bi bi-geo-alt"></i> Address Book</h3>
                        <p>Manage your saved shipping and billing addresses.</p>
                        <button class="setting-btn" disabled>
                            <i class="bi bi-house"></i> Manage Addresses
                        </button>
                    </div>

                    <div class="setting-card">
                        <h3><i class="bi bi-bell"></i> Privacy Settings</h3>
                        <p>Control your privacy preferences and data sharing options.</p>
                        <button class="setting-btn" disabled>
                            <i class="bi bi-shield-check"></i> Privacy Controls
                        </button>
                    </div>

                    <div class="setting-card" style="border-color: #e74c3c;">
                        <h3 style="color: #e74c3c;"><i class="bi bi-exclamation-triangle"></i> Danger Zone</h3>
                        <p>Permanently delete your account and all associated data.</p>
                        <button class="setting-btn" style="background: #e74c3c;" disabled>
                            <i class="bi bi-trash"></i> Delete Account
                        </button>
                    </div>
                </div>

                <div style="margin-top: 2rem; text-align: center; color: #666; font-style: italic;">
                    <i class="bi bi-info-circle"></i> 
                    Advanced settings functionality coming soon! Contact support for assistance.
                </div>
            </div>
        </div>
    </div>

    <!-- AUTH MODAL -->
    <?php include 'components/auth_modal.php'; ?>

    <script src="Js/user_dropdown.js"></script>
    <script src="components/auth_modal_otp.js"></script>
    <script src="Js/JavaScript.js"></script>
</body>
</html>