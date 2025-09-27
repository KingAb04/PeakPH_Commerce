<?php
session_start();
require_once("../../includes/db.php");

// Redirect if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Footer Management - PeakPH</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <link rel="stylesheet" href="../../Css/admin.css">
</head>
<body>
  <!-- HEADER -->
  <header>
    <h2>Footer Content Management</h2>
    <button onclick="window.location.href='../../logout.php'">Logout</button>
  </header>

  <!-- SIDEBAR -->
  <div class="sidebar">
    <h3>Menu</h3>
    <a href="../admin.php" class="menu-link"><i class="bi bi-house"></i> Admin Home</a>
    <a href="../dashboard.php" class="menu-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="../mini-view.php" class="menu-link"><i class="bi bi-pencil-square"></i> Mini View</a>
    <a href="../inventory/inventory.php" class="menu-link"><i class="bi bi-box"></i> Inventory</a>
    <a href="../orders.php" class="menu-link"><i class="bi bi-bag"></i> Orders</a>
    <a href="../users/users.php" class="menu-link"><i class="bi bi-people"></i> Users</a>
    <!-- Content Manager -->
    <button class="collapsible" onclick="toggleContentManager()">
      <i class="bi bi-folder"></i> Content Manager
      <span id="arrow" style="float:right;">&#9660;</span>
    </button>
    <div class="content-manager-links" id="contentManagerLinks" style="display:block; margin-left: 15px;">
      <a href="carousel.php" class="menu-link"><i class="bi bi-images"></i> Carousel</a>
      <a href="bestseller.php" class="menu-link"><i class="bi bi-star"></i> Best Seller</a>
      <a href="new_arrivals.php" class="menu-link"><i class="bi bi-lightning"></i> New Arrivals</a>
      <a href="footer.php" class="menu-link active"><i class="bi bi-layout-text-window-reverse"></i> Footer</a>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <div class="content">
    <h2>Footer Content Management</h2>
    
    <div class="info-box">
      <h3><i class="bi bi-info-circle"></i> Footer Management</h3>
      <p>Manage your website's footer content, social media links, and contact information.</p>
    </div>

    <!-- Footer Settings Form -->
    <div class="form-section">
      <h3>Footer Settings</h3>
      <form method="POST" action="footer_data.php">
        <div class="form-group">
          <label for="company_name">Company Name:</label>
          <input type="text" id="company_name" name="company_name" value="PeakPH Commerce" required>
        </div>

        <div class="form-group">
          <label for="company_description">Company Description:</label>
          <textarea id="company_description" name="company_description" rows="3">Your ultimate destination for camping gear and outdoor equipment.</textarea>
        </div>

        <div class="form-group">
          <label for="contact_email">Contact Email:</label>
          <input type="email" id="contact_email" name="contact_email" value="contact@peakph.com">
        </div>

        <div class="form-group">
          <label for="contact_phone">Contact Phone:</label>
          <input type="text" id="contact_phone" name="contact_phone" value="+63 123 456 7890">
        </div>

        <div class="form-group">
          <label for="address">Address:</label>
          <textarea id="address" name="address" rows="2">Philippines</textarea>
        </div>

        <div class="form-group">
          <label for="facebook_link">Facebook URL:</label>
          <input type="url" id="facebook_link" name="facebook_link" value="">
        </div>

        <div class="form-group">
          <label for="instagram_link">Instagram URL:</label>
          <input type="url" id="instagram_link" name="instagram_link" value="">
        </div>

        <div class="form-group">
          <label for="twitter_link">Twitter URL:</label>
          <input type="url" id="twitter_link" name="twitter_link" value="">
        </div>

        <div class="form-group">
          <label for="copyright_text">Copyright Text:</label>
          <input type="text" id="copyright_text" name="copyright_text" value="© 2024 PeakPH Commerce. All rights reserved.">
        </div>

        <button type="submit" class="btn-primary">
          <i class="bi bi-save"></i> Update Footer
        </button>
      </form>
    </div>

    <!-- Current Footer Preview -->
    <div class="preview-section">
      <h3>Current Footer Preview</h3>
      <div class="footer-preview">
        <div class="footer-content">
          <div class="footer-section">
            <h4>PeakPH Commerce</h4>
            <p>Your ultimate destination for camping gear and outdoor equipment.</p>
          </div>
          <div class="footer-section">
            <h4>Contact Info</h4>
            <p>Email: contact@peakph.com</p>
            <p>Phone: +63 123 456 7890</p>
            <p>Address: Philippines</p>
          </div>
          <div class="footer-section">
            <h4>Follow Us</h4>
            <p>Facebook | Instagram | Twitter</p>
          </div>
        </div>
        <div class="footer-bottom">
          <p>© 2024 PeakPH Commerce. All rights reserved.</p>
        </div>
      </div>
    </div>
  </div>

  <script src="../../Js/admin.js"></script>
  <style>
    .info-box {
      background: #e8f4fd;
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 2rem;
      border-left: 4px solid #007bff;
    }
    
    .form-section {
      background: white;
      padding: 2rem;
      border-radius: 8px;
      margin-bottom: 2rem;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .form-group {
      margin-bottom: 1rem;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
    }
    
    .form-group input, .form-group textarea {
      width: 100%;
      padding: 0.5rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-family: inherit;
    }
    
    .preview-section {
      background: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .footer-preview {
      background: #2c3e50;
      color: white;
      padding: 2rem;
      border-radius: 8px;
      margin-top: 1rem;
    }
    
    .footer-content {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
      margin-bottom: 2rem;
    }
    
    .footer-section h4 {
      margin-bottom: 1rem;
      color: #3498db;
    }
    
    .footer-bottom {
      border-top: 1px solid #34495e;
      padding-top: 1rem;
      text-align: center;
    }
  </style>
</body>
</html>