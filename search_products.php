<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

$search_term = $_GET['q'] ?? '';
$category = $_GET['category'] ?? 'all';
$min_price = floatval($_GET['min_price'] ?? 0);
$max_price = floatval($_GET['max_price'] ?? 99999);

$products = [];

if (isDatabaseConnected() && !empty($search_term)) {
    // Search in database
    $query = "SELECT id, product_name as name, price, image, tag, label, stock, created_at 
              FROM inventory 
              WHERE stock > 0 
              AND (product_name LIKE ? OR tag LIKE ?) 
              AND price BETWEEN ? AND ?";
    
    $params = ["%$search_term%", "%$search_term%", $min_price, $max_price];
    $types = "ssdd";
    
    if ($category !== 'all') {
        $query .= " AND LOWER(tag) = ?";
        $params[] = strtolower($category);
        $types .= "s";
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $result = executeQuery($query, $params, $types);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Fix image path
            $image_path = 'Assets/placeholder.svg';
            if (!empty($row['image'])) {
                if (file_exists('admin/' . $row['image'])) {
                    $image_path = 'admin/' . $row['image'];
                } elseif (file_exists($row['image'])) {
                    $image_path = $row['image'];
                }
            }
            
            $products[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'price' => number_format($row['price'], 2),
                'price_raw' => $row['price'],
                'image' => $image_path,
                'category' => strtolower($row['tag'] ?? 'other'),
                'badge' => $row['label'] ?? 'In Stock',
                'rating' => '⭐⭐⭐⭐☆',
                'reviews' => '(' . rand(50, 500) . ')',
                'stock' => $row['stock'],
                'is_database' => true
            ];
        }
    }
}

// Also search demo products if no database results or as fallback
if (empty($products) && !empty($search_term)) {
    $demo_products = [
        [
            'id' => 'demo_1',
            'name' => 'Emergency First Aid Kit',
            'price' => '950.00',
            'price_raw' => 950.00,
            'image' => 'Assets/Gallery_Images/Survival Kit Sample.png',
            'category' => 'emergency',
            'badge' => 'Popular',
            'rating' => '⭐⭐⭐⭐☆',
            'reviews' => '(4.0k)',
            'stock' => 50
        ],
        [
            'id' => 'demo_2',
            'name' => '4-Person Camping Tent',
            'price' => '1,200.00',
            'price_raw' => 1200.00,
            'image' => 'Assets/Gallery_Images/TentSample.jpg',
            'category' => 'tents',
            'badge' => 'Best Seller',
            'rating' => '⭐⭐⭐⭐⭐',
            'reviews' => '(3.2k)',
            'stock' => 25
        ],
        [
            'id' => 'demo_3',
            'name' => 'Portable Cooking Set',
            'price' => '750.00',
            'price_raw' => 750.00,
            'image' => 'Assets/Gallery_Images/CookingGearSample.png',
            'category' => 'cooking',
            'badge' => 'Popular',
            'rating' => '⭐⭐⭐⭐☆',
            'reviews' => '(1.8k)',
            'stock' => 30
        ]
    ];
    
    // Filter demo products based on search term
    foreach ($demo_products as $product) {
        if (stripos($product['name'], $search_term) !== false || 
            stripos($product['category'], $search_term) !== false) {
            if ($category === 'all' || $product['category'] === $category) {
                if ($product['price_raw'] >= $min_price && $product['price_raw'] <= $max_price) {
                    $products[] = $product;
                }
            }
        }
    }
}

echo json_encode([
    'success' => true,
    'products' => $products,
    'count' => count($products),
    'search_term' => $search_term
]);
?>