<?php

session_start();
include_once "../config/DatabaseConnection.php";
include_once  "../models/ProductModel.php";


//initialize 

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
} 


$db = new DatabaseConnection();
$connection = $db->openConnection();
$productModel = new ProductModel();




$action = $_GET["action"] ?? "";



if ($action !== "") {
    header('Content-Type: application/json');
}

if($action === "add") {
    $product_id = (int)($_POST["product_id"] ?? 0);

    if($product_id <= 0) {
        echo json_encode(["success" => false, "message" => "Invalid product ID"]);
        exit();
    }
    $result = $productModel->getProductById($connection, $product_id);

    if($result->num_rows == 0) {
        echo json_encode(["success" => false, "message" => "Product not found"]);
        exit();
    }

    $product = $result->fetch_assoc();

    if((int)$product["is_available"] !== 1) {
        echo json_encode(["success" => false, "message" => "Product is not available"]);
        exit();
    }

    $availableStock = (int)$product["stock_qty"];
    $currentQty = $_SESSION["cart"][$product_id] ?? 0;
    $newQty = $currentQty + 1;

    if($newQty > $availableStock) {
        $newQty = $availableStock;
    }

    $_SESSION["cart"][$product_id] = $newQty;

    $cartCount = 0;
    foreach($_SESSION["cart"] as $qty) {
        $cartCount += $qty;
    }

    echo json_encode([
        "success" => true,
        "message" => "Product added to cart",
        "product_id" => $product_id,
        "product_name" => $product["name"],
        "product_price" => $product["price"],
        "product_primary_image_path" => $product["primary_image_path"],
        "cartCount" => $cartCount
    ]);
    exit();
    }



if($action === "update") {
    $product_id = (int)($_POST["product_id"] ?? 0);
    $direction = (int)($_POST["direction"] ?? 0);
    

    if($product_id <= 0) {
        echo json_encode(["success" => false, "message" => "Invalid direction"]);
        exit();
    }
    

    

    if(!isset($_SESSION["cart"][$product_id])) {
        echo json_encode(["success" => false, "message" => "Product not in cart"]);
        exit();
    } 
    
    $result = $productModel->getProductById($connection, $product_id);

    if($result ->num_rows == 0) {
        echo json_encode(["success" => false, "message" => "Product not found"]);
        exit(); 
    }

    $product = $result->fetch_assoc();
    $availableStock = (int)$product["stock_qty"];
    $currentQty = $_SESSION["cart"][$product_id];
    $newQty = $currentQty + $direction;

    if($newQty <= 0) {
        unset ($_SESSION["cart"][$product_id]);
        $newQty = 0;
    } 
    elseif ($newQty > $availableStock) {
        
        $newQty = $availableStock;
        $_SESSION["cart"][$product_id] = $newQty;
    }
    else {
        $_SESSION["cart"][$product_id] = $newQty;
    }

    $grandTotal = 0;
    foreach ($_SESSION["cart"] as $id => $qty) {
        $r = $productModel->getProductById($connection, $id);
        if($r && $r->num_rows > 0) {
            $p = $r->fetch_assoc();
            $grandTotal += $p["price"] * $qty;
        }
    }

    $cartCount = 0;
    foreach ($_SESSION["cart"] as $qty) {
        $cartCount += $qty;
    }

    $lineTotal = $product["price"] * $newQty;

    echo json_encode([
        "success" => true,
        "message" => "Cart updated",
        "newQty" => $newQty,
        "lineTotal" => $lineTotal,
        "grandTotal" => $grandTotal,
        "cartCount" => $cartCount
    ]);
    exit();
}


if ($action === "remove") {
    $product_id = (int)($_POST["product_id"] ?? 0);

    if($product_id <= 0) {
        echo json_encode(["success" => false, "message" => "Invalid product ID"]);
        exit();
    }


    unset($_SESSION["cart"][$product_id]);

    $grandTotal = 0;
    foreach ($_SESSION["cart"] as $pid => $qty) {
        $r = $productModel->getProductById($connection, $pid);
        if($r && $r->num_rows > 0) {
            $p = $r->fetch_assoc();
            $grandTotal += $p["price"] * $qty;
        }
    }

    $cartCount = 0;
    foreach ($_SESSION["cart"] as $qty) {
        $cartCount += $qty;
    }

    echo json_encode([
        "success" => true,
        "message" => "Product removed from cart",
        "grandTotal" => $grandTotal,
        "cartCount" => $cartCount
    ]);
    exit();
}

if ($action === "") {

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

    // pass $cartItems and $grandTotal to view
    include "../views/customer/cart.php";
    exit();
}








echo json_encode(["success" => false, "message" => "Invalid action"]);
exit();

?>