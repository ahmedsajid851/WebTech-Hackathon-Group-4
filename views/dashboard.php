<?php
// views/dashboard.php

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id'])){
    header("Location: auth/login.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';

$database = new Database();
$connection = $database->openConnection();
$userModel = new User($connection);

// FIXED: Use getUserById() instead of findById()
$userData = $userModel->getUserById($_SESSION['user_id']);

$username = $userData['name'] ?? $_SESSION['name'] ?? '';
$userEmail = $userData['email'] ?? $_SESSION['email'] ?? '';

$database->closeConnection($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .navbar { background: #333; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 20px; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .welcome-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { margin-bottom: 10px; }
        .info { background: #e9ecef; padding: 15px; border-radius: 5px; margin-top: 15px; }
        .btn { display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 15px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="navbar">
        <div style="color: white; font-weight: bold;">E-Commerce Store</div>
        <div>
            <a href="customer/catalogue.php">Products</a>
            <a href="customer/my-orders.php">My Orders</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="../controllers/AuthController.php?action=logout">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome-card">
            <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
            <p>Email: <?php echo htmlspecialchars($userEmail); ?></p>
            <div class="info">
                <p>✅ You are logged in as a <strong>Customer</strong></p>
                <p>🛒 Click below to browse products</p>
                <a href="customer/catalogue.php" class="btn">Browse Products</a>
            </div>
        </div>
    </div>
</body>
</html>