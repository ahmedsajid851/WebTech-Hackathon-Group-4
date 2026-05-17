<?php
$cartCount= 0;
if (isset($_SESSION["cart"])){
    foreach($_SESSION["cart"]as $qty){
        $cartCount += $qty;
    } 


}



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
                margin:auto ;

            }

            h2{
                margin-bottom: 15px;
            }

            table { 
                width: 100%; border-collapse: collapse; margin-bottom: 20px; 
            }
            th, td { padding: 10px; text-align: center; border-bottom: 1px solid #ddd; 
            }
             th 
             { background-color: #f2f2f2; 
            }
           .total-row { 
            font-weight: bold; 
            }
            label { 
            display: block; margin-bottom: 6px; font-weight: bold; 
            }
            input[type="text"], textarea { 
                width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 15px; box-sizing: border-box; }
            .radio-group { 
                margin-bottom: 15px; 
            }
            .radio-group label { 
                font-weight: normal; display: inline; margin-left: 6px; 
            }
            .error { 
                color: red; margin-bottom: 15px; 
            }
            .btn-submit { 
                padding: 12px 30px; background-color: #28a745; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; 
            }
            .btn-submit:hover { 
                background-color: #218838; 
            }


        </style>


    </head>

    <body>
        <div class="navbar">
            <span><strong>My E-commerce Store</strong></span>
        <div>
            <?php if (isset($_SESSION["user_name"])): ?>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION["user_name"]); ?>!</span>
                <?php endif; ?>
                <a href="../controllers/ProductController.php">Home</a>
                <a href="../controllers/CartController.php">🛒 Cart <span id="cart-count"><?php echo $cartCount > 0 ? "($cartCount)" : ""; ?></span></a>

            
        </div>

        </div>

        <div class="checkout-container">
            <h2> Checkout </h2>
            <?php if($checkoutError): ?>
               <p class="error"><?php echo htmlspecialchars($checkoutError); ?></p>
               <?php endif; ?>

            <!--Order Summery -->
            <table>
                <tr>
                    <th>Product</th>
                    <th>Unit price</th>
                    <th>Qty</th>
                    <th>Line Total</th>

                </tr>
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

            </table>


     

        <form method = "post" action="../controllers/CheckoutController.php">
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
                            <textarea name="new_address" id="new-address-input" placeholder="Enter new address..." style="display:none;"></textarea>
                            <?php else: ?>
                                <textarea name="shipping_address" placeholder="Enter your shipping address..." rows="3"></textarea>
                                <?php endif; ?>

                               

                                <label>Payment Method</label>
                                <div class="radio-group">
                                    <input type="radio" name="payment_method" value="Cash" id="cash" checked>
                                    <label for="cash">Cash on Delivery</label>

                                     &nbsp;&nbsp;
                                     <input type="radio" name="payment_method" value="Card" id="card">
                                     <label for= "card">Card</label>
                    </div>

                    <button type="submit" class="btn-submit">Place Order</button>
                </form>





            

            


    
    
       </div>

       <script>
        var radios = document.querySelectorAll('input[name="shipping_address"]');
       var newInput = document.getElementById("new-address-input");

        if (radios.length > 0 && newInput) {
            radios.forEach(function(radio) {
               radio.addEventListener("change", function() {
                    if (this.value === "new") {
                       newInput.style.display = "block";
                       newInput.name = "shipping_address";
                    }
                     else {
                       newInput.style.display = "none";
                       newInput.name = "new_address";
            }
        });
    });
}



       </script>
       


    </body>
</html>