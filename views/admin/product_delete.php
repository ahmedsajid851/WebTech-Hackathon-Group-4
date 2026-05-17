<?php
// views/admin/product_delete.php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Product.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

$database = new DatabaseConnection();
$connection = $database->openConnection();

$productModel = new Product($connection);
$id = $_GET['id'] ?? null;

if($id) {
    if($productModel->hasOrderItems($id)) {
        header("Location: products.php?error=cannot_delete_has_orders");
    } else {
        $productModel->delete($id);
        header("Location: products.php?msg=deleted");
    }
} else {
    header("Location: products.php");
}
exit();
?>