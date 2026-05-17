<?php
// models/Category.php

class Category {
    private $conn;
    
    public function __construct($connection){
        $this->conn = $connection;
    }
    
    // Get all categories
    public function getAll(){
        $sql = "SELECT * FROM categories ORDER BY parent_id, name";
        $result = $this->conn->query($sql);
        $categories = [];
        
        while($row = $result->fetch_assoc()){
            $categories[] = $row;
        }
        return $categories;
    }
    
    // Get parent categories (for dropdown)
    public function getParentCategories($excludeId = null){
        if($excludeId){
            $sql = "SELECT id, name FROM categories WHERE parent_id IS NULL AND id != ? ORDER BY name";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $excludeId);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $sql = "SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY name";
            $result = $this->conn->query($sql);
        }
        
        $categories = [];
        while($row = $result->fetch_assoc()){
            $categories[] = $row;
        }
        return $categories;
    }
    
    // Get category by ID
    public function getById($id){
        $sql = "SELECT * FROM categories WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // Create new category
    public function create($name, $parent_id = null){
        if($parent_id && $parent_id != ''){
            $sql = "INSERT INTO categories (name, parent_id) VALUES (?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $name, $parent_id);
        } else {
            $sql = "INSERT INTO categories (name) VALUES (?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $name);
        }
        
        return $stmt->execute();
    }
    
    // Update category
    public function update($id, $name, $parent_id = null){
        if($parent_id && $parent_id != ''){
            $sql = "UPDATE categories SET name = ?, parent_id = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sii", $name, $parent_id, $id);
        } else {
            $sql = "UPDATE categories SET name = ?, parent_id = NULL WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $name, $id);
        }
        return $stmt->execute();
    }
    
    // Delete category
    public function delete($id){
        // Check if category has children
        $checkSql = "SELECT id FROM categories WHERE parent_id = ? LIMIT 1";
        $checkStmt = $this->conn->prepare($checkSql);
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if($checkResult->num_rows > 0){
            return false; // Has child categories, cannot delete
        }
        
        // Check if category has products
        $checkProductSql = "SELECT id FROM products WHERE category_id = ? LIMIT 1";
        $checkProductStmt = $this->conn->prepare($checkProductSql);
        $checkProductStmt->bind_param("i", $id);
        $checkProductStmt->execute();
        $checkProductResult = $checkProductStmt->get_result();
        
        if($checkProductResult->num_rows > 0){
            return false; // Has products, cannot delete
        }
        
        // Delete category
        $sql = "DELETE FROM categories WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    // Get child categories
    public function getChildren($parent_id){
        $sql = "SELECT * FROM categories WHERE parent_id = ? ORDER BY name";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $parent_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $categories = [];
        while($row = $result->fetch_assoc()){
            $categories[] = $row;
        }
        return $categories;
    }
    
    // Get category tree (nested)
    public function getCategoryTree(){
        $sql = "SELECT * FROM categories ORDER BY parent_id, name";
        $result = $this->conn->query($sql);
        
        $categories = [];
        while($row = $result->fetch_assoc()){
            $categories[$row['id']] = $row;
        }
        
        $tree = [];
        foreach($categories as $id => $category){
            if($category['parent_id'] === null){
                $tree[] = $category;
            } else {
                $categories[$category['parent_id']]['children'][] = $category;
            }
        }
        
        return $tree;
    }
    
    // Get category name by ID
    public function getCategoryName($id){
        $sql = "SELECT name FROM categories WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['name'] : null;
    }
}
?>