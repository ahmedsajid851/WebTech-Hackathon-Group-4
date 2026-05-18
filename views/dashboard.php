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

$userData = $userModel->getUserById($_SESSION['user_id']);

$username = $userData['name'] ?? $_SESSION['name'] ?? '';
$userEmail = $userData['email'] ?? $_SESSION['email'] ?? '';
$userPhone = $userData['phone'] ?? '';
$shippingAddresses = $userData['shipping_addresses'] ?? '[]';
$savedAddresses = json_decode($shippingAddresses, true);
if(!is_array($savedAddresses)){
    $savedAddresses = [];
}

$database->closeConnection($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard</title>
    <style>
        /* Simple beginner CSS */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }
        
        /* Navbar */
        .navbar {
            background-color: #333;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
        }
        
        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
        }
        
        .navbar a:hover {
            text-decoration: underline;
        }
        
        /* Container */
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        /* Welcome Card */
        .welcome-card {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
        
        .info {
            background-color: #e9ecef;
            padding: 15px;
            margin-top: 15px;
            border-radius: 5px;
        }
        
        /* Profile Card */
        .profile-card {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
            display: none;
        }
        
        /* Buttons */
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
            margin-right: 10px;
            border: none;
            cursor: pointer;
        }
        
        .btn-success {
            background-color: #28a745;
        }
        
        .btn-warning {
            background-color: #ffc107;
            color: #333;
        }
        
        .btn:hover {
            opacity: 0.8;
        }
        
        /* Form */
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"], input[type="email"], input[type="tel"], input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        /* Address Items */
        .address-item {
            background-color: #f8f9fa;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .remove-address {
            color: red;
            text-decoration: none;
        }
        
        /* Messages */
        .success {
            color: green;
            padding: 10px;
            background-color: #d4edda;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .error {
            color: red;
            font-size: 12px;
            margin-top: 5px;
        }
        
        /* Tabs */
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        
        .tab-btn {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            padding: 8px 20px;
            cursor: pointer;
            border-radius: 4px;
        }
        
        .tab-btn.active {
            background-color: #007bff;
            color: white;
        }
        
        .profile-section {
            display: none;
        }
        
        .profile-section.active {
            display: block;
        }
        
        h1, h2, h3 {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div style="font-weight: bold;">E-Commerce Store</div>
    <div>
        <a href="customer/catalogue.php">Products</a>
        <a href="customer/my-orders.php">My Orders</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="../controllers/AuthController.php?action=logout">Logout</a>
    </div>
</div>

<div class="container">
    <!-- Welcome Card -->
    <div class="welcome-card">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        <p>Email: <?php echo htmlspecialchars($userEmail); ?></p>
        <div class="info">
            <p>You are logged in as a <strong>Customer</strong></p>
            <button onclick="showProfile()" class="btn"> My Profile</button>
            <a href="customer/catalogue.php" class="btn"> Browse Products</a>
        </div>
    </div>

    <!-- Profile Section -->
    <div id="profileSection" class="profile-card">
        <h2>My Profile</h2>
        
        <div id="message"></div>
        
        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('info')">Personal Info</button>
            <button class="tab-btn" onclick="showTab('address')">Shipping Addresses</button>
            <button class="tab-btn" onclick="showTab('password')">Change Password</button>
        </div>
        
        <!-- Personal Info Tab -->
        <div id="infoTab" class="profile-section active">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" id="profile_name" value="<?php echo htmlspecialchars($username); ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="profile_email" value="<?php echo htmlspecialchars($userEmail); ?>">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" id="profile_phone" value="<?php echo htmlspecialchars($userPhone); ?>">
            </div>
            <button onclick="updateProfile()" class="btn btn-success">Update Profile</button>
        </div>
        
        <!-- Shipping Addresses Tab -->
        <div id="addressTab" class="profile-section">
            <h3>Saved Addresses</h3>
            <div id="addressesList">
                <?php foreach($savedAddresses as $index => $addr): ?>
                    <div class="address-item">
                        <span><?php echo htmlspecialchars($addr); ?></span>
                        <a href="#" onclick="removeAddress(<?php echo $index; ?>); return false;" class="remove-address">Remove</a>
                    </div>
                <?php endforeach; ?>
                <?php if(count($savedAddresses) >= 2): ?>
                    <p style="color:red;">Maximum 2 addresses allowed</p>
                <?php endif; ?>
            </div>
            
            <?php if(count($savedAddresses) < 2): ?>
                <h3>Add New Address</h3>
                <div class="form-group">
                    <textarea id="new_address" rows="2" placeholder="Enter new shipping address"></textarea>
                </div>
                <button onclick="addAddress()" class="btn btn-success">Add Address</button>
            <?php endif; ?>
        </div>
        
        <!-- Change Password Tab -->
        <div id="passwordTab" class="profile-section">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" id="current_password">
            </div>
            <div class="form-group">
                <label>New Password (min 8 characters)</label>
                <input type="password" id="new_password">
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" id="confirm_password">
            </div>
            <button onclick="changePassword()" class="btn btn-warning">Change Password</button>
        </div>
    </div>
</div>

<script>
    function showProfile() {
        var profile = document.getElementById("profileSection");
        if(profile.style.display === "none" || profile.style.display === ""){
            profile.style.display = "block";
        } else {
            profile.style.display = "none";
        }
    }
    
    function showTab(tab) {
        document.getElementById("infoTab").classList.remove("active");
        document.getElementById("addressTab").classList.remove("active");
        document.getElementById("passwordTab").classList.remove("active");
        
        document.querySelectorAll(".tab-btn").forEach(btn => btn.classList.remove("active"));
        
        if(tab === "info"){
            document.getElementById("infoTab").classList.add("active");
            document.querySelector(".tab-btn:nth-child(1)").classList.add("active");
        } else if(tab === "address"){
            document.getElementById("addressTab").classList.add("active");
            document.querySelector(".tab-btn:nth-child(2)").classList.add("active");
        } else if(tab === "password"){
            document.getElementById("passwordTab").classList.add("active");
            document.querySelector(".tab-btn:nth-child(3)").classList.add("active");
        }
    }
    
    function showMessage(msg, type){
        var msgDiv = document.getElementById("message");
        msgDiv.innerHTML = '<div class="' + type + '">' + msg + '</div>';
        setTimeout(function(){
            msgDiv.innerHTML = "";
        }, 3000);
    }
    
    function updateProfile() {
        var name = document.getElementById("profile_name").value;
        var email = document.getElementById("profile_email").value;
        var phone = document.getElementById("profile_phone").value;
        
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "../controllers/ProfileController.php?action=update", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if(xhr.readyState == 4 && xhr.status == 200){
                var response = JSON.parse(xhr.responseText);
                if(response.success){
                    showMessage("Profile updated successfully!", "success");
                    document.querySelector(".welcome-card h1").innerHTML = "Welcome, " + name + "!";
                } else {
                    showMessage(response.error, "error");
                }
            }
        };
        xhr.send("name=" + encodeURIComponent(name) + "&email=" + encodeURIComponent(email) + "&phone=" + encodeURIComponent(phone));
    }
    
    function addAddress() {
        var address = document.getElementById("new_address").value;
        
        if(address.trim() === ""){
            showMessage("Please enter an address", "error");
            return;
        }
        
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "../controllers/ProfileController.php?action=addAddress", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if(xhr.readyState == 4 && xhr.status == 200){
                var response = JSON.parse(xhr.responseText);
                if(response.success){
                    showMessage("Address added successfully!", "success");
                    location.reload();
                } else {
                    showMessage(response.error, "error");
                }
            }
        };
        xhr.send("address=" + encodeURIComponent(address));
    }
    
    function removeAddress(index) {
        if(confirm("Remove this address?")){
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "../controllers/ProfileController.php?action=removeAddress", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if(xhr.readyState == 4 && xhr.status == 200){
                    var response = JSON.parse(xhr.responseText);
                    if(response.success){
                        showMessage("Address removed successfully!", "success");
                        location.reload();
                    } else {
                        showMessage(response.error, "error");
                    }
                }
            };
            xhr.send("index=" + index);
        }
    }
    
    function changePassword() {
        var current = document.getElementById("current_password").value;
        var newPass = document.getElementById("new_password").value;
        var confirm = document.getElementById("confirm_password").value;
        
        if(!current || !newPass || !confirm){
            showMessage("All fields are required", "error");
            return;
        }
        
        if(newPass.length < 8){
            showMessage("New password must be at least 8 characters", "error");
            return;
        }
        
        if(newPass !== confirm){
            showMessage("New passwords do not match", "error");
            return;
        }
        
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "../controllers/ProfileController.php?action=changePassword", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if(xhr.readyState == 4 && xhr.status == 200){
                var response = JSON.parse(xhr.responseText);
                if(response.success){
                    showMessage("Password changed successfully!", "success");
                    document.getElementById("current_password").value = "";
                    document.getElementById("new_password").value = "";
                    document.getElementById("confirm_password").value = "";
                } else {
                    showMessage(response.error, "error");
                }
            }
        };
        xhr.send("current_password=" + encodeURIComponent(current) + "&new_password=" + encodeURIComponent(newPass));
    }
</script>

</body>
</html>