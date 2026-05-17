<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/helpers.php';

class AuthController {
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
            } elseif (strlen($password) < 6) {
                $_SESSION["passwordError"] = "Password must be at least 6 characters";
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
                
                // DEBUG CODE - Shows registration success and provides manual link
                echo "<html>";
                echo "<head><title>Registration Successful</title></head>";
                echo "<body style='font-family: Arial, sans-serif; text-align: center; padding: 50px;'>";
                echo "<h2 style='color: green;'>✓ Registration Successful!</h2>";
                echo "<p>" . $result["message"] . "</p>";
                echo "<p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>";
                echo "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
                echo "<p>You will be redirected in 3 seconds...</p>";
                echo "<p>Or <a href='../views/auth/login.php' style='color: blue;'>Click here to login</a></p>";
                echo "<script>";
                echo "setTimeout(function() { window.location.href = '../views/auth/login.php'; }, 3000);";
                echo "</script>";
                echo "</body>";
                echo "</html>";
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
        session_destroy();
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