<?php
session_start();
include_once "../config/DatabaseConnection.php";
include_once  "../model/ProductModel.php";


if(!isset($_SESSION["user_id"])) {
    header("Location: ../view/login.php");
    exit();
}

if (!isset($_SESSION["cart"]) || empty($_SESSION["cart"])) {
    header("Location: ../controller/CartController.php");
    exit();
}



$db = new DatabaseConnection();
$connection = $db->openConnection();
$productModel = new ProductModel();

$action = $_POST["action"] ?? "";

if ($action === "place_order") {
    $shippingAddress = trim($_POST["shipping_address"] ?? "");
    $payment_method = $_POST["payment_method"] ?? "";

    $hasError = false;

    if(!$shippingAddress) {
        
        $_SESSION["checkout_error"] = "Shipping address is required.";

        $hasError = true;


    } 
    

    if($payment_method !== "card" && $payment_method !== "cash") {
        
        $_SESSION["checkout_error"] = "Please select a valid payment method.";
        $hasError = true;
    }

    if($hasError) {
        header("Location: ../controller/CheckoutController.php");
        exit();

    }



//check stock 
    $totalAmount = 0;
    foreach($_SESSION["cart"] as $productId => $qty) {
        $result = $productModel->getProductById($connection, $productId);

        if($result->num_rows == 0) {
            $_SESSION["checkoutErr"] = "Product no longer available.";
            header("Location: ../controller/CheckoutController.php");
            exit();
        }

        $product = $result->fetch_assoc();
        $stock_qty = (int)$product["stock_qty"];

        if($stock_qty < $qty) {
            $_SESSION["checkout_error"] = "Not enough stock for product: " . $product["name"];
            header("Location: ../controller/CheckoutController.php");
            exit();
        }

        $totalAmount += (float)$product["price"] * $qty;

 


  }

  //imsert order

  $user_id = (int)$_SESSION["user_id"];
  $sql = "INSERT INTO orders (user_id, total_amount, shipping_address, payment_method, status) VALUES (?, ?, ?, ?, 'Pending')";
    $statement = $connection->prepare($sql);
    $statement->bind_param("idss", $user_id, $totalAmount, $shippingAddress, $payment_method, );

    $statement->execute();
    $orderId= $connection-> insert_id;

        //insert order items
        foreach($_SESSION["cart"] as $productId => $qty) {
            $result = $productModel->getProductById($connection, $productId);
            $product = $result->fetch_assoc();
            $unitPrice = (float)$product["price"];

            $sql2 = "INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)";
            $stmt2 = $connection->prepare($sql2);
            $stmt2->bind_param("iiid", $orderId, $productId, $qty, $unitPrice);
            $stmt2->execute();

            $sql3 = "UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?";
            $stmt3 = $connection->prepare($sql3);
            $stmt3->bind_param("ii", $qty, $productId);
            $stmt3->execute();
        }


        //clear chart

     $_SESSION["cart"] = [];


    $_SESSION["order_id"] = $orderId;
    $_SESSION["order_total"] = $totalAmount;
    $_SESSION["order_address"] = $shippingAddress;
    $_SESSION["order_payment"] = $payment_method;

    header("Location: ../view/order_confirmation.php");
    exit();

}




//checkout view
$cartItems = [];
$grandTotal = 0;

foreach ($_SESSION["cart"] as $productId => $qty) {
    $result = $productModel->getProductById($connection, $productId);
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $lineTotal = $product["price"] * $qty;

        $cartItems[] = [
            "id" => $product["id"],
            "name" => $product["name"],
            "price" => $product["price"],
            "qty" => $qty,
            "lineTotal" => $lineTotal,
            "primary_image_path" => $product["primary_image_path"]
        ];

        $grandTotal += $lineTotal;
    }



}

$checkoutError = $_SESSION["checkout_error"] ?? "";
unset($_SESSION["checkout_error"]);

$savedAddress = [];
if(isset($_SESSION["saved_address"])) {
    $savedAddress = json_decode($_SESSION["saved_address"], true)?? [];
    
}

include "../view/checkout.php";
exit();


?>