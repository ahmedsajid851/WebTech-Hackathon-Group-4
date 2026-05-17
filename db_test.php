<?php
include "config/DatabaseConnection.php";
include "model/ProductModel.php";

$db= new DatabaseConnection();
$connection = $db->openConnection();

echo "<h3>Database Connected </h3>";


$productModel = new ProductModel();
$products = $productModel->getAllProducts($connection);
echo "<h3>All Products in Database:</h3>";

if($products->num_rows > 0) {
    while($row = $products->fetch_assoc()) {
        echo "ID: " . $row["id"] . " - Name: " . $row["name"] . " - Price: " . $row["price"] . "<br>";
    }
} else {
    echo "No products found.";
}

?>