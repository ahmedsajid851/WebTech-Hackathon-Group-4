<?php
// controllers/AuthController.php

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;
    private $conn;
    
    public function __construct(){
        $database = new Database();
        $this->conn = $database->openConnection();
        $this->userModel = new User($this->conn);
    }
    
    // Handle login
    public function login(){
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember_me = isset($_POST['remember_me']) ? true : false;
            
            $errors = [];
            
            if(empty($email)){
                $errors['email'] = "Email is required";
            } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $errors['email'] = "Please enter a valid email address";
            }
            
            if(empty($password)){
                $errors['password'] = "Password is required";
            }
            
            if(!empty($errors)){
                $_SESSION['errors'] = $errors;
                $_SESSION['old_input'] = ['email' => $email];
                header("Location: ../views/auth/login.php");
                exit();
            }
            
            // ========== FIXED: Use authenticate() not login() ==========
            $user = $this->userModel->authenticate($email, $password);
            
            if($user){
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['isLoggedIn'] = true;
                
                if($remember_me){
                    $token = bin2hex(random_bytes(32));
                    $this->userModel->setRememberToken($user['id'], $token);
                    setcookie('remember_token', $token, time() + (86400 * 30), '/');
                }
                
                if($user['role'] === 'admin'){
                    header("Location: ../views/admin/dashboard.php");
                } else {
                    header("Location: ../views/dashboard.php");
                }
                exit();
            } else {
                $_SESSION['error'] = "Invalid email or password";
                $_SESSION['old_input'] = ['email' => $email];
                header("Location: ../views/auth/login.php");
                exit();
            }
        }
    }
    
    // Handle registration
    public function register(){
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            $errors = [];
            
            if(empty($name)){
                $errors['name'] = "Name is required";
            } elseif(strlen($name) < 2){
                $errors['name'] = "Name must be at least 2 characters";
            }
            
            if(empty($email)){
                $errors['email'] = "Email is required";
            } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $errors['email'] = "Please enter a valid email address";
            } elseif($this->userModel->emailExists($email)){
                $errors['email'] = "Email already registered";
            }
            
            if(empty($password)){
                $errors['password'] = "Password is required";
            } elseif(strlen($password) < 8){
                $errors['password'] = "Password must be at least 8 characters";
            }
            
            if($password !== $confirm_password){
                $errors['confirm_password'] = "Passwords do not match";
            }
            
            if(!empty($errors)){
                $_SESSION['errors'] = $errors;
                $_SESSION['old_input'] = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone
                ];
                header("Location: ../views/auth/register.php");
                exit();
            }
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $user_id = $this->userModel->createUser($name, $email, $hashed_password, $phone);
            
            if($user_id){
                $_SESSION['success'] = "Registration successful! Please login.";
                header("Location: ../views/auth/login.php");
                exit();
            } else {
                $_SESSION['error'] = "Registration failed. Please try again.";
                header("Location: ../views/auth/register.php");
                exit();
            }
        }
    }
    
    // Handle logout
    public function logout(){
        if(isset($_SESSION['user_id'])){
            $this->userModel->setRememberToken($_SESSION['user_id'], null);
        }
        
        if(isset($_COOKIE['remember_token'])){
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        $_SESSION = array();
        
        if(isset($_COOKIE[session_name()])){
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
        
        header("Location: ../views/auth/login.php");
        exit();
    }
}

// Route handling
$controller = new AuthController();

if(isset($_GET['action'])){
    switch($_GET['action']){
        case 'login':
            $controller->login();
            break;
        case 'register':
            $controller->register();
            break;
        case 'logout':
            $controller->logout();
            break;
        default:
            header("Location: ../views/auth/login.php");
            break;
    }
} else {
    header("Location: ../views/auth/login.php");
}
?>