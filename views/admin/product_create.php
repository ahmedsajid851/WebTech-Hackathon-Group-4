<?php
// views/admin/products.php

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

require_once __DIR__ . '/../../config/db.php';

$database = new Database();
$connection = $database->openConnection();

// Fetch all products with category names
$products = [];
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.created_at DESC";
$result = $connection->query($sql);
if($result){
    while($row = $result->fetch_assoc()){
        $products[] = $row;
    }
}

// Fetch categories for filter
$categories = [];
$catResult = $connection->query("SELECT id, name FROM categories ORDER BY name");
if($catResult){
    while($row = $catResult->fetch_assoc()){
        $categories[] = $row;
    }
}

// Get average ratings for all products at once
$ratings = [];
$ratingSql = "SELECT product_id, AVG(rating) as avg_rating FROM reviews GROUP BY product_id";
$ratingResult = $connection->query($ratingSql);
if($ratingResult){
    while($row = $ratingResult->fetch_assoc()){
        $ratings[$row['product_id']] = round($row['avg_rating'], 1);
    }
}

$database->closeConnection($connection);

// Function to get stock badge class
function getStockBadgeClass($stock) {
    if($stock <= 0){
        return 'badge-danger';
    } elseif($stock <= 5){
        return 'badge-warning';
    } else {
        return 'badge-success';
    }
}

function getStockText($stock) {
    if($stock <= 0){
        return 'Out of Stock';
    } elseif($stock <= 5){
        return 'Low Stock (' . $stock . ')';
    } else {
        return 'In Stock (' . $stock . ')';
    }
}

function getAvailabilityBadge($is_available) {
    if($is_available == 1){
        return '<span class="badge badge-success">In Stock</span>';
    } else {
        return '<span class="badge badge-danger">Out of Stock</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Product Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; margin-bottom: 20px; }
        .nav { margin-bottom: 20px; padding: 10px; background: #333; border-radius: 5px; }
        .nav a { color: white; text-decoration: none; margin-right: 15px; padding: 5px 10px; }
        .nav a:hover { background: #555; border-radius: 3px; }
        .btn-add { background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; display: inline-block; margin-bottom: 20px; }
        .btn-add:hover { background: #218838; }
        .filter-box { background: #f9f9f9; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .filter-box input, .filter-box select { padding: 8px; margin-right: 10px; }
        button { padding: 8px 15px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 3px; }
        button:hover { background: #0056b3; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #007bff; color: white; }
        tr:hover { background: #f5f5f5; }
        .badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-warning { background: #ffc107; color: #000; }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-info { background: #17a2b8; color: white; }
        .action-buttons a { margin-right: 5px; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 12px; display: inline-block; }
        .btn-edit { background: #007bff; color: white; }
        .btn-edit:hover { background: #0056b3; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-delete:hover { background: #c82333; }
        .stock-row-low { background-color: #fff3cd !important; }
        .product-img { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
    </style>
    <script>
        function filterProducts(){
            var search = document.getElementById("searchInput").value.toLowerCase();
            var category = document.getElementById("categoryFilter").value;
            var rows = document.getElementsByClassName("product-row");
            
            for(var i = 0; i < rows.length; i++){
                var row = rows[i];
                var productName = row.getAttribute("data-name").toLowerCase();
                var productCategory = row.getAttribute("data-category");
                
                var searchMatch = (search === "" || productName.indexOf(search) > -1);
                var categoryMatch = (category === "" || productCategory === category);
                
                row.style.display = (searchMatch && categoryMatch) ? "" : "none";
            }
        }
        
        function resetFilters(){
            document.getElementById("searchInput").value = "";
            document.getElementById("categoryFilter").value = "";
            filterProducts();
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Admin - Product Management</h1>
        <div class="nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="categories.php">Categories</a>
            <a href="products.php">Products</a>
            <a href="orders.php">Orders</a>
            <a href="../../controllers/AuthController.php?action=logout">Logout</a>
        </div>
        
        <a href="product_create.php" class="btn-add">+ Add New Product</a>
        
        <div class="filter-box">
            <h3>Filter Products</h3>
            <label>Search:</label>
            <input type="text" id="searchInput" placeholder="Product name..." onkeyup="filterProducts()">
            
            <label>Category:</label>
            <select id="categoryFilter" onchange="filterProducts()">
                <option value="">All Categories</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
            
            <button onclick="resetFilters()">Reset Filters</button>
        </div>
        
        <?php if(empty($products)): ?>
            <p>No products found. <a href="product_create.php">Add your first product</a></p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Availability</th>
                            <th>Avg Rating</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($products as $product): ?>
                            <tr class="product-row <?php echo ($product['stock_qty'] <= 5 && $product['stock_qty'] > 0) ? 'stock-row-low' : ''; ?>" 
                                data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                data-category="<?php echo $product['category_id']; ?>">
                                <td><?php echo $product['id']; ?></td>
                                <td>
                                    <?php if(!empty($product['primary_image_path']) && file_exists(__DIR__ . '/../../' . $product['primary_image_path'])): ?>
                                        <img src="/WebTech-Hackathon-Group-4/<?php echo $product['primary_image_path']; ?>" class="product-img" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/50" class="product-img" alt="No Image">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                                <td>$<?php echo number_format($product['price'], 2); ?></td>
                                <td>
                                    <span class="badge <?php echo getStockBadgeClass($product['stock_qty']); ?>">
                                        <?php echo getStockText($product['stock_qty']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo getAvailabilityBadge($product['is_available']); ?>
                                </td>
                                <td>
                                    <?php 
                                    $avgRating = $ratings[$product['id']] ?? 0;
                                    echo $avgRating > 0 ? $avgRating . ' ★' : 'No reviews';
                                    ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="product_edit.php?id=<?php echo $product['id']; ?>" class="btn-edit">Edit</a>
                                        <a href="product_delete.php?id=<?php echo $product['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>