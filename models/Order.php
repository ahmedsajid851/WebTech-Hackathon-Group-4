<?php
// models/Order.php

class Order {
    private $conn;
    
    public function __construct($connection){
        $this->conn = $connection;
    }
    
    // Create new order
    public function createOrder($user_id, $shipping_address, $payment_method, $total_amount){
        $sql = "INSERT INTO orders (user_id, shipping_address, payment_method, total_amount, status, created_at) 
                VALUES (?, ?, ?, ?, 'Pending', NOW())";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("issd", $user_id, $shipping_address, $payment_method, $total_amount);
        
        if($statement->execute()){
            return $this->conn->insert_id;
        }
        return false;
    }
    
    // Add order item
    public function addOrderItem($order_id, $product_id, $quantity, $unit_price){
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price) 
                VALUES (?, ?, ?, ?)";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("iiid", $order_id, $product_id, $quantity, $unit_price);
        
        return $statement->execute();
    }
    
    // Get user orders (customer)
    public function getUserOrders($user_id){
        $sql = "SELECT * FROM orders 
                WHERE user_id = ? 
                ORDER BY created_at DESC";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("i", $user_id);
        $statement->execute();
        
        $result = $statement->get_result();
        $orders = [];
        
        while($row = $result->fetch_assoc()){
            $orders[] = $row;
        }
        
        return $orders;
    }
    
    // Get all orders (admin)
    public function getAllOrders(){
        $sql = "SELECT o.*, u.name as customer_name, u.email 
                FROM orders o
                JOIN users u ON o.user_id = u.id 
                ORDER BY o.created_at DESC";
        
        $result = $this->conn->query($sql);
        $orders = [];
        
        while($row = $result->fetch_assoc()){
            $orders[] = $row;
        }
        
        return $orders;
    }
    
    // Get order by ID (admin)
    public function getOrderById($order_id){
        $sql = "SELECT o.*, u.name as customer_name, u.email 
                FROM orders o
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = ?";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("i", $order_id);
        $statement->execute();
        
        $result = $statement->get_result();
        return $result->fetch_assoc();
    }
    
    // Get order by ID and user (customer)
    public function getOrderByIdAndUser($order_id, $user_id){
        $sql = "SELECT * FROM orders 
                WHERE id = ? AND user_id = ?";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("ii", $order_id, $user_id);
        $statement->execute();
        
        $result = $statement->get_result();
        return $result->fetch_assoc();
    }
    
    // Get order items
    public function getOrderItems($order_id){
        $sql = "SELECT oi.*, p.name, p.primary_image_path, p.price 
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("i", $order_id);
        $statement->execute();
        
        $result = $statement->get_result();
        $items = [];
        
        while($row = $result->fetch_assoc()){
            $items[] = $row;
        }
        
        return $items;
    }
    
    // Update order status
    public function updateStatus($order_id, $status){
        $sql = "UPDATE orders SET status = ? WHERE id = ?";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("si", $status, $order_id);
        
        return $statement->execute();
    }
    
    // Check if user purchased a product (for reviews)
    public function hasUserPurchasedProduct($user_id, $product_id){
        $sql = "SELECT o.id FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'Delivered'
                LIMIT 1";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("ii", $user_id, $product_id);
        $statement->execute();
        
        $result = $statement->get_result();
        return $result->num_rows > 0;
    }
    
    // Get orders by status
    public function getOrdersByStatus($status){
        $sql = "SELECT o.*, u.name as customer_name, u.email 
                FROM orders o
                JOIN users u ON o.user_id = u.id 
                WHERE o.status = ?
                ORDER BY o.created_at DESC";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("s", $status);
        $statement->execute();
        
        $result = $statement->get_result();
        $orders = [];
        
        while($row = $result->fetch_assoc()){
            $orders[] = $row;
        }
        
        return $orders;
    }
    
    // Get orders by date range
    public function getOrdersByDateRange($start_date, $end_date){
        $sql = "SELECT o.*, u.name as customer_name, u.email 
                FROM orders o
                JOIN users u ON o.user_id = u.id 
                WHERE DATE(o.created_at) BETWEEN ? AND ?
                ORDER BY o.created_at DESC";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("ss", $start_date, $end_date);
        $statement->execute();
        
        $result = $statement->get_result();
        $orders = [];
        
        while($row = $result->fetch_assoc()){
            $orders[] = $row;
        }
        
        return $orders;
    }
    
    // Get order count by status
    public function getOrderCountByStatus($status){
        $sql = "SELECT COUNT(*) as count FROM orders WHERE status = ?";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("s", $status);
        $statement->execute();
        
        $result = $statement->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'];
    }
    
    // Get total sales
    public function getTotalSales(){
        $sql = "SELECT SUM(total_amount) as total FROM orders WHERE status != 'Cancelled'";
        
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        
        return $row['total'] ?? 0;
    }
    
    // Cancel order
    public function cancelOrder($order_id, $user_id){
        // First check if order belongs to user and is pending
        $sql = "SELECT status FROM orders WHERE id = ? AND user_id = ?";
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("ii", $order_id, $user_id);
        $statement->execute();
        $result = $statement->get_result();
        $order = $result->fetch_assoc();
        
        if($order && $order['status'] == 'Pending'){
            $sql = "UPDATE orders SET status = 'Cancelled' WHERE id = ? AND user_id = ?";
            $statement = $this->conn->prepare($sql);
            $statement->bind_param("ii", $order_id, $user_id);
            return $statement->execute();
        }
        
        return false;
    }
}
?>