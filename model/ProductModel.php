<?php
require_once __DIR__ .'/../config/database.php';
class ProductModel {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

public function getAllProducts() {
    $sql = "SELECT * FROM products";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(); // must be here
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}
?>