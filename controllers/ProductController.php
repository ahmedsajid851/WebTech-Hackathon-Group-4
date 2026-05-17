<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include_once "../config/DatabaseConnection.php";
include_once "../models/ProductModel.php";
$db = new DatabaseConnection();
$connection = $db->openConnection();
$productModel = new ProductModel();

// Handle view - NOT json
if(isset($_GET["action"]) && $_GET["action"] == "view") {
    $id = (int) ($_GET["id"] ?? 0);
    if($id <= 0) {
        header("Location: ../controllers/ProductController.php");
        exit();
    }
    $result = $productModel->getProductById($connection, $id);
    if($result->num_rows == 0) {
        header("Location: ../controllers/ProductController.php");
        exit();
    }
    $product = $result->fetch_assoc();
    $ratingData = $productModel->getAverageRating($connection, $id);
    include "../views/customer/product_detail.php";
    exit();
}

// AJAX JSON requests
if(isset($_GET["action"])) {
    header('Content-Type: application/json');

    if($_GET["action"] == "search") {
        $keyword = $_GET["q"] ?? "";
        $result = $productModel->searchProducts($connection, $keyword);
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        echo json_encode($products);
        exit();
    }

    if($_GET["action"] == "filter") {
        $category_id = $_GET["category_id"] ?? 0;
        $result = $productModel->getProductsByCategory($connection, $category_id);
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        echo json_encode($products);
        exit();
    }
}

$products = $productModel->getAllProducts($connection);
$categories = $productModel->getAllCategories($connection);
include "../views/customer/catalogue.php";
?>