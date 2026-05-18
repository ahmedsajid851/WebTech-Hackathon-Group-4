<?php
// controllers/CartController.php

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

class CartController {
    
    public function addToCart() {
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $product_id = $_POST['product_id'] ?? '';
            
            if(empty($product_id)){
                echo json_encode(['success' => false, 'message' => 'Product ID required']);
                exit();
            }
            
            // Initialize cart if not exists
            if(!isset($_SESSION['cart'])){
                $_SESSION['cart'] = [];
            }
            
            // Add or update quantity
            if(isset($_SESSION['cart'][$product_id])){
                $_SESSION['cart'][$product_id]++;
            } else {
                $_SESSION['cart'][$product_id] = 1;
            }
            
            // Calculate total cart count
            $cartCount = 0;
            foreach($_SESSION['cart'] as $qty){
                $cartCount += $qty;
            }
            
            echo json_encode(['success' => true, 'cartCount' => $cartCount]);
            exit();
        }
    }
    
    public function updateCart() {
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $product_id = $_POST['product_id'] ?? '';
            $quantity = (int)($_POST['quantity'] ?? 0);
            
            if(empty($product_id)){
                echo json_encode(['success' => false, 'message' => 'Product ID required']);
                exit();
            }
            
            if($quantity <= 0){
                unset($_SESSION['cart'][$product_id]);
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
            }
            
            // Calculate grand total and cart count
            require_once __DIR__ . '/../config/db.php';
            require_once __DIR__ . '/../models/ProductModel.php';
            
            $database = new Database();
            $connection = $database->openConnection();
            $productModel = new ProductModel();
            
            $grandTotal = 0;
            $cartCount = 0;
            $itemTotal = 0;
            
            foreach($_SESSION['cart'] as $id => $qty){
                $product = $productModel->getProductById($connection, $id);
                if($product && $product->num_rows > 0){
                    $productData = $product->fetch_assoc();
                    $grandTotal += $productData['price'] * $qty;
                    $cartCount += $qty;
                    if($id == $product_id){
                        $itemTotal = $productData['price'] * $qty;
                    }
                }
            }
            
            $database->closeConnection($connection);
            
            echo json_encode([
                'success' => true,
                'itemTotal' => $itemTotal,
                'grandTotal' => $grandTotal,
                'cartCount' => $cartCount
            ]);
            exit();
        }
    }
    
    public function removeFromCart() {
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $product_id = $_POST['product_id'] ?? '';
            
            if(empty($product_id)){
                echo json_encode(['success' => false, 'message' => 'Product ID required']);
                exit();
            }
            
            if(isset($_SESSION['cart'][$product_id])){
                unset($_SESSION['cart'][$product_id]);
            }
            
            // Calculate grand total and cart count
            require_once __DIR__ . '/../config/db.php';
            require_once __DIR__ . '/../models/ProductModel.php';
            
            $database = new Database();
            $connection = $database->openConnection();
            $productModel = new ProductModel();
            
            $grandTotal = 0;
            $cartCount = 0;
            
            foreach($_SESSION['cart'] as $id => $qty){
                $product = $productModel->getProductById($connection, $id);
                if($product && $product->num_rows > 0){
                    $productData = $product->fetch_assoc();
                    $grandTotal += $productData['price'] * $qty;
                    $cartCount += $qty;
                }
            }
            
            $database->closeConnection($connection);
            
            echo json_encode([
                'success' => true,
                'grandTotal' => $grandTotal,
                'cartCount' => $cartCount
            ]);
            exit();
        }
    }
    
    public function viewCart() {
        header("Location: ../views/customer/cart.php");
        exit();
    }
}

// Route handling
$controller = new CartController();

if(isset($_GET['action'])){
    switch($_GET['action']){
        case 'add':
            $controller->addToCart();
            break;
        case 'update':
            $controller->updateCart();
            break;
        case 'remove':
            $controller->removeFromCart();
            break;
        default:
            $controller->viewCart();
            break;
    }
} else {
    $controller->viewCart();
}
?>