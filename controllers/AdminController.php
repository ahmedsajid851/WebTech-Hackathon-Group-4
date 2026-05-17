<?php
// controllers/AdminController.php
require_once '../config/helpers.php';
require_once '../config/db.php';
require_once '../models/Order.php';

class AdminController {
    private $db;
    private $orderModel;
    
    public function __construct(){
        $database = new Database();
        $this->db = $database->getConnection();
        $this->orderModel = new Order($this->db);
    }
    
    public function manageOrders(){
        require_admin();
        
        $orders = $this->orderModel->getAllOrders();
        
        // Apply filters if provided
        $status_filter = $_GET['status'] ?? '';
        $date_filter = $_GET['date'] ?? '';
        
        if($status_filter){
            $orders = array_filter($orders, function($order) use ($status_filter){
                return $order['status'] === $status_filter;
            });
        }
        
        if($date_filter){
            $orders = array_filter($orders, function($order) use ($date_filter){
                return substr($order['created_at'], 0, 10) >= $date_filter;
            });
        }
        
        include '../views/admin/orders.php';
    }
    
    public function updateOrderStatus($order_id, $status){
        require_admin();
        return $this->orderModel->updateStatus($order_id, $status);
    }
}

// Route handling
if(isset($_GET['action'])){
    $controller = new AdminController();
    
    switch($_GET['action']){
        case 'orders':
            $controller->manageOrders();
            break;
        case 'update_status':
            if($_SERVER['REQUEST_METHOD'] === 'POST'){
                $order_id = $_POST['order_id'] ?? '';
                $status = $_POST['status'] ?? '';
                $controller->updateOrderStatus($order_id, $status);
                header("Location: ../views/admin/orders.php");
            }
            break;
    }
}
?>