<?php
// views/admin/orders.php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Order.php';
require_once __DIR__ . '/../../config/helpers.php';

// Check if user is logged in and is admin
if(!isLoggedIn()){
    redirect("views/auth/login.php");
    exit();
}

if(!isAdmin()){
    redirect("views/dashboard.php");
    exit();
}

// Create database connection
$db = new DatabaseConnection();
$connection = $db->openConnection();

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

$adminName = $_SESSION["user_name"] ?? "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Order Management - <?php echo SITE_NAME; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f0f0; }
        .top-nav{background:#2c3e50;color:white;padding:15px 20px;position:sticky;top:0;display:flex;justify-content:space-between;}
        .logout-btn{background:#d9534f;color:white;padding:5px 12px;text-decoration:none;border-radius:3px;}
        .sidebar{width:200px;background:#34495e;position:sticky;top:52px;height:calc(100vh - 52px);}
        .sidebar a{color:#ddd;display:block;padding:12px 20px;text-decoration:none;border-bottom:1px solid #3a3a3a;}
        .sidebar a:hover{background:#3a3a3a;}
        .main-container{display:flex;}
        .content{flex:1;padding:20px;}
        .filter-box { background: white; padding: 15px; margin-bottom: 20px; border-radius: 5px; border:1px solid #ddd;}
        .filter-box input, .filter-box select { padding: 8px; margin-right: 10px; border:1px solid #ddd; border-radius:3px;}
        button { padding: 8px 15px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 3px; }
        button:hover { background: #0056b3; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #2c3e50; color: white; }
        tr:hover { background: #f5f5f5; }
        .badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-warning { background: #ffc107; color: #000; }
        .badge-info { background: #17a2b8; color: white; }
        .badge-primary { background: #007bff; color: white; }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
        select.status-select { padding: 5px; border-radius: 3px; border:1px solid #ddd;}
        .message { padding: 10px; margin: 10px 0; border-radius: 3px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        h2{margin-bottom:20px;color:#333;}
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
                        showMessage("Order #" + orderId + " status updated to " + newStatus, "success");
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
        <h2><?php echo SITE_NAME; ?> - Admin Panel</h2>
        <div>
            <span><?php echo htmlspecialchars($adminName); ?></span>
            <a href="../../controllers/AuthController.php?action=logout" class="logout-btn">Logout</a>
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
            <h2>Order Management</h2>
            
            <div id="message"></div>
            
            <div class="filter-box">
                <h3 style="margin-bottom:10px;">Filter Orders</h3>
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
                
                <button onclick="resetFilters()">Reset Filters</button>
            </div>
            
            <?php if(empty($orders)): ?>
                <p>No orders found.</p>
            <?php else: ?>
                <div style="overflow-x:auto;">
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
                                <th>Update Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $order): ?>
                                <tr class="order-row" 
                                    data-status="<?php echo $order['status']; ?>" 
                                    data-date="<?php echo date('Y-m-d', strtotime($order['created_at'])); ?>">
                                    <td><?php echo $order['id']; ?></dt>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></dt>
                                    <td><?php echo htmlspecialchars($order['email']); ?></dt>
                                    <td><?php echo htmlspecialchars($order['shipping_address']); ?></dt>
                                    <td><?php echo htmlspecialchars($order['payment_method']); ?></dt>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></dt>
                                    <td><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></dt>
                                    <td>
                                        <span id="badge-<?php echo $order['id']; ?>" 
                                              class="badge <?php echo getStatusBadgeClass($order['status']); ?>">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </dt>
                                    <td>
                                        <select class="status-select" onchange="updateStatus(<?php echo $order['id']; ?>, this)">
                                            <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Processing" <?php echo $order['status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="Shipped" <?php echo $order['status'] == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="Delivered" <?php echo $order['status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="Cancelled" <?php echo $order['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </dt>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>