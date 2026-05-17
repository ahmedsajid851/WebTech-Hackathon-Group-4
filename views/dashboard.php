<?php
// views/dashboard.php
require_once __DIR__ . '/../config/helpers.php';
startSecureSession();

// Add this session check
if(!isset($_SESSION['user_id'])){
    header("Location: " . BASE_URL . "/views/auth/login.php");
    exit();
}

//require_customer();

$username = $_SESSION['name'] ?? '';
$userEmail = $_SESSION['email'] ?? '';
$role = $_SESSION['role'] ?? '';
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
    </style>
</head>
<body>
    <div class="navbar">
        <div style="color: white; font-weight: bold;">E-Commerce Store</div>
        <div>
            <a href="<?php echo BASE_URL; ?>/views/dashboard.php">Home</a>
            <a href="<?php echo BASE_URL; ?>/views/customer/my-orders.php">My Orders</a>
            <a href="<?php echo BASE_URL; ?>/views/profile.php">My Profile</a>
            <a href="<?php echo BASE_URL; ?>/controllers/AuthController.php?action=logout">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome-card">
            <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
            <p>Email: <?php echo htmlspecialchars($userEmail); ?></p>
            <div class="info">
                <p>✅ You are logged in as a <strong>Customer</strong></p>
                <p>🛒 Product catalogue will appear here (Task 3)</p>
            </div>
        </div>
    </div>
</body>
</html>