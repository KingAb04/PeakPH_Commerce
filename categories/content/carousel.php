<?php
session_start();
$message = ''; // <-- Add this line!
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: carousel.php");
    exit;
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['carousel_image'])) {
    $targetDir = "../../uploads/carousel/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    $targetFile = $targetDir . basename($_FILES["carousel_image"]["name"]);
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($imageFileType, $allowed)) {
        if (move_uploaded_file($_FILES["carousel_image"]["tmp_name"], $targetFile)) {
            $message = "Image uploaded successfully!";
        } else {
            $message = "Error uploading image.";
        }
    } else {
        $message = "Invalid file type.";
    }
    // Redirect to avoid form resubmission
    header("Location: carousel.php");
    exit;
} // <--- ADD THIS CLOSING BRACE

// Handle delete image
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $deleteFile = basename($_GET['delete']);
    $filePath = "../../uploads/carousel/" . $deleteFile;
    if (file_exists($filePath)) {
        unlink($filePath);
        $message = "Image deleted successfully!";
    }
}

// Get all carousel images
$images = [];
$carouselDir = "../../uploads/carousel/";
if (is_dir($carouselDir)) {
    $images = array_diff(scandir($carouselDir), ['.', '..']);
}

// Update carousel_data.php for homepage
$carouselDataPath = __DIR__ . '/carousel_data.php';
$carouselArray = [];
foreach ($images as $img) {
    $carouselArray[] = [
        'image' => 'uploads/carousel/' . $img,
        'link' => '',      // You can add link/button editing later
        'button' => '',
        'class' => ''
    ];
}
file_put_contents(
    $carouselDataPath,
    "<?php\nreturn " . var_export($carouselArray, true) . ";\n"
);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Carousel Content Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../../Css/admin.css" />
    <style>
        .carousel-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .carousel-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            padding: 15px;
            width: 220px;
            text-align: center;
            position: relative;
            transition: box-shadow 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .carousel-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.10);
        }

        .carousel-card img {
            width: 180px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
            background: #f5f5f5;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.07);
            border: 1px solid #e0e0e0;
        }

        .carousel-card .filename {
            font-size: 0.95em;
            color: #555;
            margin-bottom: 10px;
            word-break: break-all;
        }

        .carousel-card .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .carousel-card .actions form,
        .carousel-card .actions a {
            display: inline-block;
        }

        .carousel-card .delete-btn {
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

        .carousel-card .delete-btn:hover {
            background: #c0392b;
        }

        .carousel-card .edit-btn {
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

        .carousel-card .edit-btn:hover {
            background: #217dbb;
        }

        /* Custom file input styling */
        .custom-upload-form {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
            background: #f8f8f8;
            padding: 18px 24px;
            border-radius: 10px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.04);
            border: 1px solid #e0e0e0;
            max-width: 500px;
        }

        .custom-file-input {
            display: none;
        }

        .custom-file-label {
            background: #3498db;
            color: #fff;
            padding: 8px 18px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.2s;
            border: none;
            font-size: 1em;
            margin-right: 10px;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .custom-file-label:hover {
            background: #217dbb;
        }

        .custom-upload-btn {
            background: #27ae60;
            color: #fff;
            border: none;
            padding: 8px 22px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1em;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .custom-upload-btn:hover {
            background: #219150;
        }

        .file-chosen {
            font-size: 0.98em;
            color: #444;
            font-style: italic;
            margin-left: 5px;
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <header>
        <h2>Content Manager &gt; Carousel</h2>
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
            <a href="carousel.php" class="menu-link active"><i class="bi bi-images"></i> Carousel</a>
            <!-- ...other content links... -->
        </div>
    </div>
    <div class="content">
        <h2>Carousel Content Manager</h2>
        <?php if ($message): ?>
            <div style="color: green;"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" class="custom-upload-form">
            <label for="carousel_image" class="custom-file-label">
                <i class="bi bi-upload"></i> Choose Image
                <input type="file" name="carousel_image" id="carousel_image" class="custom-file-input" accept="image/*" required onchange="showFileName(this)">
            </label>
            <span class="file-chosen" id="file-chosen">No file chosen</span>
            <button type="submit" class="custom-upload-btn"><i class="bi bi-cloud-arrow-up"></i> Upload</button>
        </form>
        <h3>Current Carousel Images:</h3>
        <div class="carousel-grid">
            <?php foreach ($images as $img): ?>
                <div class="carousel-card">
                    <img src="<?= '../../uploads/carousel/' . htmlspecialchars($img) ?>" alt="Carousel Image">
                    <div class="filename"><?= htmlspecialchars($img) ?></div>
                    <div class="actions">
                        <form method="get" onsubmit="return confirm('Are you sure you want to delete this image?');" style="display:inline;">
                            <input type="hidden" name="delete" value="<?= htmlspecialchars($img) ?>">
                            <button type="submit" class="delete-btn"><i class="bi bi-trash"></i> Delete</button>
                        </form>
                        <a href="edit_carousel.php?img=<?= urlencode($img) ?>" class="edit-btn"><i class="bi bi-pencil"></i> Edit</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
        function showFileName(input) {
            const fileChosen = document.getElementById('file-chosen');
            if (input.files && input.files.length > 0) {
                fileChosen.textContent = input.files[0].name;
            } else {
                fileChosen.textContent = 'No file chosen';
            }
        }
    </script>
    <script src="../../Js/admin.js"></script>
</body>

</html>