<?php
// views/admin/orders.php
require_once '../../config/helpers.php';
require_admin();

$orders = $orders ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Order Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; margin-bottom: 20px; }
        .nav { margin-bottom: 20px; padding: 10px; background: #333; border-radius: 5px; }
        .nav a { color: white; text-decoration: none; margin-right: 15px; padding: 5px 10px; }
        .nav a:hover { background: #555; border-radius: 3px; }
        .filter-box { background: #f9f9f9; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .filter-box input, .filter-box select { padding: 8px; margin-right: 10px; }
        button { padding: 8px 15px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 3px; }
        button:hover { background: #0056b3; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #007bff; color: white; }
        tr:hover { background: #f5f5f5; }
        .badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-warning { background: #ffc107; color: #000; }
        .badge-info { background: #17a2b8; color: white; }
        .badge-primary { background: #007bff; color: white; }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
        select.status-select { padding: 5px; border-radius: 3px; }
        .message { padding: 10px; margin: 10px 0; border-radius: 3px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
    <script>
        function updateStatus(orderId, selectElement){
            var newStatus = selectElement.value;
            
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function(){
                if(this.readyState == 4 && this.status == 200){
                    var response = JSON.parse(this.responseText);
                    if(response.ok){
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
    <div class="container">
        <h1>Admin - Order Management</h1>
        <div class="nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="categories.php">Categories</a>
            <a href="products.php">Products</a>
            <a href="orders.php">Orders</a>
            <a href="../../controllers/AuthController.php?action=logout">Logout</a>
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
            
            <button onclick="resetFilters()">Reset Filters</button>
        </div>
        
        <?php if(empty($orders)): ?>
            <p>No orders found.</p>
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
                        <th>Update Status</th>
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
                            <td><?php echo $order['created_at']; ?></td>
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
</body>
</html>