<?php
// models/Order.php

class Order {
    /**
     * @var mysqli $connection
     */
    private $connection;
    
    public function __construct($connection) {
        $this->connection = $connection;
    }
    
    /**
     * Get all orders with customer details
     * @return array
     */
    public function getAllOrders() {
        $sql = "SELECT o.*, u.name as customer_name, u.email 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                ORDER BY o.created_at DESC";
        $result = $this->connection->query($sql);
        $orders = [];
        
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }
        return $orders;
    }
    
    /**
     * Get orders for specific user
     * @param int $userId
     * @return array
     */
    public function getUserOrders($userId) {
        $stmt = $this->connection->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = [];
        
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }
        return $orders;
    }
    
    /**
     * Get order by ID
     * @param int $id
     * @return array|null
     */
    public function getOrderById($id) {
        $stmt = $this->connection->prepare("SELECT o.*, u.name as customer_name, u.email 
                                            FROM orders o 
                                            JOIN users u ON o.user_id = u.id 
                                            WHERE o.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
    
    /**
     * Get order by ID and user (for customers)
     * @param int $orderId
     * @param int $userId
     * @return array|null
     */
    public function getOrderByIdAndUser($orderId, $userId) {
        $stmt = $this->connection->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $orderId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
    
    /**
     * Get order items
     * @param int $orderId
     * @return array
     */
    public function getOrderItems($orderId) {
        $stmt = $this->connection->prepare("SELECT oi.*, p.name as product_name, p.primary_image_path 
                                            FROM order_items oi 
                                            JOIN products p ON oi.product_id = p.id 
                                            WHERE oi.order_id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
        return $items;
    }
    
    /**
     * Update order status
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus($id, $status) {
        $stmt = $this->connection->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }
    
    /**
     * Create new order
     * @param int $userId
     * @param string $shippingAddress
     * @param string $paymentMethod
     * @param float $totalAmount
     * @return int|bool
     */
    public function createOrder($userId, $shippingAddress, $paymentMethod, $totalAmount) {
        $status = 'Pending';
        $stmt = $this->connection->prepare("INSERT INTO orders (user_id, shipping_address, payment_method, total_amount, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issds", $userId, $shippingAddress, $paymentMethod, $totalAmount, $status);
        
        if($stmt->execute()) {
            return $this->connection->insert_id;
        }
        return false;
    }
    
    /**
     * Add item to order
     * @param int $orderId
     * @param int $productId
     * @param int $quantity
     * @param float $unitPrice
     * @return bool
     */
    public function addOrderItem($orderId, $productId, $quantity, $unitPrice) {
        $stmt = $this->connection->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $orderId, $productId, $quantity, $unitPrice);
        return $stmt->execute();
    }
}
?>
