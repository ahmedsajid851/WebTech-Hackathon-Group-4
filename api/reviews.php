<?php
// api/reviews.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Simple response function
function sendResponse($success, $message, $data = []) {
    $response = ['ok' => $success, 'error' => $message];
    $response = array_merge($response, $data);
    echo json_encode($response);
    exit();
}

// Check login
if(!isset($_SESSION['user_id'])){
    sendResponse(false, 'Please login first');
}

// Only handle POST
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    sendResponse(false, 'Invalid request method');
}

// Get POST data
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';

// Validate
if($product_id <= 0){
    sendResponse(false, 'Product ID is required');
}

if($rating < 1 || $rating > 5){
    sendResponse(false, 'Rating must be between 1 and 5');
}

if(empty($review_text)){
    sendResponse(false, 'Review text is required');
}

// Database connection
require_once __DIR__ . '/../config/db.php';

try {
    $database = new Database();
    $connection = $database->openConnection();
    
    $user_id = $_SESSION['user_id'];
    
    // Check if user has delivered order for this product
    $check_sql = "SELECT o.id FROM orders o 
                  JOIN order_items oi ON o.id = oi.order_id 
                  WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'Delivered' 
                  LIMIT 1";
    
    $check_stmt = $connection->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $product_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if($check_result->num_rows === 0){
        $database->closeConnection($connection);
        sendResponse(false, 'You can only review products from delivered orders');
    }
    
    // Check if already reviewed
    $review_check_sql = "SELECT id FROM reviews WHERE user_id = ? AND product_id = ?";
    $review_check_stmt = $connection->prepare($review_check_sql);
    $review_check_stmt->bind_param("ii", $user_id, $product_id);
    $review_check_stmt->execute();
    $review_check_result = $review_check_stmt->get_result();
    
    if($review_check_result->num_rows > 0){
        $database->closeConnection($connection);
        sendResponse(false, 'You have already reviewed this product');
    }
    
    // Insert review
    $insert_sql = "INSERT INTO reviews (product_id, user_id, rating, review_text, created_at) 
                   VALUES (?, ?, ?, ?, NOW())";
    
    $insert_stmt = $connection->prepare($insert_sql);
    $insert_stmt->bind_param("iiis", $product_id, $user_id, $rating, $review_text);
    
    if($insert_stmt->execute()){
        // Get updated average rating
        $avg_sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                    FROM reviews WHERE product_id = ?";
        $avg_stmt = $connection->prepare($avg_sql);
        $avg_stmt->bind_param("i", $product_id);
        $avg_stmt->execute();
        $avg_result = $avg_stmt->get_result();
        $avg_data = $avg_result->fetch_assoc();
        
        $database->closeConnection($connection);
        
        echo json_encode([
            'ok' => true,
            'message' => 'Review submitted successfully',
            'avg_rating' => round($avg_data['avg_rating'] ?? 0, 1),
            'total_reviews' => (int)($avg_data['total_reviews'] ?? 0)
        ]);
        exit();
    } else {
        $database->closeConnection($connection);
        sendResponse(false, 'Failed to submit review: ' . $connection->error);
    }
    
} catch(Exception $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>