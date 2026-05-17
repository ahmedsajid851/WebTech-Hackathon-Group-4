<?php
// views/admin/product_delete.php

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

// ========== FIXED: Use Database class instead of DatabaseConnection ==========
require_once __DIR__ . '/../../config/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($id <= 0){
    header("Location: products.php");
    exit();
}

$database = new Database();
$connection = $database->openConnection();

// Check if product exists and has no order items
$check_sql = "SELECT id, primary_image_path FROM products WHERE id = ?";
$check_stmt = $connection->prepare($check_sql);
$check_stmt->bind_param("i", $id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$product = $check_result->fetch_assoc();

if(!$product){
    $_SESSION['error'] = "Product not found";
    header("Location: products.php");
    exit();
}

// Check if product has order items
$order_sql = "SELECT id FROM order_items WHERE product_id = ? LIMIT 1";
$order_stmt = $connection->prepare($order_sql);
$order_stmt->bind_param("i", $id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if($order_result->num_rows > 0){
    $_SESSION['error'] = "Cannot delete product that has existing order items";
    header("Location: products.php");
    exit();
}

// Delete product image if exists
if($product['primary_image_path'] && file_exists(__DIR__ . '/../../' . $product['primary_image_path'])){
    unlink(__DIR__ . '/../../' . $product['primary_image_path']);
}

// Delete product
$delete_sql = "DELETE FROM products WHERE id = ?";
$delete_stmt = $connection->prepare($delete_sql);
$delete_stmt->bind_param("i", $id);

if($delete_stmt->execute()){
    $_SESSION['success'] = "Product deleted successfully";
} else {
    $_SESSION['error'] = "Failed to delete product";
}

$database->closeConnection($connection);

header("Location: products.php");
exit();
?>