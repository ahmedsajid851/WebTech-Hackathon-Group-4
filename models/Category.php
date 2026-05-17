<?php
// models/Category.php

require_once __DIR__ . '/../config/db.php';

class Category {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM categories ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getParentCategories($excludeId = null) {
        $sql = "SELECT * FROM categories WHERE parent_id IS NULL";
        if ($excludeId) {
            $sql .= " AND id != ?";
        }
        $sql .= " ORDER BY name";
        $stmt = $this->pdo->prepare($sql);
        if ($excludeId) {
            $stmt->execute([$excludeId]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getChildCategories($parentId) {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE parent_id = ? ORDER BY name");
        $stmt->execute([$parentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function hasChildren($id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM categories WHERE parent_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
    
    public function hasProducts($id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
    
    public function create($name, $parentId = null) {
        $stmt = $this->pdo->prepare("INSERT INTO categories (name, parent_id) VALUES (?, ?)");
        return $stmt->execute([$name, $parentId]);
    }
    
    public function update($id, $name, $parentId = null) {
        $stmt = $this->pdo->prepare("UPDATE categories SET name = ?, parent_id = ? WHERE id = ?");
        return $stmt->execute([$name, $parentId, $id]);
    }
    
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function getCategoryTree() {
        $categories = $this->getAll();
        $tree = [];
        foreach ($categories as $category) {
            if ($category['parent_id'] === null) {
                $tree[] = $category;
                $children = $this->getChildCategories($category['id']);
                foreach ($children as $child) {
                    $child['name'] = '-- ' . $child['name'];
                    $tree[] = $child;
                }
            }
        }
        return $tree;
    }
}
?>