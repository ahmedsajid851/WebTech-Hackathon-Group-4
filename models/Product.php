<?php

class Product {
    private $connection;
    
    public function __construct($connection) {
        $this->connection = $connection;
    }
    public function getAvailableProducts() {
        $sql = "SELECT * FROM products WHERE is_available = 1 ORDER BY id DESC";
        $result = $this->connection->query($sql);
        $products = [];
        if($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        return $products;
    }
    public function getAll() {
        $sql = "SELECT p.*, c.name as category_name, 
                COALESCE(AVG(r.rating), 0) as avg_rating
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN reviews r ON p.id = r.product_id
                GROUP BY p.id
                ORDER BY p.id DESC";
        $result = $this->connection->query($sql);
        $products = [];
        if($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        return $products;
    }
    
    public function getById($id) {
        $stmt = $this->connection->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
    
    public function create($data) {
        $stmt = $this->connection->prepare("INSERT INTO products (name, description, price, stock_qty, category_id, primary_image_path, is_available) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiisi", $data['name'], $data['description'], $data['price'], $data['stock_qty'], $data['category_id'], $data['primary_image_path'], $data['is_available']);
        return $stmt->execute();
    }
    
    public function update($id, $data) {
        $fields = [];
        $types = "";
        $values = [];
        
        if(isset($data['name'])) {
            $fields[] = "name = ?";
            $types .= "s";
            $values[] = $data['name'];
        }
        if(isset($data['description'])) {
            $fields[] = "description = ?";
            $types .= "s";
            $values[] = $data['description'];
        }
        if(isset($data['price'])) {
            $fields[] = "price = ?";
            $types .= "d";
            $values[] = $data['price'];
        }
        if(isset($data['stock_qty'])) {
            $fields[] = "stock_qty = ?";
            $types .= "i";
            $values[] = $data['stock_qty'];
        }
        if(isset($data['category_id'])) {
            $fields[] = "category_id = ?";
            $types .= "i";
            $values[] = $data['category_id'];
        }
        if(isset($data['primary_image_path'])) {
            $fields[] = "primary_image_path = ?";
            $types .= "s";
            $values[] = $data['primary_image_path'];
        }
        if(isset($data['is_available'])) {
            $fields[] = "is_available = ?";
            $types .= "i";
            $values[] = $data['is_available'];
        }
        
        $sql = "UPDATE products SET " . implode(", ", $fields) . " WHERE id = ?";
        $types .= "i";
        $values[] = $id;
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }
    
    public function delete($id) {
        $stmt = $this->connection->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    public function hasOrderItems($id) {
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM order_items WHERE product_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
    
    public function toggleAvailability($id) {
        $product = $this->getById($id);
        $newStatus = $product['is_available'] ? 0 : 1;
        $stmt = $this->connection->prepare("UPDATE products SET is_available = ? WHERE id = ?");
        $stmt->bind_param("ii", $newStatus, $id);
        return $stmt->execute();
    }
}
?>