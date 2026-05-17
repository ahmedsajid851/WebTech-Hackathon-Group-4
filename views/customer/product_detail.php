<?php 
$cartCount = 0;

if (isset($_SESSION["cart"])) {
    foreach($_SESSION["cart"] as $qty) {
        $cartCount += $qty;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product["name"]); ?></title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 20px;
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
        .detail-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 6px;
            max-width: 700px;
            margin-left: auto;
        }
        .detail-container img {
            max-width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .detail-container h2 {
            margin-bottom: 10px;
        }
        .price {
            font-size: 22px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .rating {
            margin-bottom: 10px;
            color : #ff9800;
            font-size: 18px;
            margin-bottom: 10px;
        }
        .description {
            color : #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .stock-status {
            margin-bottom: 20px;
            font-weight: bold;

         
        }
        .in-stock {
            color : green;
        }
        .out-stock{
            color : red;
        }
        .btn-cart {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn-cart:hover {
            background-color: #0069d9;
        }
        .btn-back{ 
            display: inline-block;
            margin-top: 15px;
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="navbar">
       <span><strong>My E-commerce Store</strong></span>
       <div>
        <?php if (isset($_SESSION["name"])): ?>
        <span>Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?>!</span>
        <?php endif; ?>
        <a href="../controllers/ProductController.php">Home</a>
        <a href="../controllers/CartController.php">🛒 Cart <span id="cart-count"><?php echo $cartCount > 0 ? "($cartCount)" : ""; ?></span></a>
       </div>
    </div>
    <div class="detail-container">
        <img src="<?php echo $product["primary_image_path"]; ?>" alt="<?php echo $product["name"]; ?>">
        <h2><?php echo htmlspecialchars($product["name"]); ?></h2>
        <div class="price">$<?php echo number_format($product["price"], 2); ?></div>

        <div class ="rating">⭐ <?php echo $ratingData["total"] > 0 ? number_format($ratingData["avg_rating"], 1) . " / 5 (" . $ratingData["total"] . " reviews)" : "No reviews yet"; ?></div>
        <div class= "stock-status">
            status : <?php if ((int)$product["is_available"] === 1 && (int)$product["stock_qty"] > 0 ): ?>
                <span class="in-stock">In Stock(<?php echo (int)$product["stock_qty"]; ?> left)</span>
            <?php else: ?>
                <span class="out-stock">Out of Stock</span>
            <?php endif; ?>
        </div>
        <div class="description">
            <?php echo htmlspecialchars($product["description"]); ?>
        </div>

        <?php if ((int)$product["is_available"] === 1 && (int)$product["stock_qty"] > 0 ): ?>
            
            <button class="btn-cart" onclick="addToCart(<?php echo $product["id"]; ?>)">Add to Cart</button>
        <?php endif; ?>
        

        <br>
        <a href="../controllers/ProductController.php" class="btn-back">Back to Catalog</a>
    </div>

    <script>
        function addToCart(productId) {
            var xhr= new XMLHttpRequest();
            xhr.open("POST", "../controllers/CartController.php?action=add", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if(xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if(response.success) {
                            var cartCount = document.getElementById("cart-count");
                            if(cartCount) {
                                cartCount.textContent = "(" + response.cartCount + ")";

                            }
                            alert("added to cart!");
                        }
                        else {
                            alert(response.message || "Could not add to cart");
                        }
                    }
                    catch (e) {
                        console.log("Invalid JSON from cart add", e);
                    }
                }
            };
            xhr.send("product_id=" + encodeURIComponent(productId));
        }
    </script>
</body>
</html>