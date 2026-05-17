<?php
require_once __DIR__ . '/../../config/helpers.php';
startSecureSession();

// Check remember me cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    require_once __DIR__ . '/../../models/User.php';
    $userModel = new User();
    $token = $_COOKIE['remember_token'];
    $user = $userModel->getUserByRememberToken($token);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] === 'admin') {
            header('Location: ' . BASE_URL . '/views/admin/dashboard.php');
        } else {
            header('Location: ' . BASE_URL . '/views/dashboard.php');
        }
        exit();
    }
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: ' . BASE_URL . '/views/admin/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . '/views/dashboard.php');
    }
    exit();
}

$errors = $_SESSION['errors'] ?? [];
$oldInput = $_SESSION['old_input'] ?? [];
$error = $_SESSION['error'] ?? null;

unset($_SESSION['errors']);
unset($_SESSION['old_input']);
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - E-Commerce Store</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; min-height: 100vh; display: flex; justify-content: center; align-items: center; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 400px; }
        h2 { margin-bottom: 20px; color: #333; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .error { color: red; font-size: 12px; margin-top: 5px; }
        .error-msg { color: red; font-size: 14px; margin-bottom: 15px; text-align: center; }
        button { width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .register-link { text-align: center; margin-top: 15px; }
        .register-link a { color: #007bff; text-decoration: none; }
        .remember { margin: 10px 0; display: flex; align-items: center; gap: 8px; }
        .remember input { width: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login to Your Account</h2>
        
        <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo BASE_URL; ?>/controllers/AuthController.php?action=login">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($oldInput['email'] ?? ''); ?>">
                <?php if (isset($errors['email'])): ?>
                    <div class="error"><?php echo $errors['email']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password">
                <?php if (isset($errors['password'])): ?>
                    <div class="error"><?php echo $errors['password']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="remember">
                <input type="checkbox" name="remember_me" id="remember_me">
                <label for="remember_me">Remember Me</label>
            </div>
            
            <button type="submit">Login</button>
        </form>
        
        <div class="register-link">
            Don't have an account? <a href="<?php echo BASE_URL; ?>/views/auth/register.php">Register here</a>
        </div>
    </div>
</body>
</html>