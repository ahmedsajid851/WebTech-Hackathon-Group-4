<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);



session_start();
include_once "../config/DatabaseConnection.php";
include_once "../model/ProductModel.php";
$db=new DatabaseConnection();
$connection = $db->openConnection();
$productModel = new ProductModel();


//AJAX request to get all products
if(isset($_GET["action"])){
    header('Content-Type: application/json');

    if($_GET["action"]=="search"){
        $keyword = $_GET["q"]??"";
        $result = $productModel->searchProducts($connection, $keyword);
        $products = [];

        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        echo json_encode($products);
        exit();



    }

    if(isset($_GET["action"]) && $_GET["action"]=="filter"){
        $catagorty_id = $_GET["category_id"]?? 0;
        $result = $productModel->getProductsByCategory($connection, $catagorty_id);
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

var_dump($products->num_rows);
include "../view/catalogue.php";

?>