<?php



$cartCount = 0;
if (isset($_SESSION["cart"])) {
    foreach ($_SESSION["cart"] as $qty) {
        $cartCount += $qty;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your cart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        .navbar  {
            background-color: #333;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .navbar a {
            color : #fff;
            text-decoration: none;
            margin-left: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
        }
        th, td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .actions button {
            margin: 0 5px;
            padding: 5px 10px;
        }
        .total-row {
            font-weight: bold;
           
        }

        .empty{
            text-align: center;
            margin-top: 40px;
            font-size: 18px;
            color: #777;
        }
        .btn-checkout {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background-color: #28a745;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
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
        <a href="../controllers/CartController.php">🛒 Cart<span id= "cart-count"><?php echo $cartCount > 0 ? " ($cartCount)" : ""; ?></span></a>
    </div>
</div>

    <?php if(!empty ($cartItems)): ?>
    <table>
            <tr>
                <th>Product</th>
                <th>Image</th>
                <th>Unit Price</th>
                <th>Quantity</th>
                <th>Line Total</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($cartItems as $item): ?>
            <tr id="row-<?php echo $item['id']; ?>">
                <td>
                    <?php echo htmlspecialchars($item["name"]); ?>
                </td>
                <td>
                    <img src="<?php echo htmlspecialchars($item["primary_image_path"]); ?>" alt="" style= "height: 60px">
                </td>

                <td>$<?php echo number_format($item["price"], 2); ?></td>
                <td><button onclick="updateCart('<?php echo $item['id']; ?>', -1)">-</button> 
                <span id="qty-<?php echo $item['id']; ?>"><?php echo (int)$item["qty"]; ?></span>
                <button onclick = "updateCart('<?php echo $item['id']; ?>', 1)">+</button></td>

                <td id="line-<?php echo $item['id']; ?>">$<?php echo number_format($item["lineTotal"], 2); ?></td>
                <td> <button onclick="removeFromCart('<?php echo $item['id']; ?>')">Remove</button>
                </td>
            </tr>



            <?php endforeach; ?>    
            <tr class="total-row">
                <td colspan="4" style="text-align: right;">Grand Total:</td>
                <td id="grand-total">$<?php echo number_format($grandTotal, 2); ?></td>
                <td></td>
            </tr>
    </table>
    <a href="http://localhost/final/controllers/CheckoutController.php" class="btn-checkout">Proceed to Checkout</a>
    <?php else: ?>
        <div class="empty">Your cart is empty. <a href="../controller/ProductController.php">Continue shopping</a>.</div>
    <?php endif; ?>

    <script>
         function updateCart(productId, direction) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "http://localhost/final/controllers/CartController.php?action=update", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if(xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if(response.success) {
                        if(response.newQty <= 0) {
                            var row = document.getElementById("row-" + productId);
                            if(row) row.remove();
                        } else {
                            document.getElementById("qty-" + productId).textContent = response.newQty;
                            document.getElementById("line-" + productId).textContent = "$" + parseFloat(response.lineTotal).toFixed(2);
                        }
                        document.getElementById("grand-total").textContent = "$" + parseFloat(response.grandTotal).toFixed(2);
                        document.getElementById("cart-count").textContent = response.cartCount > 0 ? " (" + response.cartCount + ")" : "";
                    } else {
                        alert(response.message || "Could not update cart.");
                    }
                } catch(e) {
                    console.log("Invalid JSON from update:", e);
                }
            }
        };
        xhr.send("product_id=" + encodeURIComponent(productId) + "&direction=" + encodeURIComponent(direction));
    }

    function removeFromCart(productId) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "http://localhost/final/controllers/CartController.php?action=remove", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if(xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if(response.success) {
                        var row = document.getElementById("row-" + productId);
                        if(row) row.remove();
                        document.getElementById("grand-total").textContent = "$" + parseFloat(response.grandTotal).toFixed(2);
                        document.getElementById("cart-count").textContent = response.cartCount > 0 ? " (" + response.cartCount + ")" : "";
                    } else {
                        alert(response.message || "Could not remove item from cart.");
                    }
                } catch(e) {
                    console.log("Invalid JSON from remove:", e);
                }
            }
        };
        xhr.send("product_id=" + encodeURIComponent(productId));
    }
        
        </script>



 



    




</body>
</html>
