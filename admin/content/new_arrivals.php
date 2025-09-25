<?php
// New Arrivals Content Manager (admin)
session_start();
$message = '';
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../admin.php");
    exit;
}

$dataFile = __DIR__ . '/new_arrivals_data.php';
$uploadDir = '../../uploads/new_arrivals/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// Handle image upload (add arrival)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arrival_image'])) {
    $data = file_exists($dataFile) ? include $dataFile : ['arrivals' => []];
    $arrivals = $data['arrivals'];
    $imgPath = '';
    $fileName = time() . '_' . basename($_FILES['arrival_image']['name']);
    $targetFile = $uploadDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($imageFileType, $allowed)) {
        if (move_uploaded_file($_FILES['arrival_image']['tmp_name'], $targetFile)) {
            $imgPath = 'uploads/new_arrivals/' . $fileName;
            $arrivals[] = [
                'link' => $_POST['link'],
                'image' => $imgPath,
                'alt' => $_POST['alt'],
                'name' => $_POST['name'],
                'price' => $_POST['price'],
            ];
            $data['arrivals'] = $arrivals;
            file_put_contents($dataFile, "<?php\nreturn " . var_export($data, true) . ";\n");
            $message = "New arrival added successfully!";
        } else {
            $message = "Error uploading image.";
        }
    } else {
        $message = "Invalid file type.";
    }
    header("Location: new_arrivals.php");
    exit;
}

// Handle delete arrival
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $data = file_exists($dataFile) ? include $dataFile : ['arrivals' => []];
    $arrivals = $data['arrivals'];
    $idx = (int)$_GET['delete'];
    if (isset($arrivals[$idx]['image']) && strpos($arrivals[$idx]['image'], 'uploads/new_arrivals/') === 0) {
        $imgFile = '../../' . $arrivals[$idx]['image'];
        if (file_exists($imgFile)) unlink($imgFile);
    }
    array_splice($arrivals, $idx, 1);
    $data['arrivals'] = $arrivals;
    file_put_contents($dataFile, "<?php\nreturn " . var_export($data, true) . ";\n");
    $message = "Arrival deleted successfully!";
    header("Location: new_arrivals.php");
    exit;
}

// Load data for display
$data = file_exists($dataFile) ? include $dataFile : ['arrivals' => []];
$arrivals = $data['arrivals'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>New Arrivals Content Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../../Css/admin.css" />
    <style>
        .form-section { background: #f8f8f8; padding: 18px 24px; border-radius: 10px; margin-bottom: 30px; }
        .arrival-grid { display: flex; flex-wrap: wrap; gap: 20px; }
        .arrival-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 15px;
            width: 220px;
            text-align: center;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .arrival-card img {
            width: 120px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
            background: #f5f5f5;
            box-shadow: 0 1px 4px rgba(0,0,0,0.07);
            border: 1px solid #e0e0e0;
        }
        .arrival-card .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }
        .arrival-card .delete-btn {
            background: #e74c3c;
            color: #fff;
            border: none;
            padding: 5px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.95em;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .arrival-card .delete-btn:hover { background: #c0392b; }
        .arrival-card .edit-btn {
            background: #3498db;
            color: #fff;
            border: none;
            padding: 5px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.95em;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .arrival-card .edit-btn:hover { background: #217dbb; }
        .btn-add { background: #27ae60; color: #fff; margin-top: 10px; border: none; padding: 7px 18px; border-radius: 5px; font-weight: 600; }
        .btn-add:hover { background: #219150; }
    </style>
</head>
<body>
    <header>
        <h2>Content Manager &gt; New Arrivals</h2>
        <button onclick="window.location.href='../../logout.php'">Logout</button>
    </header>
    <div class="sidebar">
        <h3>Menu</h3>
        <a href="../../admin.php" class="menu-link"><i class="bi bi-house"></i> Admin Home</a>
        <a href="../dashboard.php" class="menu-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="../mini-view.php" class="menu-link"><i class="bi bi-pencil-square"></i> Mini View</a>
        <a href="../inventorycode/inventory.php" class="menu-link"><i class="bi bi-box"></i> Inventory</a>
        <a href="../orders.php" class="menu-link"><i class="bi bi-bag"></i> Orders</a>
        <a href="../users.php" class="menu-link"><i class="bi bi-people"></i> Users</a>
        <button class="collapsible" onclick="toggleContentManager()">
            <i class="bi bi-folder"></i> Content Manager
            <span id="arrow" style="float:right;">&#9660;</span>
        </button>
        <div class="content-manager-links" id="contentManagerLinks" style="display:block; margin-left: 15px;">
            <a href="carousel.php" class="menu-link"><i class="bi bi-images"></i> Carousel</a>
            <a href="bestseller.php" class="menu-link"><i class="bi bi-star"></i> Best Sellers</a>
            <a href="new_arrivals.php" class="menu-link active"><i class="bi bi-lightning"></i> New Arrivals</a>
        </div>
    </div>
    <div class="content">
        <?php if ($message): ?>
            <div style="color: green; margin-bottom: 18px; font-weight: 600; font-size: 1.1em;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <div class="form-section">
            <h3>New Arrivals</h3>
            <div class="arrival-grid">
                <?php foreach ($arrivals as $i => $a): ?>
                <div class="arrival-card">
                    <img src="../../<?= htmlspecialchars($a['image']) ?>" alt="<?= htmlspecialchars($a['alt']) ?>">
                    <div style="font-weight:600; font-size:1.1em; margin-bottom:2px;"> <?= htmlspecialchars($a['name']) ?> </div>
                    <div style="font-size:1.05em; font-weight:600; color:#27ae60; margin-bottom:6px;">₱<?= htmlspecialchars($a['price']) ?></div>
                    <div class="actions">
                        <!-- Edit functionality can be added here -->
                        <a href="?delete=<?= $i ?>" class="delete-btn" onclick="return confirm('Delete this arrival?')"><i class="bi bi-trash"></i> Delete</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <!-- Add Arrival Modal/Button -->
            <button class="btn-add" style="margin-bottom:18px; font-size:1.08em; padding:10px 28px; box-shadow:0 2px 8px rgba(39,174,96,0.08); font-weight:600; letter-spacing:0.5px; display:inline-flex; align-items:center; gap:8px;" onclick="openAddModal()">
                <i class="bi bi-plus-circle"></i> Add New Arrival
            </button>
            <div id="addModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.35); z-index:1000; align-items:center; justify-content:center;">
                <div style="background:#fff; border-radius:16px; padding:38px 38px 30px 38px; min-width:340px; max-width:95vw; box-shadow:0 8px 32px rgba(39,174,96,0.13); position:relative; display:flex; flex-direction:column; align-items:center;">
                    <button onclick="closeAddModal()" style="position:absolute; top:14px; right:18px; background:none; border:none; font-size:1.7em; color:#888; cursor:pointer; transition:color 0.2s;">&times;</button>
                    <h3 style="margin-bottom:18px; color:#27ae60; font-weight:700; letter-spacing:0.5px;">Add New Arrival</h3>
                    <form method="post" id="addArrivalForm" enctype="multipart/form-data" style="width:100%; max-width:340px; display:flex; flex-direction:column; gap:13px;">
                        <input type="text" name="link" placeholder="Product Link" required style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <label style="font-size:0.98em; color:#444; margin-bottom:2px;">Product Image</label>
                        <input type="file" name="arrival_image" accept="image/*" required style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <input type="text" name="alt" placeholder="Alt Text" required style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <input type="text" name="name" placeholder="Name" required style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <input type="text" name="price" placeholder="Price" required style="padding:9px 12px; border-radius:6px; border:1px solid #e0e0e0; font-size:1em;">
                        <button type="submit" name="add_arrival" class="btn-add" style="margin-top:10px; font-size:1.08em; padding:10px 0; font-weight:600; letter-spacing:0.5px; display:inline-flex; align-items:center; gap:8px;">
                            <i class="bi bi-plus-circle"></i> Add Arrival
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
        function openAddModal() {
            document.getElementById('addModal').style.display = 'flex';
        }
        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }
        window.onclick = function(event) {
            var addModal = document.getElementById('addModal');
            if (event.target === addModal) {
                closeAddModal();
            }
        }
    </script>
    <script src="../../Js/admin.js"></script>
</body>
</html>
