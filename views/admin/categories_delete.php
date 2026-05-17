<?php
// views/admin/categories_delete.php

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: categories.php");
    exit();
}

$category_id = intval($_GET['id']);

require_once __DIR__ . '/../../config/db.php';

$database = new Database();
$connection = $database->openConnection();

// Check if category has child categories
$child_sql = "SELECT id FROM categories WHERE parent_id = ? LIMIT 1";
$child_stmt = $connection->prepare($child_sql);
$child_stmt->bind_param("i", $category_id);
$child_stmt->execute();
$child_result = $child_stmt->get_result();

if($child_result->num_rows > 0){
    $database->closeConnection($connection);
    header("Location: categories.php?msg=error");
    exit();
}

// Check if category has products
$product_sql = "SELECT id FROM products WHERE category_id = ? LIMIT 1";
$product_stmt = $connection->prepare($product_sql);
$product_stmt->bind_param("i", $category_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();

if($product_result->num_rows > 0){
    $database->closeConnection($connection);
    header("Location: categories.php?msg=error");
    exit();
}

// Delete category
$delete_sql = "DELETE FROM categories WHERE id = ?";
$delete_stmt = $connection->prepare($delete_sql);
$delete_stmt->bind_param("i", $category_id);

if($delete_stmt->execute()){
    header("Location: categories.php?msg=deleted");
} else {
    header("Location: categories.php?msg=error");
}

$database->closeConnection($connection);
exit();
?>