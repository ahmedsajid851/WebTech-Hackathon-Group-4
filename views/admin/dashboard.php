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
        
        .stats{display:flex;gap:15px;margin-bottom:20px;flex-wrap:wrap;}
        .stat-box{background:white;padding:15px;border:1px solid #ddd;min-width:140px;text-align:center;}
        .stat-box h3{font-size:13px;color:#666;margin-bottom:8px;}
        .stat-box .number{font-size:28px;font-weight:bold;}
        
        .welcome-box{background:white;padding:20px;border:1px solid #ddd;margin-bottom:20px;}
        .welcome-box h2{margin-bottom:10px;}
    </style>
</head>
<body>
    <div class="top-nav">
        <h2>Admin Panel</h2>
        <div class="user-info">
            <span><?php echo htmlspecialchars(getCurrentUserName()); ?></span>
            <span><?php echo $_SESSION['role'] ?? 'admin'; ?></span>
            <a href="../../views/auth/logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="main-container">
        <div class="sidebar">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="categories.php">Categories</a>
            <a href="products.php">Products</a>
            <a href="orders.php">Orders</a>
        </div>
        
        <div class="content">
            <div class="welcome-box">
                <h2>Welcome, <?php echo htmlspecialchars(getCurrentUserName()); ?>!</h2>
                <p>You are logged in as an administrator.</p>
            </div>
            
            <div class="stats">
                <div class="stat-box">
                    <h3>Total Products</h3>
                    <div class="number"><?php echo $totalProducts; ?></div>
                </div>
                <div class="stat-box">
                    <h3>Total Categories</h3>
                    <div class="number"><?php echo $totalCategories; ?></div>
                </div>
                <div class="stat-box">
                    <h3>Low Stock Items</h3>
                    <div class="number" style="color: <?php echo $lowStockItems > 0 ? '#d9534f' : '#5cb85c'; ?>">
                        <?php echo $lowStockItems; ?>
                    </div>
                </div>
                <div class="stat-box">
                    <h3>Pending Orders</h3>
                    <div class="number"><?php echo $pendingOrders; ?></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>