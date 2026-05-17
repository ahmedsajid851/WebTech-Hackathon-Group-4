<?php
// api/products/toggle.php

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Product.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$database = new DatabaseConnection();
$connection = $database->openConnection();

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;

if(!$id) {
    echo json_encode(['success' => false, 'error' => 'Product ID required']);
    exit();
}

$productModel = new Product($connection);
$result = $productModel->toggleAvailability($id);

if($result) {
    $product = $productModel->getById($id);
    echo json_encode(['success' => true, 'is_available' => $product['is_available']]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to toggle']);
}
?>