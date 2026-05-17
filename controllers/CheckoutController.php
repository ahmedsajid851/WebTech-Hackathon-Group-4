<?php
session_start();
include_once "../config/DatabaseConnection.php";
include_once "../models/ProductModel.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../views/customer/login.php");
    exit();
}

if (!isset($_SESSION["cart"]) || empty($_SESSION["cart"])) {
    header("Location: ../controllers/CartController.php");
    exit();
}

$db = new DatabaseConnection();
$connection = $db->openConnection();
$productModel = new ProductModel();

$action = $_POST["action"] ?? "";

if ($action === "place_order") {
    $shippingAddress = trim($_POST["shipping_address"] ?? "");
    $payment_method  = $_POST["payment_method"] ?? "";
    $hasError        = false;

    if (!$shippingAddress) {
        $_SESSION["checkout_error"] = "Shipping address is required.";
        $hasError = true;
    }

    if ($payment_method !== "Card" && $payment_method !== "Cash") {
        $_SESSION["checkout_error"] = "Please select a valid payment method.";
        $hasError = true;
    }

    if ($hasError) {
        header("Location: ../controllers/CheckoutController.php");
        exit();
    }

    // Check stock
    $totalAmount = 0;
    foreach ($_SESSION["cart"] as $productId => $qty) {
        $result = $productModel->getProductById($connection, $productId);

        if ($result->num_rows == 0) {
            $_SESSION["checkout_error"] = "Product no longer available.";
            header("Location: ../controllers/CheckoutController.php");
            exit();
        }

        $product  = $result->fetch_assoc();
        $stockQty = (int)$product["stock_qty"];       // ✅ correct

        if ($stockQty < $qty) {
            $_SESSION["checkout_error"] = "Not enough stock for: " . $product["name"];
            header("Location: ../controllers/CheckoutController.php");
            exit();
        }

        $totalAmount += (float)$product["price"] * $qty;
    }

    // Insert order
    $user_id   = (int)$_SESSION["user_id"];
    $sql       = "INSERT INTO orders (user_id, shipping_address, payment_method, total_amount, status)
                  VALUES (?, ?, ?, ?, 'Pending')";  // ✅ correct
    $statement = $connection->prepare($sql);
    $statement->bind_param("issd", $user_id, $shippingAddress, $payment_method, $totalAmount);
    $statement->execute();
    $orderId = $connection->insert_id;

    // Insert order items + deduct stock
    foreach ($_SESSION["cart"] as $productId => $qty) {
        $result    = $productModel->getProductById($connection, $productId);
        $product   = $result->fetch_assoc();
        $unitPrice = (float)$product["price"];

        $sql2  = "INSERT INTO order_items (order_id, product_id, quantity, unit_price)
                  VALUES (?, ?, ?, ?)";              // ✅ correct
        $stmt2 = $connection->prepare($sql2);
        $stmt2->bind_param("iiid", $orderId, $productId, $qty, $unitPrice);
        $stmt2->execute();

        $sql3  = "UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?"; // ✅ correct
        $stmt3 = $connection->prepare($sql3);
        $stmt3->bind_param("ii", $qty, $productId);
        $stmt3->execute();
    }

    // Save address to DB if new
    $stmtA = $connection->prepare("SELECT shipping_addresses FROM users WHERE id = ?");
    $stmtA->bind_param("i", $user_id);
    $stmtA->execute();
    $userRow  = $stmtA->get_result()->fetch_assoc();
    $existing = [];
    if ($userRow) {
        $existing = json_decode($userRow["shipping_addresses"] ?? "[]", true) ?? [];
    }

    if (!in_array($shippingAddress, $existing)) {
        $existing[] = $shippingAddress;
        $encoded    = json_encode($existing);
        $stmtB      = $connection->prepare("UPDATE users SET shipping_addresses = ? WHERE id = ?");
        $stmtB->bind_param("si", $encoded, $user_id);
        $stmtB->execute();
    }

    $_SESSION["cart"]          = [];
    $_SESSION["order_id"]      = $orderId;
    $_SESSION["order_total"]   = $totalAmount;
    $_SESSION["order_address"] = $shippingAddress;
    $_SESSION["order_payment"] = $payment_method;

    header("Location: ../views/customer/confirmation.php");
    exit();
}

// Build cart items for view
$cartItems  = [];
$grandTotal = 0;

foreach ($_SESSION["cart"] as $productId => $qty) {
    $result = $productModel->getProductById($connection, $productId);
    if ($result && $result->num_rows > 0) {
        $product   = $result->fetch_assoc();
        $lineTotal = $product["price"] * $qty;

        $cartItems[] = [
            "id"                 => $product["id"],
            "name"               => $product["name"],
            "price"              => $product["price"],
            "qty"                => $qty,
            "lineTotal"          => $lineTotal,
            "primary_image_path" => $product["primary_image_path"]  // ✅ correct
        ];

        $grandTotal += $lineTotal;
    }
}

$checkoutError = $_SESSION["checkout_error"] ?? "";
unset($_SESSION["checkout_error"]);

// Load saved addresses from DB
$savedAddresses = [];
$user_id        = (int)$_SESSION["user_id"];
$stmtC          = $connection->prepare("SELECT shipping_addresses FROM users WHERE id = ?");
$stmtC->bind_param("i", $user_id);
$stmtC->execute();
$userRow = $stmtC->get_result()->fetch_assoc();
if ($userRow && !empty($userRow["shipping_addresses"])) {
    $savedAddresses = json_decode($userRow["shipping_addresses"], true) ?? [];
}

include "../views/customer/checkout.php";
exit();
?>