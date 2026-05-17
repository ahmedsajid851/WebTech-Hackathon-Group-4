<?php
// views/customer/checkout.php

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/ProductModel.php';
require_once __DIR__ . '/../../models/User.php';

$database = new Database();
$connection = $database->openConnection();
$productModel = new ProductModel();
$userModel = new User($connection);

// ========== INITIALIZE ALL VARIABLES ==========
$cartItems = [];
$grandTotal = 0;
$checkoutError = '';
$savedAddresses = [];

// ========== GET CART ITEMS FROM SESSION ==========
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
                'lineTotal' => $productData['price'] * $quantity
            ];
            $grandTotal += $productData['price'] * $quantity;
        }
    }
}

// ========== GET SAVED ADDRESSES FROM USER ==========
$userData = $userModel->getUserById($_SESSION['user_id']);
if($userData && isset($userData['shipping_addresses']) && !empty($userData['shipping_addresses'])){
    $savedAddresses = json_decode($userData['shipping_addresses'], true);
    if(!is_array($savedAddresses)){
        $savedAddresses = [];
    }
}

// ========== CART COUNT ==========
$cartCount = 0;
if (isset($_SESSION["cart"])){
    foreach($_SESSION["cart"] as $qty){
        $cartCount += $qty;
    }
}

// ========== GET ERROR FROM SESSION ==========
if(isset($_SESSION['checkout_error'])){
    $checkoutError = $_SESSION['checkout_error'];
    unset($_SESSION['checkout_error']);
}

$database->closeConnection($connection);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <style>
        body{
            font-family: Arial, Helvetica, sans-serif;
            margin: 20px;
            background-color:#f4f4f4;
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
        .checkout-container{
            background-color: #fff;
            padding: 20px;
            border-radius: 6px;
            max-width: 700px;
            margin:auto;
        }
        h2{
            margin-bottom: 15px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
        }
        th, td { 
            padding: 10px; 
            text-align: center; 
            border-bottom: 1px solid #ddd; 
        }
        th { 
            background-color: #f2f2f2; 
        }
        .total-row { 
            font-weight: bold; 
        }
        label { 
            display: block; 
            margin-bottom: 6px; 
            font-weight: bold; 
        }
        input[type="text"], textarea { 
            width: 100%; 
            padding: 8px; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            margin-bottom: 15px; 
            box-sizing: border-box; 
        }
        .radio-group { 
            margin-bottom: 15px; 
        }
        .radio-group label { 
            font-weight: normal; 
            display: inline; 
            margin-left: 6px; 
        }
        .error { 
            color: red; 
            margin-bottom: 15px; 
            padding: 10px;
            background: #f8d7da;
            border-radius: 4px;
        }
        .btn-submit { 
            padding: 12px 30px; 
            background-color: #28a745; 
            color: #fff; 
            border: none; 
            border-radius: 4px; 
            font-size: 16px; 
            cursor: pointer; 
        }
        .btn-submit:hover { 
            background-color: #218838; 
        }
        .empty-cart{
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .btn-continue {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        .btn-continue:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        var radios = document.querySelectorAll('input[name="shipping_address"]');
        var newInput = document.getElementById("new-address-input");

        if (radios.length > 0 && newInput) {
            radios.forEach(function(radio) {
                radio.addEventListener("change", function() {
                    if (this.value === "new") {
                        newInput.style.display = "block";
                        newInput.name = "shipping_address";
                    } else {
                        newInput.style.display = "none";
                        newInput.name = "new_address";
                    }
                });
            });
        }
    </script>
</head>
<body>
    <div class="navbar">
        <span><strong>My E-Commerce Store</strong></span>
        <div>
            <?php if(isset($_SESSION["name"])): ?>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?>!</span>
            <?php endif; ?>
            <a href="catalogue.php">Products</a>
            <a href="cart.php">🛒 Cart <span id="cart-count"><?php echo $cartCount > 0 ? "($cartCount)" : ""; ?></span></a>
            <a href="../dashboard.php">Dashboard</a>
            <a href="../../controllers/AuthController.php?action=logout">Logout</a>
        </div>
    </div>

    <div class="checkout-container">
        <h2>Checkout</h2>
        
        <?php if(empty($cartItems)): ?>
            <div class="empty-cart">
                <p>Your cart is empty.</p>
                <a href="catalogue.php" class="btn-continue">Continue Shopping</a>
            </div>
        <?php else: ?>
        
        <?php if($checkoutError): ?>
            <p class="error"><?php echo htmlspecialchars($checkoutError); ?></p>
        <?php endif; ?>

        <!-- Order Summary -->
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Unit price</th>
                    <th>Qty</th>
                    <th>Line Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($cartItems as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item["name"]); ?></td>
                    <td>$<?php echo number_format($item["price"], 2); ?></td>
                    <td><?php echo (int)$item["qty"]; ?></td>
                    <td>$<?php echo number_format($item["lineTotal"], 2); ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;">Grand Total:</td>
                    <td>$<?php echo number_format($grandTotal, 2); ?></td>
                </tr>
            </tbody>
        </table>

        <form method="post" action="../../controllers/CheckoutController.php">
            <input type="hidden" name="action" value="place_order">
            
            <label>Shipping Address</label>
            <?php if(!empty($savedAddresses)): ?>
                <div class="radio-group">
                    <?php foreach ($savedAddresses as $index => $addr): ?>
                        <div>
                            <input type="radio" name="shipping_address" value="<?php echo htmlspecialchars($addr); ?>" <?php echo $index === 0 ? "checked" : ""; ?>>
                            <label><?php echo htmlspecialchars($addr); ?></label>
                        </div>
                    <?php endforeach; ?>
                    <div>
                        <input type="radio" name="shipping_address" value="new" id="new-address-radio">
                        <label for="new-address-radio">Use a new address</label>
                    </div>
                    <textarea name="new_address" id="new-address-input" placeholder="Enter new address..." style="display:none;" rows="3"></textarea>
                </div>
            <?php else: ?>
                <textarea name="shipping_address" placeholder="Enter your shipping address..." rows="3" required></textarea>
            <?php endif; ?>

            <label>Payment Method</label>
            <div class="radio-group">
                <input type="radio" name="payment_method" value="Cash" id="cash" checked>
                <label for="cash">Cash on Delivery</label>
                &nbsp;&nbsp;
                <input type="radio" name="payment_method" value="Card" id="card">
                <label for="card">Card</label>
            </div>

            <button type="submit" class="btn-submit">Place Order</button>
        </form>
        
        <?php endif; ?>
    </div>
</body>
</html>