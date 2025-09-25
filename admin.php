<?php
session_start();

// Redirect to index.php if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>PeakPH Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <link rel="stylesheet" href="Css/admin.css"/>
 
</head>
<div>
  <!-- HEADER -->
  <header>
    <h2>PeakPH Admin Dashboard</h2>
    <button onclick="window.location.href='logout.php'">Logout</button>
  </header>

  <!-- SIDEBAR -->
  <div class="sidebar">
  <h3>Menu</h3>
  <a href="admin.php" class="menu-link active"><i class="bi bi-house"></i> Admin Home</a>
  <a href="categories/dashboard.php" class="menu-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
  <a href="categories/mini-view.php" class="menu-link"><i class="bi bi-pencil-square"></i> Mini View</a>
  <a href="categories/inventorycode/inventory.php" class="menu-link"><i class="bi bi-box"></i> Inventory</a>
  <a href="categories/orders.php" class="menu-link"><i class="bi bi-bag"></i> Orders</a>
  <a href="categories/users.php" class="menu-link"><i class="bi bi-people"></i> Users</a>
  <!-- Collapsible Content Manager (expanded by default) -->
  <button class="collapsible" onclick="toggleContentManager()">
    <i class="bi bi-folder"></i> Content Manager
    <span id="arrow" style="float:right;">&#9660;</span>
  </button>
  <div class="content-manager-links" id="contentManagerLinks" style="display:block; margin-left: 15px;">
    <a href="categories/content/carousel.php" class="menu-link"><i class="bi bi-images"></i> Carousel</a>
    <a href="categories/content/bestseller.php" class="menu-link"><i class="bi bi-star"></i> Best Seller</a>
    <a href="categories/content/new_arrivals.php" class="menu-link"><i class="bi bi-lightning"></i> New Arrivals</a>
    <a href="categories/content/footer.php" class="menu-link"><i class="bi bi-layout-text-window-reverse"></i> Footer</a>
  </div>
</div>
  


  <!-- MAIN CONTENT -->
  <div class="content">
    <h2>Welcome to PeakPH Admin Panel</h2>
    <p>Select a section from the sidebar to begin.</p>
    
  <script src="Js/admin.js"></script>
  
</body>
</html>
