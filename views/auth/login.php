<?php
// views/auth/login.php

require_once __DIR__ . '/../../config/db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: /WebTech/WebTech-Hackathon-Group-4/views/admin/dashboard.php");
        exit();
    } else {
        header("Location: /WebTech/WebTech-Hackathon-Group-4/views/customer/catalogue.php");
        exit();
    }
}

// Handle login form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                if ($remember_me) {
                    $token = bin2hex(random_bytes(32));
                    $hashed_token = password_hash($token, PASSWORD_DEFAULT);
                    
                    $updateStmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                    $updateStmt->execute([$hashed_token, $user['id']]);
                    
                    setcookie('remember_token', $token, time() + (86400 * 30), '/');
                }
                
                if ($user['role'] === 'admin') {
                    header("Location: ../../views/admin/dashboard.php");
                } else {
                    header("Location: ../../views/customer/catalogue.php");
                }
                exit();
            } else {
                $error = 'Invalid email or password';
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Commerce Store</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .login-container {
            background: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            width: 350px;
        }
        
        h2 {
            margin: 0 0 20px 0;
            text-align: center;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-size: 14px;
        }
        
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            box-sizing: border-box;
        }
        
        .checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .checkbox label {
            margin: 0;
        }
        
        button {
            width: 100%;
            padding: 10px;
            background: #333;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 16px;
        }
        
        button:hover {
            background: #555;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 3px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .register-link {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }
        
        .register-link a {
            color: #333;
            text-decoration: none;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group checkbox">
                <input type="checkbox" id="remember_me" name="remember_me">
                <label for="remember_me">Remember Me</label>
            </div>
            
            <button type="submit">Login</button>
        </form>
        
        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>