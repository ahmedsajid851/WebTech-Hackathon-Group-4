<?php
// controllers/ReviewController.php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/Order.php';

class ReviewController {
    private $db;
    private $reviewModel;
    private $orderModel;
    
    public function __construct(){
        // Fix: Use DatabaseConnection class instead of Database
        $database = new DatabaseConnection();
        $this->db = $database->openConnection();
        $this->reviewModel = new Review($this->db);
        $this->orderModel = new Order($this->db);
    }
    
    public function submitReview(){
        require_login();
        
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $product_id = $_POST['product_id'] ?? '';
            $rating = $_POST['rating'] ?? '';
            $review_text = $_POST['review_text'] ?? '';
            $user_id = $_SESSION['user_id'];
            $order_id = $_POST['order_id'] ?? '';
            
            $errors = [];
            
            if(!$product_id){
                $errors[] = "Product ID is required";
            }
            if(!$rating || $rating < 1 || $rating > 5){
                $errors[] = "Valid rating is required (1-5)";
            }
            if(empty($review_text)){
                $errors[] = "Review text is required";
            }
            
            if(!empty($errors)){
                $_SESSION['review_errors'] = $errors;
                header("Location: ../views/customer/order-detail.php?id=" . $order_id);
                exit();
            }
            
            // Fix: Check if user purchased the product using existing method
            // You need to add this method to Order model
            $hasPurchased = $this->checkUserPurchasedProduct($user_id, $product_id);
            if(!$hasPurchased){
                $_SESSION['review_error'] = "You can only review products from delivered orders";
                header("Location: ../views/customer/order-detail.php?id=" . $order_id);
                exit();
            }
            
            // Check if already reviewed
            if($this->reviewModel->hasUserReviewed($user_id, $product_id)){
                $_SESSION['review_error'] = "You have already reviewed this product";
                header("Location: ../views/customer/order-detail.php?id=" . $order_id);
                exit();
            }
            
            if($this->reviewModel->addReview($user_id, $product_id, $rating, $review_text)){
                $_SESSION['review_success'] = "Review submitted successfully!";
            } else {
                $_SESSION['review_error'] = "Failed to submit review";
            }
            
            header("Location: ../views/customer/order-detail.php?id=" . $order_id);
            exit();
        }
    }
    
    // Helper method to check if user purchased the product
    private function checkUserPurchasedProduct($user_id, $product_id){
        $sql = "SELECT o.id FROM orders o 
                JOIN order_items oi ON o.id = oi.order_id 
                WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'Delivered' 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
}

// Route handling
if(isset($_GET['action'])){
    $controller = new ReviewController();
    
    switch($_GET['action']){
        case 'submit':
            $controller->submitReview();
            break;
    }
}
?>