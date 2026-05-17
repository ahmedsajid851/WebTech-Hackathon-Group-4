<?php
require_once __DIR__ . '/../../config/helpers.php';
startSecureSession();

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
$success = $_SESSION['success'] ?? null;

unset($_SESSION['errors']);
unset($_SESSION['old_input']);
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - E-Commerce Store</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; min-height: 100vh; display: flex; justify-content: center; align-items: center; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 450px; }
        h2 { margin-bottom: 20px; color: #333; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .error { color: red; font-size: 12px; margin-top: 5px; }
        .success { color: green; font-size: 14px; margin-bottom: 15px; text-align: center; }
        button { width: 100%; padding: 12px; background: #28a745; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        button:hover { background: #218838; }
        .login-link { text-align: center; margin-top: 15px; }
        .login-link a { color: #007bff; text-decoration: none; }
    </style>
    <script>
        function checkEmail() {
            let email = document.getElementById("email").value;
            let errorDiv = document.getElementById("emailError");
            
            if (!email) {
                errorDiv.innerHTML = "";
                return;
            }
            
            let xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    let response = JSON.parse(this.responseText);
                    if (!response.available) {
                        errorDiv.innerHTML = response.message;
                        errorDiv.style.color = "red";
                    } else {
                        errorDiv.innerHTML = response.message;
                        errorDiv.style.color = "green";
                    }
                }
            };
            xhttp.open("POST", "<?php echo BASE_URL; ?>/api/auth.php?action=checkEmail", true);
            xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhttp.send("email=" + encodeURIComponent(email));
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Create Account</h2>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <!-- FIXED: Changed form action to relative path -->
        <form method="POST" action="../../controllers/AuthController.php?action=register">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($oldInput['name'] ?? ''); ?>" required>
                <?php if (isset($errors['name'])): ?>
                    <div class="error"><?php echo $errors['name']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Email *</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($oldInput['email'] ?? ''); ?>" onkeyup="checkEmail()" required>
                <div id="emailError" class="error"></div>
                <?php if (isset($errors['email'])): ?>
                    <div class="error"><?php echo $errors['email']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Phone (Optional)</label>
                <input type="tel" name="phone" value="<?php echo htmlspecialchars($oldInput['phone'] ?? ''); ?>">
                <?php if (isset($errors['phone'])): ?>
                    <div class="error"><?php echo $errors['phone']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Password * (min 8 characters)</label>
                <input type="password" name="password" required>
                <?php if (isset($errors['password'])): ?>
                    <div class="error"><?php echo $errors['password']; ?></div>
                <?php endif; ?>
            </div>
            
            <!-- FIXED: Added confirm password field -->
            <div class="form-group">
                <label>Confirm Password *</label>
                <input type="password" name="confirm_password" required>
                <?php if (isset($errors['confirm_password'])): ?>
                    <div class="error"><?php echo $errors['confirm_password']; ?></div>
                <?php endif; ?>
            </div>
            
            <button type="submit">Register</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="<?php echo BASE_URL; ?>/views/auth/login.php">Login here</a>
        </div>
    </div>
</body>
</html>