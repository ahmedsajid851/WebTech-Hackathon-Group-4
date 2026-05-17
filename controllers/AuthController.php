<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/helpers.php';

class AuthController {
    /**
     * @var User $userModel
     */
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    // Handle registration
    public function register() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $name = sanitize($_POST["name"] ?? "");
            $email = sanitize($_POST["email"] ?? "");
            $password = $_POST["password"] ?? "";
            $confirmPassword = $_POST["confirm_password"] ?? "";
            $phone = sanitize($_POST["phone"] ?? "");
            
            // Validation
            $hasError = false;
            
            if (empty($name)) {
                $_SESSION["nameError"] = "Name is required";
                $hasError = true;
            } else {
                unset($_SESSION["nameError"]);
            }
            
            if (empty($email)) {
                $_SESSION["emailError"] = "Email is required";
                $hasError = true;
            } elseif (!validateEmail($email)) {
                $_SESSION["emailError"] = "Invalid email format";
                $hasError = true;
            } else {
                unset($_SESSION["emailError"]);
            }
            
            if (empty($password)) {
                $_SESSION["passwordError"] = "Password is required";
                $hasError = true;
            } elseif (strlen($password) < 8) {
                $_SESSION["passwordError"] = "Password must be at least 8 characters";
                $hasError = true;
            } else {
                unset($_SESSION["passwordError"]);
            }
            
            if ($password !== $confirmPassword) {
                $_SESSION["confirmError"] = "Passwords do not match";
                $hasError = true;
            } else {
                unset($_SESSION["confirmError"]);
            }
            
            if ($hasError) {
                $_SESSION["name"] = $name;
                $_SESSION["email"] = $email;
                $_SESSION["phone"] = $phone;
                header("Location: ../views/auth/register.php");
                exit();
            }
            
            // Register user
            $result = $this->userModel->register($name, $email, $password, $phone);
            
            if ($result["success"]) {
                $_SESSION["flash_success"] = $result["message"];
                header("Location: ../views/auth/login.php");
                exit();
            } else {
                $_SESSION["registerError"] = $result["message"];
                $_SESSION["name"] = $name;
                $_SESSION["email"] = $email;
                $_SESSION["phone"] = $phone;
                header("Location: ../views/auth/register.php");
                exit();
            }
        }
    }
    
    // Handle login
    public function login() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $email = sanitize($_POST["email"] ?? "");
            $password = $_POST["password"] ?? "";
            
            // Validation
            $hasError = false;
            
            if (empty($email)) {
                $_SESSION["emailError"] = "Email is required";
                $hasError = true;
            } else {
                unset($_SESSION["emailError"]);
            }
            
            if (empty($password)) {
                $_SESSION["passwordError"] = "Password is required";
                $hasError = true;
            } else {
                unset($_SESSION["passwordError"]);
            }
            
            if ($hasError) {
                $_SESSION["email"] = $email;
                header("Location: ../views/auth/login.php");
                exit();
            }
            
            // Login user
            $result = $this->userModel->login($email, $password);
            
            if ($result["success"]) {
                $_SESSION["flash_success"] = $result["message"];
                if ($result["role"] === "admin") {
                    header("Location: ../views/admin/dashboard.php");
                } else {
                    header("Location: ../views/dashboard.php");
                }
            } else {
                $_SESSION["loginError"] = $result["message"];
                $_SESSION["email"] = $email;
                header("Location: ../views/auth/login.php");
            }
            exit();
        }
    }
    
    // Handle logout
    public function logout() {
        // Destroy all session data
        session_destroy();
        
        // Clear session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Redirect to login page
        header("Location: ../views/auth/login.php");
        exit();
    }
}

// Route handling for direct script access
if (basename($_SERVER['SCRIPT_FILENAME']) == 'AuthController.php') {
    $action = $_GET['action'] ?? '';
    $auth = new AuthController();
    
    switch ($action) {
        case 'register':
            $auth->register();
            break;
        case 'login':
            $auth->login();
            break;
        case 'logout':
            $auth->logout();
            break;
        default:
            header("Location: ../views/auth/login.php");
            exit();
    }
}
?>