<?php 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/User.php';

// Check if user is logged in
if(!isLoggedIn()){
    redirect("views/auth/login.php");
    exit();
}

$username = $_SESSION["user_name"] ?? "";
$userEmail = $_SESSION["user_email"] ?? "";
$userRole = $_SESSION["role"] ?? "";
$userId = $_SESSION["user_id"] ?? "";

$userModel = new User();
$successMsg = "";
$errorMsg = "";

// Get user data including phone number
$userResult = $userModel->findById($userId);
$userData = $userResult->fetch_assoc();
$userPhone = $userData['phone'] ?? "Not provided";

// Handle password change
if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["change_password"])){
    $oldPassword = $_POST["old_password"] ?? "";
    $newPassword = $_POST["new_password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";
    
    if(empty($oldPassword)){
        $errorMsg = "Current password is required";
    } elseif(empty($newPassword)){
        $errorMsg = "New password is required";
    } elseif(strlen($newPassword) < 6){
        $errorMsg = "New password must be at least 6 characters";
    } elseif($newPassword !== $confirmPassword){
        $errorMsg = "New passwords do not match";
    } else {
        $result = $userModel->changePassword($userId, $oldPassword, $newPassword);
        if($result["success"]){
            $successMsg = $result["message"];
        } else {
            $errorMsg = $result["message"];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        
        .header {
            background: #2c3e50;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 20px;
            font-weight: bold;
        }
        
        .nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            padding: 5px 10px;
            cursor: pointer;
        }
        
        .nav a:hover {
            background: #34495e;
        }
        
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .welcome-box {
            background: white;
            padding: 25px;
            border: 1px solid #ddd;
            margin-bottom: 25px;
            border-radius: 5px;
        }
        
        .welcome-box h2 {
            margin-bottom: 10px;
            color: #333;
        }
        
        .welcome-box p {
            margin: 5px 0;
            color: #666;
        }
        
        .card {
            background: white;
            border: 1px solid #ddd;
            margin-bottom: 25px;
            border-radius: 5px;
        }
        
        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            background: #f9f9f9;
        }
        
        .card-header h3 {
            color: #333;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .info-row {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: bold;
            width: 130px;
            display: inline-block;
        }
        
        .product-placeholder {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .hidden {
            display: none;
        }
        
        .password-form {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .password-form h4 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .password-form input {
            width: 100%;
            max-width: 300px;
            padding: 8px;
            margin: 5px 0 15px 0;
            border: 1px solid #ddd;
        }
        
        .password-form button {
            background: #2c3e50;
            color: white;
            padding: 8px 20px;
            border: none;
            cursor: pointer;
        }
        
        .password-form button:hover {
            background: #34495e;
        }
        
        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #c3e6cb;
            border-radius: 3px;
        }
        
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
            border-radius: 3px;
        }
    </style>
    <script>
        // Function to logout across all tabs
        function logoutUser() {
            localStorage.setItem('logout', 'true');
            localStorage.removeItem('logout');
            window.location.href = '../controllers/AuthController.php?action=logout';
        }
        
        // Listen for logout events from other tabs
        window.addEventListener('storage', function(event) {
            if (event.key === 'logout' && event.newValue === 'true') {
                window.location.href = '../views/auth/login.php';
            }
        });
        
        function showProfile() {
            document.getElementById('profileCard').classList.remove('hidden');
            document.getElementById('productCard').classList.add('hidden');
        }
        
        function showHome() {
            document.getElementById('profileCard').classList.add('hidden');
            document.getElementById('productCard').classList.remove('hidden');
        }
        
        // Check session every 5 seconds (backup method)
        setInterval(function() {
            fetch('../api/auth.php?action=checkSession')
                .then(response => response.json())
                .then(data => {
                    if (!data.loggedIn) {
                        window.location.href = '../views/auth/login.php';
                    }
                })
                .catch(() => {});
        }, 5000);
    </script>
</head>
<body>
    <div class="header">
        <div class="logo"><?php echo SITE_NAME; ?></div>
        <div class="nav">
            <a onclick="showHome()">Home</a>
            <a href="#">My Orders</a>
            <a onclick="showProfile()">My Profile</a>
            <a href="javascript:void(0)" onclick="logoutUser()">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome-box">
            <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
            <p>Email: <?php echo htmlspecialchars($userEmail); ?></p>
            <p>✓ You are logged in as a <strong><?php echo ucfirst($userRole); ?></strong></p>
        </div>
        
        <!-- Account Information Card (Hidden by default) -->
        <div id="profileCard" class="card hidden">
            <div class="card-header">
                <h3>Account Information</h3>
            </div>
            <div class="card-body">
                <?php if($successMsg): ?>
                    <div class="success-msg"><?php echo $successMsg; ?></div>
                <?php endif; ?>
                <?php if($errorMsg): ?>
                    <div class="error-msg"><?php echo $errorMsg; ?></div>
                <?php endif; ?>
                
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span><?php echo htmlspecialchars($username); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span><?php echo htmlspecialchars($userEmail); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone Number:</span>
                    <span><?php echo htmlspecialchars($userPhone); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Role:</span>
                    <span><?php echo ucfirst($userRole); ?></span>
                </div>
                
                <!-- Change Password Section -->
                <div class="password-form">
                    <h4>Change Password</h4>
                    <form method="post">
                        <div>
                            <label>Current Password:</label><br>
                            <input type="password" name="old_password" required>
                        </div>
                        <div>
                            <label>New Password:</label><br>
                            <input type="password" name="new_password" required>
                        </div>
                        <div>
                            <label>Confirm New Password:</label><br>
                            <input type="password" name="confirm_password" required>
                        </div>
                        <button type="submit" name="change_password">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Product Catalogue Card (Visible by default) -->
        <div id="productCard" class="card">
            <div class="card-header">
                <h3>Product Catalogue</h3>
            </div>
            <div class="card-body">
                <div class="product-placeholder">
                    <p>Product catalogue will appear here (Task 3)</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>