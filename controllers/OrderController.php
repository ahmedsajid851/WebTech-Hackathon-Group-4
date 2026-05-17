<?php
// controllers/OrderController.php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Order.php';

class OrderController {
    private $db;
    private $orderModel;
    
    public function __construct(){
        // Fix: Use DatabaseConnection class instead of Database
        $database = new DatabaseConnection();
        $this->db = $database->openConnection();
        $this->orderModel = new Order($this->db);
    }
    
    public function myOrders(){
        require_login();
        
        $user_id = $_SESSION['user_id'];
        $orders = $this->orderModel->getUserOrders($user_id);
        
        include __DIR__ . '/../views/customer/my-orders.php';
    }
    
    public function orderDetail($order_id){
        require_login();
        
        $user_id = $_SESSION['user_id'];
        $order = $this->orderModel->getOrderByIdAndUser($order_id, $user_id);
        
        if(!$order){
            header("Location: my-orders.php");
            exit();
        }
        
        $items = $this->orderModel->getOrderItems($order_id);
        
        include __DIR__ . '/../views/customer/order-detail.php';
    }
}

// Route handling
if(isset($_GET['action'])){
    $controller = new OrderController();
    
    switch($_GET['action']){
        case 'my-orders':
            $controller->myOrders();
            break;
        case 'detail':
            $order_id = $_GET['id'] ?? '';
            $controller->orderDetail($order_id);
            break;
    }
}
?>