<?php
// controllers/ProfileController.php

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id'])){
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';

$database = new Database();
$connection = $database->openConnection();
$userModel = new User($connection);
$user_id = $_SESSION['user_id'];

$action = $_GET['action'] ?? '';

// ========== 1. UPDATE PROFILE ==========
if($action == 'update'){
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    $errors = [];
    if(empty($name)) $errors[] = "Name is required";
    if(empty($email)) $errors[] = "Email is required";
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    
    if(!empty($errors)){
        echo json_encode(['success' => false, 'error' => implode(", ", $errors)]);
        exit();
    }
    
    $userData = $userModel->getUserById($user_id);
    $shippingAddresses = $userData['shipping_addresses'] ?? '[]';
    
    if($userModel->updateProfile($user_id, $name, $email, $phone, $shippingAddresses)){
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update profile']);
    }
    exit();
}

// ========== 2. ADD ADDRESS ==========
if($action == 'addAddress'){
    $newAddress = trim($_POST['address'] ?? '');
    
    if(empty($newAddress)){
        echo json_encode(['success' => false, 'error' => 'Address is required']);
        exit();
    }
    
    $userData = $userModel->getUserById($user_id);
    $addresses = json_decode($userData['shipping_addresses'] ?? '[]', true);
    if(!is_array($addresses)) $addresses = [];
    
    if(count($addresses) >= 2){
        echo json_encode(['success' => false, 'error' => 'Maximum 2 addresses allowed']);
        exit();
    }
    
    $addresses[] = $newAddress;
    $encoded = json_encode($addresses);
    
    $sql = "UPDATE users SET shipping_addresses = ? WHERE id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("si", $encoded, $user_id);
    
    if($stmt->execute()){
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add address']);
    }
    exit();
}

// ========== 3. REMOVE ADDRESS (UPDATED - by address value) ==========
if($action == 'removeAddress'){
    $addressToRemove = trim($_POST['address'] ?? '');
    
    if(empty($addressToRemove)){
        echo json_encode(['success' => false, 'error' => 'Address is required']);
        exit();
    }
    
    $userData = $userModel->getUserById($user_id);
    $addresses = json_decode($userData['shipping_addresses'] ?? '[]', true);
    if(!is_array($addresses)) $addresses = [];
    
    $newAddresses = array_values(array_filter($addresses, function($addr) use ($addressToRemove) {
        return trim($addr) !== trim($addressToRemove);
    }));
    
    $encoded = json_encode($newAddresses);
    $sql = "UPDATE users SET shipping_addresses = ? WHERE id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("si", $encoded, $user_id);
    
    if($stmt->execute()){
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to remove address']);
    }
    exit();
}

// ========== 4. CHANGE PASSWORD ==========
if($action == 'changePassword'){
    $current = $_POST['current_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    
    if(empty($current) || empty($newPass)){
        echo json_encode(['success' => false, 'error' => 'All fields are required']);
        exit();
    }
    
    if(strlen($newPass) < 8){
        echo json_encode(['success' => false, 'error' => 'Password must be at least 8 characters']);
        exit();
    }
    
    $userData = $userModel->getUserById($user_id);
    
    if(!$userData){
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit();
    }
    
    if(!password_verify($current, $userData['password_hash'])){
        echo json_encode(['success' => false, 'error' => 'Current password is incorrect']);
        exit();
    }
    
    $newHash = password_hash($newPass, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("si", $newHash, $user_id);
    
    if($stmt->execute()){
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to change password']);
    }
    exit();
}

$connection->close();
?>