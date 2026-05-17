<?php
// models/Category.php

class Category {
    private $connection;
    
    public function __construct($connection) {
        $this->connection = $connection;
    }
    
    public function getCategoryTree() {
        $sql = "SELECT * FROM categories ORDER BY parent_id, id";
        $result = $this->connection->query($sql);
        $categories = [];
        
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }
        return $categories;
    }
    
    public function getCategoryTreeWithLevel() {
        $sql = "SELECT * FROM categories ORDER BY parent_id, id";
        $result = $this->connection->query($sql);
        $categories = [];
        $all = [];
        
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $all[$row['id']] = $row;
                $all[$row['id']]['children'] = [];
            }
            
            foreach($all as $id => $cat) {
                if($cat['parent_id']) {
                    $all[$cat['parent_id']]['children'][] = &$all[$id];
                } else {
                    $categories[] = &$all[$id];
                }
            }
            
            $this->addLevel($categories);
            
            $flat = [];
            $this->flattenCategories($categories, $flat);
            return $flat;
        }
        return [];
    }
    
    private function addLevel(&$categories, $level = 0) {
        foreach($categories as &$cat) {
            $cat['level'] = $level;
            if(!empty($cat['children'])) {
                $this->addLevel($cat['children'], $level + 1);
            }
        }
    }
    
    private function flattenCategories($categories, &$result) {
        foreach($categories as $cat) {
            $result[] = $cat;
            if(!empty($cat['children'])) {
                $this->flattenCategories($cat['children'], $result);
            }
        }
    }
    
    public function getParentCategories() {
        $sql = "SELECT * FROM categories ORDER BY name";
        $result = $this->connection->query($sql);
        $categories = [];
        
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }
        return $categories;
    }
    
    public function getParentCategoriesExcluding($excludeId = null) {
        if ($excludeId) {
            $stmt = $this->connection->prepare("SELECT * FROM categories WHERE id != ? ORDER BY name");
            $stmt->bind_param("i", $excludeId);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $sql = "SELECT * FROM categories ORDER BY name";
            $result = $this->connection->query($sql);
        }
        
        $categories = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }
        return $categories;
    }
    
    public function getById($id) {
        $stmt = $this->connection->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
    
    public function hasChildren($id) {
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM categories WHERE parent_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
    
    public function hasProducts($id) {
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
    
    public function create($name, $parentId = null) {
        if ($parentId && $parentId != '') {
            $stmt = $this->connection->prepare("INSERT INTO categories (name, parent_id) VALUES (?, ?)");
            $stmt->bind_param("si", $name, $parentId);
        } else {
            $stmt = $this->connection->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->bind_param("s", $name);
        }
        return $stmt->execute();
    }
    
    public function update($id, $name, $parentId = null) {
        if ($parentId && $parentId != '') {
            $stmt = $this->connection->prepare("UPDATE categories SET name = ?, parent_id = ? WHERE id = ?");
            $stmt->bind_param("sii", $name, $parentId, $id);
        } else {
            $stmt = $this->connection->prepare("UPDATE categories SET name = ?, parent_id = NULL WHERE id = ?");
            $stmt->bind_param("si", $name, $id);
        }
        return $stmt->execute();
    }
    
    public function delete($id) {
        $stmt = $this->connection->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}