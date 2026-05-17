<?php
// models/Review.php

class Review {
    private $conn;
    private $table_name = "reviews";
    
    public function __construct($db){
        $this->conn = $db;
    }
    
    public function addReview($user_id, $product_id, $rating, $review_text){
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, product_id, rating, review_text, created_at) 
                  VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iiis", $user_id, $product_id, $rating, $review_text);
        
        return $stmt->execute();
    }
    
    public function getProductReviews($product_id){
        $query = "SELECT r.*, u.name as user_name 
                  FROM " . $this->table_name . " r
                  JOIN users u ON r.user_id = u.id 
                  WHERE r.product_id = ? 
                  ORDER BY r.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $reviews = [];
        
        while($row = $result->fetch_assoc()){
            $reviews[] = $row;
        }
        
        return $reviews;
    }
    
    public function getAverageRating($product_id){
        $query = "SELECT COALESCE(AVG(rating), 0) as avg_rating, COUNT(*) as total_reviews 
                  FROM " . $this->table_name . " 
                  WHERE product_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    public function hasUserReviewed($user_id, $product_id){
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE user_id = ? AND product_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    public function deleteReview($review_id, $user_id, $is_admin = false){
        if($is_admin){
            $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $review_id);
        } else {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = ? AND user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $review_id, $user_id);
        }
        
        return $stmt->execute();
    }
}
?>
