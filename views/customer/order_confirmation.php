<?php
// views/customer/order_confirmation.php

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

$order_id = $_GET['order_id'] ?? $_SESSION['last_order_id'] ?? '';

if(empty($order_id)){
    header("Location: my-orders.php");
    exit();
}

require_once __DIR__ . '/../../config/db.php';

$database = new Database();
$connection = $database->openConnection();

// Get order details
$sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if(!$order){
    header("Location: my-orders.php");
    exit();
}

// Get order items
$sql_items = "SELECT oi.*, p.name FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = ?";
$stmt_items = $connection->prepare($sql_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items = $stmt_items->get_result();

$database->closeConnection($connection);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; }
        .navbar { background-color: #333; color: #fff; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .navbar a { color: #fff; text-decoration: none; margin-left: 15px; }
        .container { background-color: #fff; padding: 30px; border-radius: 6px; max-width: 800px; margin: auto; text-align: center; }
        .success-icon { font-size: 60px; color: #28a745; margin-bottom: 20px; }
        h1 { color: #28a745; margin-bottom: 20px; }
        .order-details { background-color: #f9f9f9; padding: 20px; border-radius: 6px; margin: 20px 0; text-align: left; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        .btn { display: inline-block; background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-top: 20px; margin-right: 10px; }
        .btn:hover { background-color: #0056b3; }
        .btn-green { background-color: #28a745; }
        .btn-green:hover { background-color: #218838; }
    </style>
</head>
<body>
    <div class="navbar">
        <span><strong>My E-Commerce Store</strong></span>
        <div>
            <a href="catalogue.php">Products</a>
            <a href="my-orders.php">My Orders</a>
            <a href="../dashboard.php">Dashboard</a>
            <a href="../../controllers/AuthController.php?action=logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="success-icon">✓</div>
        <h1>Order Confirmed!</h1>
        <p>Thank you for your purchase!</p>
        
        <div class="order-details">
            <h3>Order Details</h3>
            <p><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
            <p><strong>Order Date:</strong> <?php echo $order['created_at']; ?></p>
            <p><strong>Payment Method:</strong> <?php echo $order['payment_method']; ?></p>
            <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
            <p><strong>Status:</strong> <?php echo $order['status']; ?></p>
            
            <h3>Items Ordered</h3>
            <table>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                </tr>
                <?php while($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                    <td>$<?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
                <tr style="font-weight: bold;">
                    <td colspan="3" style="text-align: right;">Total Amount:</td>
                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                </tr>
            </table>
        </div>
        
        <a href="my-orders.php" class="btn">View My Orders</a>
        <a href="catalogue.php" class="btn btn-green">Continue Shopping</a>
    </div>
</body>
</html>