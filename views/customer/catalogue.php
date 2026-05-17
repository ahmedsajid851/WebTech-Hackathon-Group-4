<?php
// views/customer/catalogue.php

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

// ========== FIXED: Correct path for db.php ==========
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/ProductModel.php';

$database = new Database();
$connection = $database->openConnection();
$productModel = new ProductModel();

// Get all products
$products = $productModel->getAllProducts($connection);
$categories = $productModel->getAllCategories($connection);

$cartCount = 0;
if (isset($_SESSION["cart"])) {
    foreach($_SESSION["cart"] as $qty) {
        $cartCount += $qty;
    }
}

$database->closeConnection($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Catalogue</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            background-color: #f4f4f4;
        }
        .navbar {
            background-color: #333;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .navbar a {
            color: #fff;
            text-decoration: none;
            margin-left: 15px;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .product-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 15px;
            text-align: center;
        }
        .product-card img {
            max-width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .product-card h3 {
            font-size: 16px;
            margin: 8px 0;
        }
        .product-card .price {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .btn-cart {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 14px;
        }
        .btn-cart:hover {
            background-color: #0056b3;
        }
        .no-products {
            text-align: center;
            font-size: 16px;
            color: #888;
            grid-column: 1/-1;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <span><strong>My E-Commerce Store</strong></span>
        <div>
            <?php if(isset($_SESSION["name"])): ?>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?></span>
            <?php endif; ?>
            <a href="cart.php">🛒 Cart <span id="cart-count"><?php echo $cartCount; ?></span></a>
            <a href="../dashboard.php">Dashboard</a>
            <a href="../../controllers/AuthController.php?action=logout">Logout</a>
        </div>
    </div>

    <h1>Product Catalogue</h1>

    <div class="product-grid">
        <?php if ($products && $products->num_rows > 0): ?>
            <?php while ($product = $products->fetch_assoc()): ?>
                <div class="product-card">
                    <img src="<?php echo htmlspecialchars($product['primary_image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <div class="price">$<?php echo number_format($product['price'], 2); ?></div>
                    <button class="btn-cart" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-products">No products available.</div>
        <?php endif; ?>
    </div>

    <script>
        function addToCart(productId) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "../../controllers/CartController.php?action=add", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        document.getElementById("cart-count").innerText = response.cartCount;
                        alert("Product added to cart!");
                    }
                }
            };
            xhr.send("product_id=" + productId);
        }
    </script>
</body>
</html>
