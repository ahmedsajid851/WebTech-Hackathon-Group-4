<?php
// controllers/AuthController.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';

session_start();

class AuthController {
    private $userModel;
    
    public function __construct(){
        $database = new Database();
        $connection = $database->openConnection();
        $this->userModel = new User($connection);
    }
    
    // Handle login
    public function login(){
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember_me = isset($_POST['remember_me']) ? true : false;
            
            $errors = [];
            
            // Validate email
            if(empty($email)){
                $errors['email'] = "Email is required";
            } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $errors['email'] = "Please enter a valid email address";
            }
            
            // Validate password
            if(empty($password)){
                $errors['password'] = "Password is required";
            }
            
            if(!empty($errors)){
                $_SESSION['errors'] = $errors;
                $_SESSION['old_input'] = ['email' => $email];
                header("Location: ../views/auth/login.php");
                exit();
            }
            
            // Authenticate user
            $user = $this->userModel->authenticate($email, $password);
            
            if($user){
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['isLoggedIn'] = true;
                
                // Handle Remember Me
                if($remember_me){
                    $token = bin2hex(random_bytes(32));
                    $this->userModel->setRememberToken($user['id'], $token);
                    setcookie('remember_token', $token, time() + (86400 * 30), '/');
                }
                
                // FIXED: Redirect to correct dashboard paths
                if($user['role'] === 'admin'){
                    header("Location: ../views/admin/dashboard.php");
                } else {
                    // Changed from customer/dashboard.php to dashboard.php
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
            
            // Validate name
            if(empty($name)){
                $errors['name'] = "Name is required";
            } elseif(strlen($name) < 2){
                $errors['name'] = "Name must be at least 2 characters";
            }
            
            // Validate email
            if(empty($email)){
                $errors['email'] = "Email is required";
            } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $errors['email'] = "Please enter a valid email address";
            } elseif($this->userModel->emailExists($email)){
                $errors['email'] = "Email already registered";
            }
            
            // Validate password
            if(empty($password)){
                $errors['password'] = "Password is required";
            } elseif(strlen($password) < 8){
                $errors['password'] = "Password must be at least 8 characters";
            }
            
            // Validate confirm password
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
            
            // Create user
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
        // Clear remember me token from database
        if(isset($_SESSION['user_id'])){
            $this->userModel->setRememberToken($_SESSION['user_id'], null);
        }
        
        // Clear remember me cookie
        if(isset($_COOKIE['remember_token'])){
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // Clear all session variables
        $_SESSION = array();
        
        // Destroy session cookie
        if(isset($_COOKIE[session_name()])){
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy session
        session_destroy();
        
        header("Location: ../views/auth/login.php");
        exit();
    }
    
    // Check remember me cookie
    public function checkRememberMe(){
        if(isset($_COOKIE['remember_token'])){
            $token = $_COOKIE['remember_token'];
            $user = $this->userModel->getUserByRememberToken($token);
            
            if($user){
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['isLoggedIn'] = true;
                
                if($user['role'] === 'admin'){
                    header("Location: ../views/admin/dashboard.php");
                } else {
                    // Changed from customer/dashboard.php to dashboard.php
                    header("Location: ../views/dashboard.php");
                }
                exit();
            }
        }
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
        case 'check_remember':
            $controller->checkRememberMe();
            break;
        default:
            header("Location: ../views/auth/login.php");
            break;
    }
} else {
    header("Location: ../views/auth/login.php");
}
?>