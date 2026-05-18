<?php
// views/admin/dashboard.php

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

require_once __DIR__ . '/../../config/db.php';

function getCurrentUserName() {
    return $_SESSION['name'] ?? 'Guest';
}

function getCurrentUserRole() {
    return $_SESSION['role'] ?? 'guest';
}

$database = new Database();
$conn = $database->openConnection();

// Initialize variables
$totalProducts = 0;
$totalCategories = 0;
$lowStockItems = 0;
$pendingOrders = 0;

// Get total products
$result = $conn->query("SELECT COUNT(*) as total FROM products");
if($result){
    $row = $result->fetch_assoc();
    $totalProducts = $row['total'];
}

// Get total categories
$result = $conn->query("SELECT COUNT(*) as total FROM categories");
if($result){
    $row = $result->fetch_assoc();
    $totalCategories = $row['total'];
}

// Get low stock items (stock_qty <= 5)
$result = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock_qty <= 5");
if($result){
    $row = $result->fetch_assoc();
    $lowStockItems = $row['total'];
}

// Get pending orders
$result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'");
if($result){
    $row = $result->fetch_assoc();
    $pendingOrders = $row['total'];
}

$database->closeConnection($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        /* Beginner CSS - Simple and Clean */
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }
        
        /* Top Navigation Bar */
        .top-nav {
            background-color: #333;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            background-color: #555;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
        }
        
        .logout-btn {
            background-color: #d9534f;
            color: white;
            padding: 5px 12px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 13px;
        }
        
        .logout-btn:hover {
            background-color: #c9302c;
        }
        
        /* Sidebar */
        .main-container {
            display: flex;
        }
        
        .sidebar {
            width: 200px;
            background-color: #2c2c2c;
            min-height: 100vh;
        }
        
        .sidebar a {
            color: #ddd;
            text-decoration: none;
            display: block;
            padding: 12px 20px;
            border-bottom: 1px solid #444;
            font-size: 14px;
        }
        
        .sidebar a:hover {
            background-color: #444;
            color: white;
        }
        
        .sidebar a.active {
            background-color: #007bff;
            color: white;
        }
        
        /* Main Content */
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
        
        /* Statistics Boxes */
        .stats {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .stat-box {
            background-color: white;
            border: 1px solid #ddd;
            padding: 15px;
            min-width: 140px;
            text-align: center;
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

<!-- Top Navigation Bar -->
<div class="top-nav">
    <h2>Admin Panel</h2>
    <div class="user-info">
        <span class="user-name"><?php echo htmlspecialchars(getCurrentUserName()); ?></span>
        <span class="user-role"><?php echo getCurrentUserRole(); ?></span>
        <a href="../../views/auth/logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<!-- Main Container -->
<div class="main-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="categories.php">Categories</a>
        <a href="products.php">Products</a>
        <a href="orders.php">Orders</a>
    </div>
    
    <!-- Main Content -->
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