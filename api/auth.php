<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../models/User.php';

$response = [];

$action = $_GET['action'] ?? '';

// Create User model instance directly
$userModel = new User();

switch ($action) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            
            $result = $userModel->login($email, $password);
            $response = $result;
        } else {
            $response = ['success' => false, 'message' => 'Invalid request method'];
        }
        break;
        
    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $name = $data['name'] ?? '';
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            $phone = $data['phone'] ?? '';
            
            $result = $userModel->register($name, $email, $password, $phone);
            $response = $result;
        } else {
            $response = ['success' => false, 'message' => 'Invalid request method'];
        }
        break;
        
    case 'checkEmail':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $result = $userModel->findByEmail($email);
            if ($result && $result->num_rows > 0) {
                $response = ['available' => false, 'message' => 'Email already registered'];
            } else {
                $response = ['available' => true, 'message' => 'Email available'];
            }
        } else {
            $response = ['success' => false, 'message' => 'Invalid request method'];
        }
        break;
        
    case 'checkSession':
        session_start();
        $isLoggedIn = isset($_SESSION['isLoggedIn']) && $_SESSION['isLoggedIn'] === true;
        $response = ['loggedIn' => $isLoggedIn];
        break;

    
        
    default:
        $response = ['success' => false, 'message' => 'Invalid action'];
}

echo json_encode($response);
?>