<?php
// views/admin/dashboard.php

require_once __DIR__ . '/../../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

function getCurrentUserName() {
    return $_SESSION['name'] ?? 'Guest';
}

function getCurrentUserRole() {
    return $_SESSION['role'] ?? 'guest';
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products");
    $stmt->execute();
    $totalProducts = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM categories");
    $stmt->execute();
    $totalCategories = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE stock_qty <= 5");
    $stmt->execute();
    $lowStockItems = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'");
        $stmt->execute();
        $pendingOrders = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (PDOException $e) {
        $pendingOrders = 0;
    }
    
} catch (PDOException $e) {
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0;
        }
        
        .top-nav {
            background: #1a1a1a;
            color: white;
            padding: 15px 25px;
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #333;
        }
        
        .top-nav h2 {
            font-size: 18px;
            font-weight: normal;
        }
        
        .user-info {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .user-name {
            font-size: 14px;
        }
        
        .user-role {
            background: #333;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
        }
        
        .logout-btn {
            background: #d9534f;
            color: white;
            padding: 5px 12px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 13px;
        }
        
        .logout-btn:hover {
            background: #c9302c;
        }
        
        .main-container {
            display: flex;
        }
        
        .sidebar {
            width: 200px;
            background: #2c2c2c;
            position: sticky;
            top: 52px;
            height: calc(100vh - 52px);
            overflow-y: auto;
        }
        
        .sidebar a {
            color: #ddd;
            text-decoration: none;
            display: block;
            padding: 12px 20px;
            border-bottom: 1px solid #3a3a3a;
            font-size: 14px;
        }
        
        .sidebar a:hover {
            background: #3a3a3a;
            color: white;
        }
        
        .sidebar a.active {
            background: #007bff;
            color: white;
        }
        
        .content {
            flex: 1;
            padding: 20px;
        }
        
        .page-title {
            margin-bottom: 20px;
        }
        
        .page-title h2 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .page-title p {
            color: #666;
            font-size: 14px;
        }
        
        .stats {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .stat-box {
            background: white;
            padding: 15px;
            border: 1px solid #ddd;
            min-width: 140px;
            text-align: center;
            border-radius: 3px;
        }
        
        .stat-box h3 {
            font-size: 13px;
            color: #666;
            margin-bottom: 8px;
        }
        
        .stat-box .number {
            font-size: 28px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="top-nav">
        <h2>Admin Panel</h2>
        <div class="user-info">
            <span class="user-name"><?php echo htmlspecialchars(getCurrentUserName()); ?></span>
            <span class="user-role"><?php echo getCurrentUserRole(); ?></span>
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
            <div class="page-title">
                <h2>Dashboard</h2>
                <p>Welcome, <?php echo htmlspecialchars(getCurrentUserName()); ?></p>
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