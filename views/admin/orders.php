<?php
// views/admin/orders.php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Order.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

$database = new DatabaseConnection();
$connection = $database->openConnection();

$orderModel = new Order($connection);
$orders = $orderModel->getAllOrders();

function getStatusBadgeClass($status) {
    $classes = [
        'Pending' => 'badge-warning',
        'Processing' => 'badge-info',
        'Shipped' => 'badge-primary',
        'Delivered' => 'badge-success',
        'Cancelled' => 'badge-danger'
    ];
    return $classes[$status] ?? 'badge-secondary';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:Arial;background:#f0f0f0;}
        
        .top-nav{background:#1a1a1a;color:white;padding:15px 20px;position:sticky;top:0;display:flex;justify-content:space-between;align-items:center;}
        .top-nav h2{font-size:18px;}
        .user-info{display:flex;gap:15px;align-items:center;}
        .logout-btn{background:#d9534f;color:white;padding:5px 12px;text-decoration:none;border-radius:3px;}
        .logout-btn:hover{background:#c9302c;}
        
        .sidebar{width:200px;background:#2c2c2c;position:sticky;top:52px;height:calc(100vh - 52px);}
        .sidebar a{color:#ddd;display:block;padding:12px 20px;text-decoration:none;border-bottom:1px solid #3a3a3a;}
        .sidebar a:hover{background:#3a3a3a;}
        .sidebar a.active{background:#007bff;color:white;}
        
        .main-container{display:flex;}
        .content{flex:1;padding:20px;}
        
        .header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;}
        .header h2{font-size:24px;}
        
        .filter-box{background:white;padding:15px;margin-bottom:20px;border:1px solid #ddd;}
        .filter-box h3{margin-bottom:10px;font-size:16px;}
        .filter-box label{margin-right:5px;}
        .filter-box select,.filter-box input{padding:8px;margin-right:10px;border:1px solid #ddd;border-radius:3px;}
        .btn{padding:8px 15px;background:#007bff;color:white;border:none;cursor:pointer;border-radius:3px;}
        .btn:hover{background:#0056b3;}
        
        table{width:100%;background:white;border-collapse:collapse;}
        th,td{padding:12px;text-align:left;border-bottom:1px solid #ddd;}
        th{background:#f5f5f5;font-weight:bold;}
        tr:hover{background:#f9f9f9;}
        
        .badge{display:inline-block;padding:3px 8px;border-radius:3px;font-size:12px;}
        .badge-warning{background:#ffc107;color:#000;}
        .badge-info{background:#17a2b8;color:white;}
        .badge-primary{background:#007bff;color:white;}
        .badge-success{background:#28a745;color:white;}
        .badge-danger{background:#dc3545;color:white;}
        
        .status-select{padding:5px;border:1px solid #ddd;border-radius:3px;}
        
        .message{padding:10px;margin-bottom:20px;border-radius:3px;}
        .success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;}
        .error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;}
        
        .empty{text-align:center;padding:40px;color:#999;}
    </style>
    <script>
        function updateStatus(orderId, selectElement){
            var newStatus = selectElement.value;
            
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function(){
                if(this.readyState == 4 && this.status == 200){
                    var response = JSON.parse(this.responseText);
                    if(response.success){
                        var badge = document.getElementById("badge-" + orderId);
                        badge.className = "badge " + getBadgeClass(newStatus);
                        badge.innerHTML = newStatus;
                        showMessage("Order #" + orderId + " updated to " + newStatus, "success");
                    } else {
                        showMessage("Failed to update status", "error");
                    }
                }
            };
            xhttp.open("PUT", "../../api/orders.php?id=" + orderId, true);
            xhttp.setRequestHeader("Content-Type", "application/json");
            xhttp.send(JSON.stringify({status: newStatus}));
        }
        
        function getBadgeClass(status){
            var classes = {
                'Pending': 'badge-warning',
                'Processing': 'badge-info',
                'Shipped': 'badge-primary',
                'Delivered': 'badge-success',
                'Cancelled': 'badge-danger'
            };
            return classes[status] || 'badge-secondary';
        }
        
        function filterOrders(){
            var status = document.getElementById("statusFilter").value;
            var date = document.getElementById("dateFilter").value;
            var rows = document.getElementsByClassName("order-row");
            
            for(var i = 0; i < rows.length; i++){
                var row = rows[i];
                var rowStatus = row.getAttribute("data-status");
                var rowDate = row.getAttribute("data-date");
                
                var statusMatch = (status === "" || rowStatus === status);
                var dateMatch = (date === "" || rowDate >= date);
                
                row.style.display = (statusMatch && dateMatch) ? "" : "none";
            }
        }
        
        function resetFilters(){
            document.getElementById("statusFilter").value = "";
            document.getElementById("dateFilter").value = "";
            filterOrders();
        }
        
        function showMessage(msg, type){
            var messageDiv = document.getElementById("message");
            messageDiv.innerHTML = '<div class="message ' + type + '">' + msg + '</div>';
            setTimeout(function(){
                messageDiv.innerHTML = "";
            }, 3000);
        }
    </script>
</head>
<body>
    <div class="top-nav">
        <h2>Admin Panel</h2>
        <div class="user-info">
            <span><?php echo htmlspecialchars($_SESSION['name'] ?? 'Guest'); ?></span>
            <span><?php echo $_SESSION['role'] ?? 'admin'; ?></span>
            <a href="../../views/auth/logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="main-container">
        <div class="sidebar">
            <a href="dashboard.php">Dashboard</a>
            <a href="categories.php">Categories</a>
            <a href="products.php">Products</a>
            <a href="orders.php" class="active">Orders</a>
        </div>
        
        <div class="content">
            <div class="header">
                <h2>Manage Orders</h2>
            </div>
            
            <div id="message"></div>
            
            <div class="filter-box">
                <h3>Filter Orders</h3>
                <label>Status:</label>
                <select id="statusFilter" onchange="filterOrders()">
                    <option value="">All</option>
                    <option value="Pending">Pending</option>
                    <option value="Processing">Processing</option>
                    <option value="Shipped">Shipped</option>
                    <option value="Delivered">Delivered</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
                
                <label>Date From:</label>
                <input type="date" id="dateFilter" onchange="filterOrders()">
                
                <button onclick="resetFilters()" class="btn">Reset Filters</button>
            </div>
            
            <?php if(empty($orders)): ?>
                <div class="empty">No orders found</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Shipping Address</th>
                            <th>Payment Method</th>
                            <th>Total Amount</th>
                            <th>Order Date</th>
                            <th>Status</th>
                            <th>Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orders as $order): ?>
                            <tr class="order-row" 
                                data-status="<?php echo $order['status']; ?>" 
                                data-date="<?php echo date('Y-m-d', strtotime($order['created_at'])); ?>">
                                <td><?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['email']); ?></td>
                                <td><?php echo htmlspecialchars($order['shipping_address']); ?></td>
                                <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <span id="badge-<?php echo $order['id']; ?>" 
                                          class="badge <?php echo getStatusBadgeClass($order['status']); ?>">
                                        <?php echo $order['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <select class="status-select" onchange="updateStatus(<?php echo $order['id']; ?>, this)">
                                        <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Processing" <?php echo $order['status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="Shipped" <?php echo $order['status'] == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="Delivered" <?php echo $order['status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="Cancelled" <?php echo $order['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>