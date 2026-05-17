<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../models/Order.php';

session_start();

if(!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$database = new Database();
$connection = $database->openConnection();  // Fixed: use openConnection()
$orderModel = new Order($connection);

// GET - Fetch orders
if($_SERVER['REQUEST_METHOD'] === 'GET'){
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    
    if(isset($_GET['id'])){
        $order_id = $_GET['id'];
        
        if($role === 'admin'){
            $result = $orderModel->getOrderById($order_id);
        } else {
            $result = $orderModel->getOrderByIdAndUser($order_id, $user_id);
        }
        
        if($result){
            $items = $orderModel->getOrderItems($order_id);
            $result['items'] = $items;
            echo json_encode($result);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Order not found']);
        }
    } else {
        if($role === 'admin'){
            $orders = $orderModel->getAllOrders();
        } else {
            $orders = $orderModel->getUserOrders($user_id);
        }
        echo json_encode(['orders' => $orders]);
    }
}

// PUT - Update order status (Admin only)
if($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['id'])){
    if($_SESSION['role'] !== 'admin'){
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        exit();
    }
    
    $order_id = $_GET['id'];
    $input = json_decode(file_get_contents('php://input'), true);
    $status = $input['status'] ?? '';
    
    $allowed_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
    if(!in_array($status, $allowed_statuses)){
        http_response_code(400);
        echo json_encode(['error' => 'Invalid status']);
        exit();
    }
    
    if($orderModel->updateStatus($order_id, $status)){
        echo json_encode(['ok' => true, 'status' => $status]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update order status']);
    }
}
?>