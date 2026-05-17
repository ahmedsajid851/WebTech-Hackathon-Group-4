<?php
// views/customer/cart.php

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/ProductModel.php';

$database = new Database();
$connection = $database->openConnection();
$productModel = new ProductModel();

// Debug: Check if cart exists in session
// echo "<pre>"; print_r($_SESSION['cart']); echo "</pre>";

$cartItems = [];
$grandTotal = 0;

if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])){
    foreach($_SESSION['cart'] as $productId => $quantity){
        $product = $productModel->getProductById($connection, $productId);
        if($product && $product->num_rows > 0){
            $productData = $product->fetch_assoc();
            $cartItems[] = [
                'id' => $productData['id'],
                'name' => $productData['name'],
                'price' => $productData['price'],
                'qty' => $quantity,
                'lineTotal' => $productData['price'] * $quantity,
                'image' => $productData['primary_image_path']
            ];
            $grandTotal += $productData['price'] * $quantity;
        }
    }
}

// Calculate cart count
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
    <title>Shopping Cart</title>
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
        .cart-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 6px;
            max-width: 900px;
            margin: auto;
        }
        h1 {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .btn-remove {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }
        .btn-remove:hover {
            background-color: #c82333;
        }
        .btn-update {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }
        .btn-update:hover {
            background-color: #0056b3;
        }
        .btn-checkout {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-checkout:hover {
            background-color: #218838;
        }
        .btn-continue {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-continue:hover {
            background-color: #5a6268;
        }
        .qty-input {
            width: 60px;
            padding: 5px;
            text-align: center;
        }
        .empty-cart {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        .actions {
            display: flex;
            gap: 10px;
            justify-content: space-between;
            margin-top: 20px;
        }
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
    <script>
        function updateCart(productId) {
            var qty = document.getElementById("qty-" + productId).value;
            
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "../../controllers/CartController.php?action=update", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Update subtotal
                            document.getElementById("subtotal-" + productId).innerHTML = "$" + parseFloat(response.itemTotal).toFixed(2);
                            // Update grand total
                            document.getElementById("grand-total").innerHTML = "$" + parseFloat(response.grandTotal).toFixed(2);
                            // Update cart count
                            document.getElementById("cart-count").innerText = response.cartCount;
                            location.reload();
                        } else {
                            alert(response.message || "Error updating cart");
                        }
                    } catch(e) {
                        console.log("Error:", e);
                    }
                }
            };
            xhr.send("product_id=" + productId + "&quantity=" + qty);
        }

        function removeFromCart(productId) {
            if(confirm("Remove this item from cart?")) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "../../controllers/CartController.php?action=remove", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                location.reload();
                            } else {
                                alert(response.message || "Error removing item");
                            }
                        } catch(e) {
                            console.log("Error:", e);
                        }
                    }
                };
                xhr.send("product_id=" + productId);
            }
        }
    </script>
</head>
<body>
    <div class="navbar">
        <span><strong>My E-Commerce Store</strong></span>
        <div>
            <?php if(isset($_SESSION["name"])): ?>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?></span>
            <?php endif; ?>
            <a href="catalogue.php">Products</a>
            <a href="cart.php">🛒 Cart (<span id="cart-count"><?php echo $cartCount; ?></span>)</a>
            <a href="../dashboard.php">Dashboard</a>
            <a href="../../controllers/AuthController.php?action=logout">Logout</a>
        </div>
    </div>

    <div class="cart-container">
        <h1>Shopping Cart</h1>

        <?php if(empty($cartItems)): ?>
            <div class="empty-cart">
                <p>Your cart is empty.</p>
                <a href="catalogue.php" class="btn-continue">Continue Shopping</a>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($cartItems as $item): ?>
                    <tr id="row-<?php echo $item['id']; ?>">
                        <td>
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" class="product-img" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td>
                            <input type="number" class="qty-input" id="qty-<?php echo $item['id']; ?>" value="<?php echo $item['qty']; ?>" min="1" max="99">
                            <button class="btn-update" onclick="updateCart(<?php echo $item['id']; ?>)">Update</button>
                        </td>
                        <td id="subtotal-<?php echo $item['id']; ?>">$<?php echo number_format($item['lineTotal'], 2); ?></td>
                        <td>
                            <button class="btn-remove" onclick="removeFromCart(<?php echo $item['id']; ?>)">Remove</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="4" style="text-align: right;"><strong>Grand Total:</strong></td>
                        <td colspan="2" id="grand-total">$<?php echo number_format($grandTotal, 2); ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="actions">
                <a href="catalogue.php" class="btn-continue">Continue Shopping</a>
                <a href="checkout.php" class="btn-checkout">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>