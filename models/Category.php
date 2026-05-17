<?php
// models/Category.php

class Category {
    /**
     * @var mysqli $connection
     */
    private $connection;
    
    public function __construct($connection) {
        $this->connection = $connection;
    }
    
    /**
     * Get all categories as tree
     * @return array
     */
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
    
    /**
     * Get categories with level indentation for dropdown
     * @return array
     */
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
            
            // Flatten array
            $flat = [];
            $this->flattenCategories($categories, $flat);
            return $flat;
        }
        return [];
    }
    
    /**
     * Add level to categories
     * @param array &$categories
     * @param int $level
     */
    private function addLevel(&$categories, $level = 0) {
        foreach($categories as &$cat) {
            $cat['level'] = $level;
            if(!empty($cat['children'])) {
                $this->addLevel($cat['children'], $level + 1);
            }
        }
    }
    
    /**
     * Flatten categories array
     * @param array $categories
     * @param array &$result
     */
    private function flattenCategories($categories, &$result) {
        foreach($categories as $cat) {
            $result[] = $cat;
            if(!empty($cat['children'])) {
                $this->flattenCategories($cat['children'], $result);
            }
        }
    }
    
    /**
     * Get all categories for parent dropdown
     * @return array
     */
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
    
    /**
     * Get category by ID
     * @param int $id
     * @return array|null
     */
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
    
    /**
     * Check if category has children
     * @param int $id
     * @return bool
     */
    public function hasChildren($id) {
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM categories WHERE parent_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
    
    /**
     * Check if category has products
     * @param int $id
     * @return bool
     */
    public function hasProducts($id) {
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
    
    /**
     * Create new category
     * @param string $name
     * @param int|null $parentId
     * @return bool
     */
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
    
    /**
     * Update category
     * @param int $id
     * @param string $name
     * @param int|null $parentId
     * @return bool
     */
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
    
    /**
     * Delete category
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->connection->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>