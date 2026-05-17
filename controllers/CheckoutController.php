<?php
// controllers/CheckoutController.php

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id'])){
    header("Location: ../views/auth/login.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/User.php';

$database = new Database();
$connection = $database->openConnection();
$productModel = new ProductModel();
$userModel = new User($connection);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if($action === 'place_order'){
    
    // Get shipping address
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    
    // If new address is selected
    if($shipping_address === 'new'){
        $shipping_address = trim($_POST['new_address'] ?? '');
    }
    
    $payment_method = $_POST['payment_method'] ?? '';
    $user_id = $_SESSION['user_id'];
    
    // Validation
    $errors = [];
    
    if(empty($shipping_address)){
        $errors[] = "Shipping address is required";
    }
    
    if(empty($payment_method)){
        $errors[] = "Payment method is required";
    }
    
    // Check if cart is empty
    if(empty($_SESSION['cart'])){
        $errors[] = "Your cart is empty";
    }
    
    if(!empty($errors)){
        $_SESSION['checkout_error'] = implode(", ", $errors);
        header("Location: ../views/customer/checkout.php");
        exit();
    }
    
    // Calculate total amount and check stock
    $total_amount = 0;
    $cartItems = $_SESSION['cart'];
    
    foreach($cartItems as $product_id => $quantity){
        $product = $productModel->getProductById($connection, $product_id);
        if($product && $product->num_rows > 0){
            $productData = $product->fetch_assoc();
            $total_amount += $productData['price'] * $quantity;
            
            // Check stock
            if($productData['stock_qty'] < $quantity){
                $_SESSION['checkout_error'] = "Not enough stock for: " . $productData['name'];
                header("Location: ../views/customer/checkout.php");
                exit();
            }
        } else {
            $_SESSION['checkout_error'] = "Product not found";
            header("Location: ../views/customer/checkout.php");
            exit();
        }
    }
    
    // Insert order
    $sql = "INSERT INTO orders (user_id, shipping_address, payment_method, total_amount, status, created_at) 
            VALUES (?, ?, ?, ?, 'Pending', NOW())";
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("issd", $user_id, $shipping_address, $payment_method, $total_amount);
    
    if($stmt->execute()){
        $order_id = $connection->insert_id;
        
        // Insert order items and update stock
        foreach($cartItems as $product_id => $quantity){
            $product = $productModel->getProductById($connection, $product_id);
            $productData = $product->fetch_assoc();
            $unit_price = $productData['price'];
            
            // Insert order item
            $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, unit_price) 
                        VALUES (?, ?, ?, ?)";
            $stmt_item = $connection->prepare($sql_item);
            $stmt_item->bind_param("iiid", $order_id, $product_id, $quantity, $unit_price);
            $stmt_item->execute();
            
            // Update stock
            $sql_stock = "UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?";
            $stmt_stock = $connection->prepare($sql_stock);
            $stmt_stock->bind_param("ii", $quantity, $product_id);
            $stmt_stock->execute();
        }
        
        // Save new address to user's saved addresses
        $userData = $userModel->getUserById($user_id);
        $existingAddresses = [];
        if($userData && !empty($userData['shipping_addresses'])){
            $existingAddresses = json_decode($userData['shipping_addresses'], true);
            if(!is_array($existingAddresses)){
                $existingAddresses = [];
            }
        }
        
        if(!in_array($shipping_address, $existingAddresses)){
            $existingAddresses[] = $shipping_address;
            $encoded = json_encode($existingAddresses);
            $update_sql = "UPDATE users SET shipping_addresses = ? WHERE id = ?";
            $update_stmt = $connection->prepare($update_sql);
            $update_stmt->bind_param("si", $encoded, $user_id);
            $update_stmt->execute();
        }
        
        // Clear cart
        unset($_SESSION['cart']);
        
        // Set order ID for confirmation
        $_SESSION['last_order_id'] = $order_id;
        
        // Redirect to confirmation page
        header("Location: ../views/customer/order_confirmation.php?order_id=" . $order_id);
        exit();
        
    } else {
        $_SESSION['checkout_error'] = "Failed to place order: " . $connection->error;
        header("Location: ../views/customer/checkout.php");
        exit();
    }
}

// If no action, redirect to checkout
header("Location: ../views/customer/checkout.php");
exit();

$database->closeConnection($connection);
?>