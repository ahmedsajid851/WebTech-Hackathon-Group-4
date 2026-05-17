<?php
// views/admin/dashboard.php

require_once __DIR__ . '/../../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

$database = new DatabaseConnection();
$connection = $database->openConnection();

function getCurrentUserName() {
    return $_SESSION['name'] ?? 'Guest';
}

try {
    $stmt = $connection->prepare("SELECT COUNT(*) as total FROM products");
    $stmt->execute();
    $result = $stmt->get_result();
    $totalProducts = $result->fetch_assoc()['total'];
    
    $stmt = $connection->prepare("SELECT COUNT(*) as total FROM categories");
    $stmt->execute();
    $result = $stmt->get_result();
    $totalCategories = $result->fetch_assoc()['total'];
    
    $stmt = $connection->prepare("SELECT COUNT(*) as total FROM products WHERE stock_qty <= 5");
    $stmt->execute();
    $result = $stmt->get_result();
    $lowStockItems = $result->fetch_assoc()['total'];
    
    try {
        $stmt = $connection->prepare("SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'");
        $stmt->execute();
        $result = $stmt->get_result();
        $pendingOrders = $result->fetch_assoc()['total'];
    } catch (Exception $e) {
        $pendingOrders = 0;
    }
    
} catch (Exception $e) {
    $totalProducts = 0;
    $totalCategories = 0;
    $lowStockItems = 0;
    $pendingOrders = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:Arial;background:#f0f0f0;}
        .header{background:#2c3e50;color:white;padding:15px 20px;display:flex;justify-content:space-between;}
        .nav a{color:white;text-decoration:none;margin-left:15px;padding:5px 10px;}
        .nav a:hover{background:#34495e;}
        .container{max-width:1200px;margin:20px auto;padding:0 20px;}
        .welcome-box{background:white;padding:20px;border:1px solid #ddd;margin-bottom:20px;}
        .stats{display:flex;gap:15px;margin-bottom:20px;flex-wrap:wrap;}
        .stat{background:white;padding:15px;border:1px solid #ddd;flex:1;text-align:center;min-width:120px;}
        .stat .number{font-size:28px;font-weight:bold;color:#2c3e50;}
        .card{background:white;border:1px solid #ddd;margin-bottom:20px;}
        .card-header{padding:12px 15px;border-bottom:1px solid #ddd;background:#f9f9f9;}
        .card-body{padding:15px;}
        table{width:100%;border-collapse:collapse;}
        th,td{padding:8px;text-align:left;border-bottom:1px solid #eee;}
        th{background:#f5f5f5;}
        .badge{display:inline-block;padding:3px 8px;font-size:11px;border-radius:3px;}
        .badge-admin{background:#e74c3c;color:white;}
        .badge-customer{background:#27ae60;color:white;}
        .quick-links{display:flex;gap:10px;flex-wrap:wrap;}
        .quick-link{background:#ecf0f1;padding:6px 12px;text-decoration:none;color:#333;}
        .quick-link:hover{background:#3498db;color:white;}
    </style>
    <script>
        // Function to logout across all tabs
        function logoutUser() {
            localStorage.setItem('logout', 'true');
            localStorage.removeItem('logout');
            window.location.href = '../../controllers/AuthController.php?action=logout';
        }
        
        // Listen for logout events from other tabs
        window.addEventListener('storage', function(event) {
            if (event.key === 'logout' && event.newValue === 'true') {
                window.location.href = '../../views/auth/login.php';
            }
        });
        
        // Check session every 3 seconds (backup method)
        setInterval(function() {
            fetch('../../api/auth.php?action=checkSession')
                .then(response => response.json())
                .then(data => {
                    if (!data.loggedIn) {
                        window.location.href = '../../views/auth/login.php';
                    }
                })
                .catch(() => {});
        }, 3000);
    </script>
</head>
<body>
    <div class="header">
        <div class="logo"><?php echo SITE_NAME; ?> - Admin</div>
        <div class="nav">
            <a href="#">Dashboard</a>
            <a href="#">Orders</a>
            <a href="javascript:void(0)" onclick="logoutUser()">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome-box">
            <h2>Welcome, <?php echo htmlspecialchars($adminName); ?>!</h2>
            <p>Email: <?php echo htmlspecialchars($adminEmail); ?></p>
            <p>✓ Logged in as Administrator</p>
        </div>
        
        <div class="stats">
            <div class="stat">
                <div class="number"><?php echo $totalUsers; ?></div>
                <div class="label">Users</div>
            </div>
            <div class="stat">
                <div class="number">0</div>
                <div class="label">Orders</div>
            </div>
            <div class="stat">
                <div class="number">0</div>
                <div class="label">Products</div>
            </div>
            <div class="stat">
                <div class="number">0</div>
                <div class="label">Categories</div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3>Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="quick-links">
                    <a href="#" class="quick-link">Orders</a>
                    <a href="#" class="quick-link">Categories</a>
                    <a href="#" class="quick-link">Products</a>
                    <a href="#" class="quick-link">Users</a>
                    <a href="#" class="quick-link">Reviews</a>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3>Users List</h3>
            </div>
            <div class="card-body">
                <?php if(count($recentUsers) > 0): ?>
                    <table>
                        <thead>
                            <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($recentUsers as $row): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></dt>
                                <td><?php echo htmlspecialchars($row['name']); ?></dt>
                                <td><?php echo htmlspecialchars($row['email']); ?></dt>
                                <td><span class="badge <?php echo $row['role'] === 'admin' ? 'badge-admin' : 'badge-customer'; ?>"><?php echo ucfirst($row['role']); ?></span></dt>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No users found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>