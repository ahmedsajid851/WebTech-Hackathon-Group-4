<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();





if(!isset($_SESSION["order_id"])){
    header("Location: ../controllers/ProductController.php");
    exit();

}


$orderId = $_SESSION["order_id"];
$total = $_SESSION["order_total"];
$address = $_SESSION["order_address"];
$payment = $_SESSION["order_payment"];

unset($_SESSION["order_id"]);
unset($_SESSION["order_total"]);
unset($_SESSION["order_address"]);
unset($_SESSION["order_payment"]);



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirmation</title>
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
           margin-left: 15px;}

        .confirm-box{
            background-color: #fff;
            padding: 20px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .confirm-box h2{
            color: #28a745;

        }

        .details{
            text-align: left;
            margin-bottom: 20px;
        }
        .details p{
            margin: 8px 0;
        }
        .btn-home{
            display: inline-block;
            margin-top: 20px;
            padding: 10px 25px;
            background-color: #28a745;
            color: #fff;
            border-radius: 4px;
            text-decoration: none;
            
        }
        </style>
        </head>
        <body>
            <div class="navbar">
                <span><strong>My E-commerce Store</strong></span>
                <div><?php if (isset($_SESSION["name"])): ?>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?>!</span>
                <?php endif; ?>
                <a href="../../controllers/ProductController.php">Home</a>
 
                </div>
            </div>

            <div class="confirm-box">
                <h2>Order Confirmed</h2>
                <p>Thank you for your purchase!, your order has been recived</p>
                <div class="details">
                    <p><strong>Order ID:</strong># <?php echo (int)$orderId; ?></p>
                    <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($address); ?></p>
                    <p><strong>Total:</strong> $<?php echo number_format($total, 2); ?></p>
                    <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($payment); ?></p>
                    <p><strong>Status:</strong> Pending</p>
                </div>
                <a href="../../controllers/ProductController.php" class="btn-home">Continue Shopping</a>
            </div>


        </body>



</html>



