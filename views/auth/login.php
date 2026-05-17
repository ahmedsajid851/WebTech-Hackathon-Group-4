<?php 
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/helpers.php';

// If already logged in, redirect to dashboard
if(isLoggedIn()){
    if(isAdmin()){
        Header("Location: ../admin/dashboard.php");
    } else {
        Header("Location: ../dashboard.php");
    }
    exit();
}

$emailError = $_SESSION["emailError"] ?? "";
$passwordError = $_SESSION["passwordError"] ?? "";
$loginError = $_SESSION["loginError"] ?? "";
$successMsg = $_SESSION["flash_success"] ?? "";
unset($_SESSION["flash_success"]);

$email = $_SESSION["email"] ?? "";

// Clear session errors
unset($_SESSION["emailError"]);
unset($_SESSION["passwordError"]);
unset($_SESSION["loginError"]);
unset($_SESSION["email"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 400px;
            margin: 80px auto;
            background: white;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        h2 {
            margin-top: 0;
            margin-bottom: 25px;
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            box-sizing: border-box;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #2c3e50;
        }
        button {
            background: #2c3e50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 10px;
        }
        button:hover {
            background: #34495e;
        }
        .error {
            color: red;
            font-size: 13px;
            margin-top: 5px;
        }
        .success {
            color: green;
            font-size: 14px;
            margin-bottom: 20px;
            padding: 10px;
            background: #e8f5e9;
            border: 1px solid #4caf50;
            border-radius: 3px;
            text-align: center;
        }
        .register-link {
            margin-top: 20px;
            text-align: center;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .register-link a {
            color: #2c3e50;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .login-error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            border-radius: 3px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login to <?php echo SITE_NAME; ?></h2>
        
        <?php if($successMsg): ?>
            <div class="success"><?php echo $successMsg; ?></div>
        <?php endif; ?>
        
        <?php if($loginError): ?>
            <div class="login-error"><?php echo $loginError; ?></div>
        <?php endif; ?>
        
        <form method="post" action="../../controllers/AuthController.php?action=login">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>" required/>
                <?php if($emailError): ?>
                    <div class="error"><?php echo $emailError; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required/>
                <?php if($passwordError): ?>
                    <div class="error"><?php echo $passwordError; ?></div>
                <?php endif; ?>
            </div>
            
            <button type="submit">Login</button>
            
            <div class="register-link">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </form>
    </div>
</body>
</html>