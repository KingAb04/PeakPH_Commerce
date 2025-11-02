<?php
require_once('../auth_helper.php');
requireAdminAuth();
require_once("../../includes/db.php");

// --- Filters ---
$filterTag = isset($_GET['tag']) ? trim($_GET['tag']) : '';
$filterName = isset($_GET['name']) ? trim($_GET['name']) : '';

// Get the cart quantities for each product from active cart session
function getCartQuantities() {
    $quantities = [];
    
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Get cart data from current session
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $product_id => $item) {
            if (!isset($quantities[$product_id])) {
                $quantities[$product_id] = 0;
            }
            $quantities[$product_id] += $item['quantity'];
        }
    }
    
    return $quantities;
}

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

// Get cart quantities
$cartQuantities = getCartQuantities();

// Check database connection
if (!isDatabaseConnected()) {
    die("Database connection error. Please check your database settings.");
}

// Execute query using safe helper function
$result = executeQuery($sql, $params);
if ($result === false) {
    die("Error retrieving inventory data. Please try again later.");
}
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
  <style>
    .drag-drop-area {
      border: 2px dashed #ccc;
      border-radius: 8px;
      padding: 20px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s ease;
      background-color: #f9f9f9;
      min-height: 80px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .drag-drop-area:hover {
      border-color: #007bff;
      background-color: #f0f8ff;
    }

    .drag-drop-area.drag-over {
      background-color: #e3f2fd;
      border-color: #2196f3;
      border-style: solid;
    }

    .drag-drop-area p {
      margin: 0;
      color: #666;
      font-size: 14px;
    }

    .drag-drop-area.has-file {
      background-color: #e8f5e8;
      border-color: #4caf50;
    }

    .drag-drop-area.has-file p {
      color: #2e7d32;
      font-weight: 500;
    }

    .cart-badge {
      background-color: #2196f3;
      color: white;
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 0.9em;
      font-weight: 500;
      display: inline-block;
      min-width: 24px;
      text-align: center;
    }
  </style>
</head>
<body>
  <header>
    <h2>Inventory Management</h2>
    <button onclick="logout()">Logout</button>
  </header>

  <div class="sidebar">
    <h3>Menu</h3>
    <a href="../admin.php" class="menu-link"><i class="bi bi-house"></i> Admin Home</a>
    <a href="../dashboard.php" class="menu-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="../mini-view.php" class="menu-link"><i class="bi bi-pencil-square"></i> Mini View</a>
    <a href="inventory.php" class="menu-link active"><i class="bi bi-box"></i> Inventory</a>
    <a href="../orders.php" class="menu-link"><i class="bi bi-bag"></i> Orders</a>
    <a href="../users/users.php" class="menu-link"><i class="bi bi-people"></i> Users</a>
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

  <button class="show-form-btn" onclick="showAddProductModal()"><span class="plus-green">+</span> Add Product</button>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Image</th>
          <th>Name</th>
          <th>Price</th>
          <th>Stock</th>
          <th>In Carts</th>
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
                  <img src="../<?= htmlspecialchars($row['image']); ?>" width="50" style="border-radius: 4px;">
                <?php else: ?>
                  <img src="../Assets/placeholder.svg" width="50" style="border-radius: 4px;">
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
              <td>
                <?php
                $inCart = isset($cartQuantities[$row['id']]) ? $cartQuantities[$row['id']] : 0;
                if ($inCart > 0) {
                    echo '<span class="cart-badge" title="Number of items in users\' carts">' . $inCart . '</span>';
                } else {
                    echo '—';
                }
                ?>
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

    <!-- ADD PRODUCT MODAL -->
    <div class="modal-overlay" id="addProductModal">
      <div class="modal-popup">
        <h3>Add New Product</h3>
        <form action="inventory_add.php" method="POST" enctype="multipart/form-data">
          <table class="form-table">
            <tr>
              <td><label>Name:</label></td>
              <td><input type="text" name="product_name" required></td>
            </tr>
            <tr>
              <td><label>Price (₱):</label></td>
              <td><input type="number" step="0.01" name="price" required></td>
            </tr>
            <tr>
              <td><label>Stock:</label></td>
              <td><input type="number" name="stock" required></td>
            </tr>
            <tr>
              <td><label>Category:</label></td>
              <td>
                <select name="tag" required>
                  <option value="">Select Category</option>
                  <option value="tents">Tents</option>
                  <option value="cooking">Cooking Equipment</option>
                  <option value="emergency">Emergency Kits/Tools</option>
                </select>
              </td>
            </tr>
            <tr>
              <td><label>Image:</label></td>
              <td>
                <div id="dragDropArea" class="drag-drop-area">
                  <p id="fileInputLabel">Drag and drop an image here, or click to select a file.</p>
                  <input type="file" name="image" id="fileInput" accept="image/*" style="display: none;">
                </div>
              </td>
            </tr>
          </table>
          <div class="modal-actions">
            <button type="submit" class="save-btn">Save Product</button>
            <button type="button" class="cancel-btn" onclick="hideAddProductModal()">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- EDIT PRODUCT MODAL -->
    <div class="modal-overlay" id="editProductModal">
      <div class="modal-popup">
        <h3>Edit Product</h3>
        <form action="inventory_update.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="id" id="edit_id">
          <table class="form-table">
            <tr>
              <td><label>Name:</label></td>
              <td><input type="text" name="product_name" id="edit_name" required></td>
            </tr>
            <tr>
              <td><label>Price (₱):</label></td>
              <td><input type="number" step="0.01" name="price" id="edit_price" required></td>
            </tr>
            <tr>
              <td><label>Stock:</label></td>
              <td><input type="number" name="stock" id="edit_stock" required></td>
            </tr>
            <tr>
              <td><label>Category:</label></td>
              <td>
                <select name="tag" id="edit_tag" required>
                  <option value="">Select Category</option>
                  <option value="tents">Tents</option>
                  <option value="cooking">Cooking Equipment</option>
                  <option value="emergency">Emergency Kits/Tools</option>
                </select>
              </td>
            </tr>
            <tr>
              <td><label>Current Image:</label></td>
              <td><img id="current_image_preview" src="" width="100" style="border-radius: 4px; display: none;"></td>
            </tr>
            <tr>
              <td><label>Change Image:</label></td>
              <td>
                <div id="editDragDropArea" class="drag-drop-area">
                  <p id="editFileInputLabel">Drag and drop an image here, or click to select a file.</p>
                  <input type="file" name="image" id="editFileInput" accept="image/*" style="display: none;">
                </div>
              </td>
            </tr>
          </table>
          <div class="modal-actions">
            <button type="submit" class="save-btn">Update Product</button>
            <button type="button" class="cancel-btn" onclick="hideEditProductModal()">Cancel</button>
          </div>
        </form>
      </div>
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

  <script>
    // Function to validate file type and size
    function validateFile(file) {
      const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
      const maxSize = 5 * 1024 * 1024; // 5MB
      
      if (!allowedTypes.includes(file.type)) {
        alert('Please select a valid image file (JPG, JPEG, PNG, or GIF)');
        return false;
      }
      
      if (file.size > maxSize) {
        alert('File size must be less than 5MB');
        return false;
      }
      
      return true;
    }

    // Function to update drag area appearance
    function updateDragArea(area, label, hasFile) {
      if (hasFile) {
        area.classList.add('has-file');
      } else {
        area.classList.remove('has-file');
      }
    }

    // Drag-and-drop for Add Product
    const dragDropArea = document.getElementById('dragDropArea');
    const fileInput = document.getElementById('fileInput');
    const fileInputLabel = document.getElementById('fileInputLabel');

    dragDropArea.addEventListener('dragover', (e) => {
      e.preventDefault();
      dragDropArea.classList.add('drag-over');
    });
    
    dragDropArea.addEventListener('dragleave', (e) => {
      // Only remove drag-over if we're leaving the drag area completely
      if (!dragDropArea.contains(e.relatedTarget)) {
        dragDropArea.classList.remove('drag-over');
      }
    });
    
    dragDropArea.addEventListener('drop', (e) => {
      e.preventDefault();
      dragDropArea.classList.remove('drag-over');
      const files = e.dataTransfer.files;
      
      if (files.length > 0 && validateFile(files[0])) {
        fileInput.files = files;
        fileInputLabel.textContent = `📁 ${files[0].name}`;
        updateDragArea(dragDropArea, fileInputLabel, true);
      }
    });
    
    dragDropArea.addEventListener('click', () => {
      fileInput.click();
    });
    
    fileInput.addEventListener('change', () => {
      if (fileInput.files.length > 0 && validateFile(fileInput.files[0])) {
        fileInputLabel.textContent = `📁 ${fileInput.files[0].name}`;
        updateDragArea(dragDropArea, fileInputLabel, true);
      } else {
        fileInputLabel.textContent = 'Drag and drop an image here, or click to select a file.';
        updateDragArea(dragDropArea, fileInputLabel, false);
      }
    });

    // Drag-and-drop for Edit Product
    const editDragDropArea = document.getElementById('editDragDropArea');
    const editFileInput = document.getElementById('editFileInput');
    const editFileInputLabel = document.getElementById('editFileInputLabel');

    editDragDropArea.addEventListener('dragover', (e) => {
      e.preventDefault();
      editDragDropArea.classList.add('drag-over');
    });
    
    editDragDropArea.addEventListener('dragleave', (e) => {
      // Only remove drag-over if we're leaving the drag area completely
      if (!editDragDropArea.contains(e.relatedTarget)) {
        editDragDropArea.classList.remove('drag-over');
      }
    });
    
    editDragDropArea.addEventListener('drop', (e) => {
      e.preventDefault();
      editDragDropArea.classList.remove('drag-over');
      const files = e.dataTransfer.files;
      
      if (files.length > 0 && validateFile(files[0])) {
        editFileInput.files = files;
        editFileInputLabel.textContent = `📁 ${files[0].name}`;
        updateDragArea(editDragDropArea, editFileInputLabel, true);
      }
    });
    
    editDragDropArea.addEventListener('click', () => {
      editFileInput.click();
    });
    
    editFileInput.addEventListener('change', () => {
      if (editFileInput.files.length > 0 && validateFile(editFileInput.files[0])) {
        editFileInputLabel.textContent = `📁 ${editFileInput.files[0].name}`;
        updateDragArea(editDragDropArea, editFileInputLabel, true);
      } else {
        editFileInputLabel.textContent = 'Drag and drop an image here, or click to select a file.';
        updateDragArea(editDragDropArea, editFileInputLabel, false);
      }
    });

    // Reset file inputs when modals are closed
    function resetAddProductModal() {
      fileInput.value = '';
      fileInputLabel.textContent = 'Drag and drop an image here, or click to select a file.';
      updateDragArea(dragDropArea, fileInputLabel, false);
    }

    function resetEditProductModal() {
      editFileInput.value = '';
      editFileInputLabel.textContent = 'Drag and drop an image here, or click to select a file.';
      updateDragArea(editDragDropArea, editFileInputLabel, false);
    }
  </script>
  <script src="../../Js/admin.js"></script>
</body>
</html>
