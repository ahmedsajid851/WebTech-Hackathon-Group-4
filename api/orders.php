<?php
// api/orders.php
header('Content-Type: application/json');

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/../config/db.php';

$database = new Database();
$connection = $database->openConnection();

// Handle GET request - Fetch orders
if($_SERVER['REQUEST_METHOD'] === 'GET'){
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    
    if(isset($_GET['id'])){
        $order_id = $_GET['id'];
        
        if($role === 'admin'){
            $sql = "SELECT o.*, u.name as customer_name, u.email 
                    FROM orders o 
                    JOIN users u ON o.user_id = u.id 
                    WHERE o.id = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $order_id);
        } else {
            $sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("ii", $order_id, $user_id);
        }
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        
        // Get order items
        $items_sql = "SELECT oi.*, p.name, p.primary_image_path 
                      FROM order_items oi 
                      JOIN products p ON oi.product_id = p.id 
                      WHERE oi.order_id = ?";
        $items_stmt = $connection->prepare($items_sql);
        $items_stmt->bind_param("i", $order_id);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();
        
        $items = [];
        while($item = $items_result->fetch_assoc()){
            $items[] = $item;
        }
        
        $order['items'] = $items;
        echo json_encode($order);
    }
    else {
        if($role === 'admin'){
            $sql = "SELECT o.*, u.name as customer_name, u.email 
                    FROM orders o 
                    JOIN users u ON o.user_id = u.id 
                    ORDER BY o.created_at DESC";
            $result = $connection->query($sql);
        } else {
            $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
        }
        
        $orders = [];
        while($row = $result->fetch_assoc()){
            $orders[] = $row;
        }
        echo json_encode(['orders' => $orders]);
    }
}

// ========== FIXED: Handle PUT request to update order status ==========
if($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['id'])){
    // Check if admin
    if($_SESSION['role'] !== 'admin'){
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        exit();
    }
    
    $order_id = $_GET['id'];
    $input = json_decode(file_get_contents('php://input'), true);
    $status = $input['status'] ?? '';
    
    // Validate status
    $allowed_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
    if(!in_array($status, $allowed_statuses)){
        http_response_code(400);
        echo json_encode(['error' => 'Invalid status']);
        exit();
    }
    
    // Update order status
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("si", $status, $order_id);
    
    if($stmt->execute()){
        echo json_encode(['ok' => true, 'status' => $status, 'message' => 'Order status updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update order status: ' . $connection->error]);
    }
    exit();
}

$database->closeConnection($connection);
?>