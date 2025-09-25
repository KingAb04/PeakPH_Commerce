<?php
session_start();
require_once("../../includes/db.php"); 

// Redirect if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../../index.php");
    exit;
}

// --- Filters ---
$filterTag = isset($_GET['tag']) ? trim($_GET['tag']) : '';
$filterName = isset($_GET['name']) ? trim($_GET['name']) : '';

$sql = "SELECT * FROM inventory WHERE 1=1";
$params = [];

if ($filterTag !== '') {
    $sql .= " AND tag LIKE ?";
    $params[] = "%$filterTag%";
}
if ($filterName !== '') {
    $sql .= " AND product_name LIKE ?";
    $params[] = "%$filterName%";
}
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $types = str_repeat("s", count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Inventory - PeakPH</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <link rel="stylesheet" href="../../css/admin.css">
</head>
<body>
  <header>
    <h2>Inventory Management</h2>
    <button onclick="window.location.href='../../logout.php'">Logout</button>
  </header>

  <div class="sidebar">
    <h3>Menu</h3>
    <a href="../../admin.php" class="menu-link"><i class="bi bi-house"></i> Admin Home</a>
    <a href="../dashboard.php" class="menu-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="../mini-view.php" class="menu-link"><i class="bi bi-pencil-square"></i> Mini View</a>
    <a href="inventory.php" class="menu-link active"><i class="bi bi-box"></i> Inventory</a>
    <a href="../orders.php" class="menu-link"><i class="bi bi-bag"></i> Orders</a>
    <a href="../users.php" class="menu-link"><i class="bi bi-people"></i> Users</a>
    <!-- Collapsible Content Manager (expanded by default) -->
    <button class="collapsible" onclick="toggleContentManager()">
      <i class="bi bi-folder"></i> Content Manager
      <span id="arrow" style="float:right;">&#9660;</span>
    </button>
    <div class="content-manager-links" id="contentManagerLinks" style="display:block; margin-left: 15px;">
      <a href="../content/carousel.php" class="menu-link"><i class="bi bi-images"></i> Carousel</a>
      <a href="../content/bestseller.php" class="menu-link"><i class="bi bi-star"></i> Best Seller</a>
      <a href="../content/new_arrivals.php" class="menu-link"><i class="bi bi-lightning"></i> New Arrivals</a>
      <a href="../content/footer.php" class="menu-link"><i class="bi bi-layout-text-window-reverse"></i> Footer</a>
    </div>
  </div>

  <div class="content">
    <?php if (isset($_GET['status'])): ?>
      <p style="font-weight: bold; color: <?= $_GET['status']==='deleted' ? 'red' : 'green'; ?>;">
        <?php 
          if ($_GET['status'] === 'updated') echo "✅ Product updated successfully!";
          elseif ($_GET['status'] === 'added') echo "✅ Product added successfully!";
          elseif ($_GET['status'] === 'label-updated') echo "🎯 Label updated!";
          elseif ($_GET['status'] === 'deleted') echo "🗑 Product deleted successfully!";
        ?>
      </p>
    <?php endif; ?>

    <!-- 🔍 Search Filters -->
    <form method="GET" action="inventory.php" class="search-bar">
      <div class="search-group">
        <input type="text" name="tag" placeholder="🔖 Search by tag..." 
               value="<?= htmlspecialchars($filterTag); ?>">
      </div>
      <div class="search-group">
        <input type="text" name="name" placeholder="📦 Search by name..." 
               value="<?= htmlspecialchars($filterName); ?>">
      </div>
      <button type="submit" class="search-btn"><i class="bi bi-search"></i> Search</button>
      <a href="inventory.php" class="reset-btn"><i class="bi bi-arrow-clockwise"></i> Reset</a>
    </form>

    <button class="show-form-btn" onclick="toggleAddForm()">➕ Add Product</button>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Image</th>
          <th>Name</th>
          <th>Price</th>
          <th>Stock</th>
          <th>Tag</th>
          <th>Label</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id']; ?></td>
              <td>
                <?php if (!empty($row['image'])): ?>
                  <img src="../<?= htmlspecialchars($row['image']); ?>" width="50">
                <?php else: ?>
                  <img src="../Assets/placeholder.png" width="50">
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($row['product_name']); ?></td>
              <td>₱<?= number_format($row['price'], 2); ?></td>
              <td>
                <?php
                  $stock = $row['stock'];
                  $stockClass = '';
                  if ($stock < 30) $stockClass = 'low-stock';
                  elseif ($stock < 50) $stockClass = 'warning-stock';
                ?>
                <span class="stock-badge <?= $stockClass; ?>"><?= $stock; ?></span>
              </td>
              <td><?= $row['tag'] ?: '—'; ?></td>
              <td><?= $row['label'] ?: '—'; ?></td>
              <td>
                <button class="tag-btn" onclick="showLabelMenu(<?= $row['id']; ?>, this, '<?= $row['label']; ?>')">
                  <i class="bi bi-gift"></i> Label
                </button>
                <button class="edit-btn" onclick='showEditForm(<?= json_encode($row); ?>)'>
                  <i class="bi bi-pencil-square"></i> Edit
                </button>
                <form method="POST" action="inventory_delete.php" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $row['id']; ?>">
                  <button type="submit" class="delete-btn" onclick="return confirm('Delete this product?')">
                    <i class="bi bi-trash"></i> Delete
                  </button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="8" style="text-align:center;">No products found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- ADD FORM -->
    <div class="add-form hidden" id="addProductForm">
      <h3>Add New Product</h3>
      <form action="inventory_add.php" method="POST" enctype="multipart/form-data">
        <label>Name:</label>
        <input type="text" name="product_name" required>
        <label>Price (₱):</label>
        <input type="number" step="0.01" name="price" required>
        <label>Stock:</label>
        <input type="number" name="stock" required>
        <label>Tag:</label>
        <input type="text" name="tag">
        <label>Image:</label>
        <input type="file" name="image" accept="image/*">
        <button type="submit" class="save-btn">Save</button>
        <button type="button" class="cancel-btn" onclick="toggleAddForm()">Cancel</button>
      </form>
    </div>

    <!-- EDIT FORM -->
    <div class="add-form hidden" id="editProductForm">
      <h3>Edit Product</h3>
      <form action="inventory_update.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" id="edit_id">
        <label>Name:</label>
        <input type="text" name="product_name" id="edit_name" required>
        <label>Price (₱):</label>
        <input type="number" step="0.01" name="price" id="edit_price" required>
        <label>Stock:</label>
        <input type="number" name="stock" id="edit_stock" required>
        <label>Tag:</label>
        <input type="text" name="tag" id="edit_tag">
        <label>Change Image (optional):</label>
        <input type="file" name="image" accept="image/*">
        <button type="submit" class="save-btn">Update</button>
        <button type="button" class="cancel-btn" onclick="toggleEditForm()">Cancel</button>
      </form>
    </div>
  </div>

  <!-- Floating Label Menu -->
  <div id="labelMenu">
    <form id="labelForm" action="inventory_label.php" method="POST">
      <input type="hidden" name="id" id="label_id">
      <button type="submit" name="label" value="Best Seller" class="tag-option best-seller">🏆 Best Seller</button>
      <button type="submit" name="label" value="Popular" class="tag-option popular">🔥 Popular</button>
      <button type="submit" name="label" value="New Arrival" class="tag-option new-arrival">🆕 New Arrival</button>
      <button type="submit" name="label" value="" class="tag-option clear">❌ Clear Label</button>
    </form>
  </div>

  <script src="../../Js/admin.js"></script>
</body>
</html>
