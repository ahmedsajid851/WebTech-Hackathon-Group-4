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
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px 0;
            border: 1px solid #ddd;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #333;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #555;
        }
        .error {
            color: red;
            font-size: 13px;
            margin-top: -10px;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            background: #e8f5e9;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #4caf50;
            text-align: center;
        }
        .login-error {
            color: red;
            background: #f8d7da;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            text-align: center;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        label {
            font-weight: bold;
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
            <div>
                <label>Email</label><br>
                <input type="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>" required/>
                <?php if($emailError): ?>
                    <div class="error"><?php echo $emailError; ?></div>
                <?php endif; ?>
            </div>
            
            <div>
                <label>Password</label><br>
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