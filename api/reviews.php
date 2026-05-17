<?php
header('Content-Type: application/json');
require_once '../config/db.php';

session_start();

if(!isset($_SESSION['user_id'])){
    echo json_encode(['error' => 'Please login first']);
    exit();
}

$database = new Database();
$conn = $database->openConnection();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $product_id = $_POST['product_id'] ?? '';
    $rating = $_POST['rating'] ?? '';
    $review_text = $_POST['review_text'] ?? '';
    $user_id = $_SESSION['user_id'];
    
    if(!$product_id || !$rating){
        echo json_encode(['error' => 'Product ID and rating are required']);
        exit();
    }
    
    if($rating < 1 || $rating > 5){
        echo json_encode(['error' => 'Rating must be between 1 and 5']);
        exit();
    }
    
    // Check if user has delivered order for this product
    $check = $conn->prepare("
        SELECT o.id FROM orders o 
        JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'Delivered' 
        LIMIT 1
    ");
    $check->bind_param("ii", $user_id, $product_id);
    $check->execute();
    $check_result = $check->get_result();
    
    if($check_result->num_rows === 0){
        echo json_encode(['error' => 'You can only review products from delivered orders']);
        exit();
    }
    
    // Check if already reviewed
    $check_review = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
    $check_review->bind_param("ii", $user_id, $product_id);
    $check_review->execute();
    $review_result = $check_review->get_result();
    
    if($review_result->num_rows > 0){
        echo json_encode(['error' => 'You have already reviewed this product']);
        exit();
    }
    
    // Insert review
    $insert = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, review_text, created_at) VALUES (?, ?, ?, ?, NOW())");
    $insert->bind_param("iiis", $product_id, $user_id, $rating, $review_text);
    
    if($insert->execute()){
        echo json_encode(['ok' => true, 'message' => 'Review submitted successfully']);
    } else {
        echo json_encode(['error' => 'Failed to submit review']);
    }
}

$conn->close();
?>