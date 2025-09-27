<?php
// Best Seller Content Manager (admin)
require_once('../auth_helper.php');
requireAdminAuth();
$message = '';

$dataFile = __DIR__ . '/bestseller_data.php';
$uploadDir = '../../uploads/bestseller/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// Handle image upload (add product)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['product_image'])) {
    $data = include $dataFile;
    $products = $data['products'];
    $imgPath = '';
    $fileName = time() . '_' . basename($_FILES['product_image']['name']);
    $targetFile = $uploadDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($imageFileType, $allowed)) {
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $targetFile)) {
            $imgPath = 'uploads/bestseller/' . $fileName;
            $products[] = [
                'link' => $_POST['link'],
                'image' => $imgPath,
                'alt' => $_POST['alt'],
                'badge' => $_POST['badge'],
                'name' => $_POST['name'],
                'desc' => $_POST['desc'],
                'rating' => $_POST['rating'],
                'reviews' => $_POST['reviews'],
                'price' => $_POST['price'],
            ];
            $data['products'] = $products;
            file_put_contents($dataFile, "<?php\nreturn " . var_export($data, true) . ";\n");
            $message = "Product added successfully!";
        } else {
            $message = "Error uploading image.";
        }
    } else {
        $message = "Invalid file type.";
    }
    header("Location: bestseller.php");
    exit;
}

// Handle delete product
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $data = include $dataFile;
    $products = $data['products'];
    $idx = (int)$_GET['delete'];
    if (isset($products[$idx]['image']) && strpos($products[$idx]['image'], 'uploads/bestseller/') === 0) {
        $imgFile = '../../' . $products[$idx]['image'];
        if (file_exists($imgFile)) unlink($imgFile);
    }
    array_splice($products, $idx, 1);
    $data['products'] = $products;
    file_put_contents($dataFile, "<?php\nreturn " . var_export($data, true) . ";\n");
    $message = "Product deleted successfully!";
    header("Location: bestseller.php");
    exit;
}

// Load data for display
$data = include $dataFile;
$promo = $data['promo'];
$products = $data['products'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Best Seller Content Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../../Css/admin.css" />
    <style>
        .form-section {
            background: #f8f8f8;
            padding: 18px 24px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .promo-card {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            padding: 20px 30px;
            margin-bottom: 30px;
            max-width: 600px;
        }

        .promo-card h3 {
            margin-top: 0;
        }

        .product-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .product-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            padding: 15px;
            width: 260px;
            text-align: center;
            position: relative;
            transition: box-shadow 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .product-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.10);
        }

        .product-card img {
            width: 120px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
            background: #f5f5f5;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.07);
            border: 1px solid #e0e0e0;
        }

        .product-card .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }

        .product-card .actions form,
        .product-card .actions a {
            display: inline-block;
        }

        .product-card .delete-btn {
            background: #e74c3c;
            color: #fff;
            border: none;
            padding: 5px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.95em;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .product-card .delete-btn:hover {
            background: #c0392b;
        }

        .product-card .edit-btn {
            background: #3498db;
            color: #fff;
            border: none;
            padding: 5px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.95em;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .product-card .edit-btn:hover {
            background: #217dbb;
        }

        .btn-add {
            background: #27ae60;
            color: #fff;
            margin-top: 10px;
            border: none;
            padding: 7px 18px;
            border-radius: 5px;
            font-weight: 600;
        }

        .btn-add:hover {
            background: #219150;
        }
    </style>
</head>

<body>
    <header>
        <h2>Content Manager &gt; Best Sellers</h2>
        <button onclick="logout()">Logout</button>
    </header>
    <div class="sidebar">
        <h3>Menu</h3>
        <a href="../admin.php" class="menu-link"><i class="bi bi-house"></i> Admin Home</a>
        <a href="../dashboard.php" class="menu-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="../mini-view.php" class="menu-link"><i class="bi bi-pencil-square"></i> Mini View</a>
        <a href="../inventory/inventory.php" class="menu-link"><i class="bi bi-box"></i> Inventory</a>
        <a href="../orders.php" class="menu-link"><i class="bi bi-bag"></i> Orders</a>
        <a href="../users/users.php" class="menu-link"><i class="bi bi-people"></i> Users</a>
        <button class="collapsible" onclick="toggleContentManager()">
            <i class="bi bi-folder"></i> Content Manager
            <span id="arrow" style="float:right;">&#9660;</span>
        </button>
        <div class="content-manager-links" id="contentManagerLinks" style="display:block; margin-left: 15px;">
            <a href="carousel.php" class="menu-link"><i class="bi bi-images"></i> Carousel</a>
            <a href="bestseller.php" class="menu-link active"><i class="bi bi-star"></i> Best Seller</a>
            <a href="new_arrivals.php" class="menu-link"><i class="bi bi-lightning"></i> New Arrivals</a>
            <a href="footer.php" class="menu-link"><i class="bi bi-layout-text-window-reverse"></i> Footer</a>
        </div>
    </div>
    <div class="content">
        <?php if ($message): ?>
            <div style="color: green; margin-bottom: 18px; font-weight: 600; font-size: 1.1em;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>



        <div class="form-section">
            <h3>Best Seller Products</h3>
            <div class="product-grid">
                <?php foreach ($products as $i => $p): ?>
                    <div class="product-card">
                        <img src="../../<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['alt']) ?>">
                        <div style="font-weight:600; font-size:1.1em; margin-bottom:2px;"> <?= htmlspecialchars($p['name']) ?> </div>
                        <div style="color:#888; font-size:0.97em; margin-bottom:4px;"> <?= htmlspecialchars($p['badge']) ?> </div>
                        <div style="font-size:0.96em; color:#555; margin-bottom:2px;">Rating: <?= htmlspecialchars($p['rating']) ?> (<?= htmlspecialchars($p['reviews']) ?> reviews)</div>
                        <div style="font-size:1.05em; font-weight:600; color:#27ae60; margin-bottom:6px;">₱<?= htmlspecialchars($p['price']) ?></div>
                        <div style="font-size:0.97em; color:#888; margin-bottom:7px; min-height:32px;"> <?= htmlspecialchars($p['desc']) ?> </div>
                        <div class="actions">
                            <button class="edit-btn" onclick="openEditModal(<?= $i ?>)"><i class="bi bi-pencil"></i> Edit</button>
                            <a href="?delete=<?= $i ?>" class="delete-btn" onclick="return confirm('Delete this product?')"><i class="bi bi-trash"></i> Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Edit Product Modal -->
            <div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.35); z-index:1000; align-items:center; justify-content:center;">
                <div style="background:#fff; border-radius:16px; padding:38px 38px 30px 38px; min-width:340px; max-width:95vw; box-shadow:0 8px 32px rgba(39,174,96,0.13); position:relative; display:flex; flex-direction:column; align-items:center;">
                    <button onclick="closeEditModal()" style="position:absolute; top:14px; right:18px; background:none; border:none; font-size:1.7em; color:#888; cursor:pointer; transition:color 0.2s;">&times;</button>
                    <h3 style="margin-bottom:18px; color:#27ae60; font-weight:700; letter-spacing:0.5px;">Edit Product</h3>
                    <form method="post" id="editProductForm" style="width:100%; max-width:340px; display:flex; flex-direction:column; gap:13px;">
                        <input type="hidden" name="idx" id="edit_idx">
                        <input type="text" name="link" id="edit_link" placeholder="Product Link" required style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <input type="text" name="image" id="edit_image" placeholder="Image Path" required style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <input type="text" name="alt" id="edit_alt" placeholder="Alt Text" required style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <input type="text" name="badge" id="edit_badge" placeholder="Badge" required style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <input type="text" name="name" id="edit_name" placeholder="Name" required style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <input type="text" name="desc" id="edit_desc" placeholder="Description" required style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <input type="text" name="rating" id="edit_rating" placeholder="Rating" required style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <input type="text" name="reviews" id="edit_reviews" placeholder="Reviews" required style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <input type="text" name="price" id="edit_price" placeholder="Price" required style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <button type="submit" name="edit_product" class="btn-add" style="margin-top:10px; font-size:1.08em; padding:10px 0; font-weight:600; letter-spacing:0.5px; display:inline-flex; align-items:center; gap:8px;">
                            <i class="bi bi-save"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
            <!-- Add Product Button and Modal -->
            <button class="btn-add" style="margin-bottom:18px; font-size:1.08em; padding:10px 28px; box-shadow:0 2px 8px rgba(39,174,96,0.08); font-weight:600; letter-spacing:0.5px; display:inline-flex; align-items:center; gap:8px;" onclick="openAddModal()">
                <i class="bi bi-plus-circle"></i> Add New Product
            </button>

            <div id="addModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.35); z-index:1000; align-items:center; justify-content:center;">
                <div style="background:#fff; border-radius:16px; padding:38px 38px 30px 38px; min-width:340px; max-width:95vw; box-shadow:0 8px 32px rgba(39,174,96,0.13); position:relative; display:flex; flex-direction:column; align-items:center;">
                    <button onclick="closeAddModal()" style="position:absolute; top:14px; right:18px; background:none; border:none; font-size:1.7em; color:#888; cursor:pointer; transition:color 0.2s;">&times;</button>
                    <h3 style="margin-bottom:18px; color:#27ae60; font-weight:700; letter-spacing:0.5px;">Add New Product</h3>
                    <form method="post" id="addProductForm" enctype="multipart/form-data" style="width:100%; max-width:340px; display:flex; flex-direction:column; gap:13px;">
                        <input type="text" name="link" placeholder="Product Link" required style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <label style="font-size:0.98em; color:#444; margin-bottom:2px;">Product Image</label>
                        <div id="drop-area" style="border:2px dashed #27ae60; border-radius:8px; padding:18px 10px; text-align:center; background:#f8fff8; margin-bottom:7px; cursor:pointer; transition:border-color 0.2s;">
                            <input type="file" name="product_image" id="product_image" accept="image/*" style="display:none;">
                            <div id="drop-text" style="color:#27ae60; font-weight:600; font-size:1.05em;">
                                <i class="bi bi-cloud-arrow-up" style="font-size:1.3em;"></i> Drag & drop or click to select image
                            </div>
                            <img id="imagePreview" src="" alt="Preview" style="display:none; margin-top:10px; max-width:90%; max-height:120px; border-radius:6px; box-shadow:0 1px 6px rgba(39,174,96,0.10);">
                        </div>
                        <div style="font-size:0.93em; color:#888; margin-bottom:2px;">or paste image path below (optional)</div>
                        <input type="text" name="image" placeholder="Image Path (optional)" style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <input type="text" name="alt" placeholder="Alt Text" required style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <input type="text" name="badge" placeholder="Badge" required style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <input type="text" name="name" placeholder="Name" required style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <input type="text" name="desc" placeholder="Description" required style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <input type="text" name="rating" placeholder="Rating" required style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <input type="text" name="reviews" placeholder="Reviews" required style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <input type="text" name="price" placeholder="Price" required style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <button type="submit" name="add_product" class="btn-add" style="margin-top:10px; font-size:1.08em; padding:10px 0; font-weight:600; letter-spacing:0.5px; display:inline-flex; align-items:center; gap:8px;">
                            <i class="bi bi-plus-circle"></i> Add Product
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        function toggleContentManager() {
            var links = document.getElementById('contentManagerLinks');
            var arrow = document.getElementById('arrow');
            if (links.style.display === 'none') {
                links.style.display = 'block';
                arrow.innerHTML = '&#9660;';
            } else {
                links.style.display = 'none';
                arrow.innerHTML = '&#9654;';
            }
        }

        // Modal logic for editing product
        const products = <?php echo json_encode($products); ?>;

        function openEditModal(idx) {
            const p = products[idx];
            document.getElementById('edit_idx').value = idx;
            document.getElementById('edit_link').value = p.link;
            document.getElementById('edit_image').value = p.image;
            document.getElementById('edit_alt').value = p.alt;
            document.getElementById('edit_badge').value = p.badge;
            document.getElementById('edit_name').value = p.name;
            document.getElementById('edit_desc').value = p.desc;
            document.getElementById('edit_rating').value = p.rating;
            document.getElementById('edit_reviews').value = p.reviews;
            document.getElementById('edit_price').value = p.price;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        // Modal logic for adding product
        function openAddModal() {
            document.getElementById('addModal').style.display = 'flex';
        }

        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }
        // Close modals on outside click
        window.onclick = function(event) {
            var editModal = document.getElementById('editModal');
            var addModal = document.getElementById('addModal');
            if (event.target === editModal) {
                closeEditModal();
            }
            if (event.target === addModal) {
                closeAddModal();
            }
        }
    </script>
    <script>
        // Drag and drop + preview for product image
        const dropArea = document.getElementById('drop-area');
        const fileInput = document.getElementById('product_image');
        const imagePreview = document.getElementById('imagePreview');
        dropArea.addEventListener('click', () => fileInput.click());
        dropArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropArea.style.borderColor = '#219150';
        });
        dropArea.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropArea.style.borderColor = '#27ae60';
        });
        dropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            dropArea.style.borderColor = '#27ae60';
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                fileInput.files = e.dataTransfer.files;
                showImagePreview(fileInput.files[0]);
            }
        });
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                showImagePreview(this.files[0]);
            }
        });

        function showImagePreview(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    </script>
    <script src="../../Js/admin.js"></script>
</body>

</html>