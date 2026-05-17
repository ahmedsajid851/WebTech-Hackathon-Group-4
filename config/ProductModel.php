<?php


class ProductModel {

    function getAllProducts($connection) {
        $sql = "SELECT * FROM products WHERE is_available = 1";
        $result = $connection->query($sql);
        return $result;
    }

    function getProductById($connection, $id) {
        $sql = "SELECT * FROM products WHERE id = ?";
        $statement = $connection->prepare($sql);
        $statement->bind_param("i", $id);
        $statement->execute();
        $result = $statement->get_result();
        return $result;
    }

    function getAllCategories($connection) {
        $sql = "SELECT * FROM categories";
        $result = $connection->query($sql);
        return $result;
    }

    function getProductsByCategory($connection, $category_id) {
        $sql = "SELECT * FROM products WHERE category_id = ? AND is_available = 1";
        $statement = $connection->prepare($sql);
        $statement->bind_param("i", $category_id);
        $statement->execute();
        $result = $statement->get_result();
        return $result;
    }

    function searchProducts($connection, $keyword) {
    $keywordLike = "%" . $keyword . "%";
    $sql = "SELECT * FROM products WHERE is_available = 1 AND name LIKE ?";
    $statement = $connection->prepare($sql);
    $statement->bind_param("s", $keywordLike);
    $statement->execute();
    $result = $statement->get_result();
    return $result;
}

}

?>