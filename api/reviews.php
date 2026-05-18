<?php
// api/reviews.php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(['error' => 'Please login first']);
    exit();
}

require_once __DIR__ . '/../config/db.php';

$database = new Database();
$connection = $database->openConnection();
$user_id = $_SESSION['user_id'];

// Handle POST request - Add new review
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    
    $product_id = $_POST['product_id'] ?? '';
    $rating = $_POST['rating'] ?? '';
    $review_text = trim($_POST['review_text'] ?? '');
    
    // Validation
    $errors = [];
    
    if(empty($product_id)){
        $errors[] = "Product ID is required";
    }
    
    if(empty($rating)){
        $errors[] = "Rating is required";
    } elseif($rating < 1 || $rating > 5){
        $errors[] = "Rating must be between 1 and 5";
    }
    
    if(empty($review_text)){
        $errors[] = "Review text is required";
    }
    
    if(!empty($errors)){
        echo json_encode(['error' => implode(", ", $errors)]);
        exit();
    }
    
    // Check if user has purchased this product from a delivered order
    $check_sql = "SELECT o.id FROM orders o 
                  JOIN order_items oi ON o.id = oi.order_id 
                  WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'Delivered' 
                  LIMIT 1";
    
    $check_stmt = $connection->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $product_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if($check_result->num_rows === 0){
        echo json_encode(['error' => 'You can only review products from delivered orders']);
        exit();
    }
    
    // Check if user already reviewed this product
    $review_check_sql = "SELECT id FROM reviews WHERE user_id = ? AND product_id = ?";
    $review_check_stmt = $connection->prepare($review_check_sql);
    $review_check_stmt->bind_param("ii", $user_id, $product_id);
    $review_check_stmt->execute();
    $review_check_result = $review_check_stmt->get_result();
    
    if($review_check_result->num_rows > 0){
        echo json_encode(['error' => 'You have already reviewed this product']);
        exit();
    }
    
    // Insert review
    $insert_sql = "INSERT INTO reviews (product_id, user_id, rating, review_text, created_at) 
                   VALUES (?, ?, ?, ?, NOW())";
    
    $insert_stmt = $connection->prepare($insert_sql);
    $insert_stmt->bind_param("iiis", $product_id, $user_id, $rating, $review_text);
    
    if($insert_stmt->execute()){
        // Get updated average rating
        $avg_sql = "SELECT COALESCE(AVG(rating), 0) as avg_rating, COUNT(*) as total_reviews 
                    FROM reviews WHERE product_id = ?";
        $avg_stmt = $connection->prepare($avg_sql);
        $avg_stmt->bind_param("i", $product_id);
        $avg_stmt->execute();
        $avg_result = $avg_stmt->get_result();
        $avg_data = $avg_result->fetch_assoc();
        
        $response = [
            'ok' => true,
            'message' => 'Review submitted successfully!',
            'avg_rating' => round($avg_data['avg_rating'] ?? 0, 1),
            'total_reviews' => (int)($avg_data['total_reviews'] ?? 0)
        ];
        
        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Failed to submit review: ' . $connection->error]);
    }
    
    $database->closeConnection($connection);
    exit();
}

// Handle GET request - Fetch product reviews
if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['product_id'])){
    $product_id = $_GET['product_id'];
    
    $sql = "SELECT r.*, u.name as user_name 
            FROM reviews r 
            JOIN users u ON r.user_id = u.id 
            WHERE r.product_id = ? 
            ORDER BY r.created_at DESC";
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reviews = [];
    while($row = $result->fetch_assoc()){
        $reviews[] = $row;
    }
    
    // Get average rating
    $avg_sql = "SELECT COALESCE(AVG(rating), 0) as avg_rating, COUNT(*) as total_reviews 
                FROM reviews WHERE product_id = ?";
    $avg_stmt = $connection->prepare($avg_sql);
    $avg_stmt->bind_param("i", $product_id);
    $avg_stmt->execute();
    $avg_result = $avg_stmt->get_result();
    $avg_data = $avg_result->fetch_assoc();
    
    echo json_encode([
        'reviews' => $reviews,
        'avg_rating' => round($avg_data['avg_rating'] ?? 0, 1),
        'total_reviews' => (int)($avg_data['total_reviews'] ?? 0)
    ]);
    exit();
}

$database->closeConnection($connection);
echo json_encode(['error' => 'Invalid request']);
?>