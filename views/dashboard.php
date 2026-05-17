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
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:Arial;background:#f0f0f0;}
        .header{background:#2c3e50;color:white;padding:15px 20px;display:flex;justify-content:space-between;}
        .nav a{color:white;text-decoration:none;margin-left:15px;padding:5px 10px;cursor:pointer;}
        .nav a:hover{background:#34495e;}
        .container{max-width:800px;margin:20px auto;padding:0 20px;}
        .welcome-box{background:white;padding:20px;border:1px solid #ddd;margin-bottom:20px;}
        .card{background:white;border:1px solid #ddd;margin-bottom:20px;}
        .card-header{padding:12px 15px;border-bottom:1px solid #ddd;background:#f9f9f9;}
        .card-body{padding:15px;}
        .info-row{padding:10px 0;border-bottom:1px solid #eee;display:flex;}
        .info-label{font-weight:bold;width:130px;display:inline-block;}
        .info-value{flex:1;}
        .hidden{display:none;}
        .password-form{margin-top:15px;padding-top:15px;border-top:1px solid #eee;}
        .password-form h4{margin-bottom:15px;}
        .form-field{margin-bottom:15px;}
        .form-field label{display:block;font-weight:bold;margin-bottom:5px;}
        .password-form input{width:100%;max-width:300px;padding:8px;border:1px solid #ddd;}
        .password-form button{background:#2c3e50;color:white;padding:8px 20px;border:none;cursor:pointer;margin-top:5px;}
        .password-form button:hover{background:#34495e;}
        .success-msg{background:#d4edda;color:#155724;padding:10px;margin-bottom:15px;border:1px solid #c3e6cb;}
        .error-msg{background:#f8d7da;color:#721c24;padding:10px;margin-bottom:15px;border:1px solid #f5c6cb;}
        .product-placeholder{text-align:center;padding:40px;color:#999;}
    </style>
    <script>
        // Cross-tab logout function
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
        
        // Check session every 3 seconds (backup method)
        setInterval(function() {
            fetch('../api/auth.php?action=checkSession')
                .then(response => response.json())
                .then(data => {
                    if (!data.loggedIn) {
                        window.location.href = '../views/auth/login.php';
                    }
                })
                .catch(() => {});
        }, 3000);
    </script>
</head>
<body>
    <div class="header">
        <div class="logo"><?php echo SITE_NAME; ?></div>
        <div class="nav">
            <a onclick="showHome()">Home</a>
            <a href="#">Orders</a>
            <a onclick="showProfile()">Profile</a>
            <a href="javascript:void(0)" onclick="logoutUser()">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome-box">
            <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
            <p>Email: <?php echo htmlspecialchars($userEmail); ?></p>
            <p>✓ Logged in as <strong><?php echo ucfirst($userRole); ?></strong></p>
        </div>
        
        <!-- Account Information Card -->
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
                    <span class="info-value"><?php echo htmlspecialchars($username); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo htmlspecialchars($userEmail); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value"><?php echo htmlspecialchars($userPhone); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Role:</span>
                    <span class="info-value"><?php echo ucfirst($userRole); ?></span>
                </div>
                
                <div class="password-form">
                    <h4>Change Password</h4>
                    <form method="post">
                        <div class="form-field">
                            <label>Current Password</label>
                            <input type="password" name="old_password" required>
                        </div>
                        <div class="form-field">
                            <label>New Password</label>
                            <input type="password" name="new_password" required>
                        </div>
                        <div class="form-field">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" required>
                        </div>
                        <button type="submit" name="change_password">Update</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Product Catalogue Card -->
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