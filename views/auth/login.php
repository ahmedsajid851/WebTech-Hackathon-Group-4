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

<html>
<head>
    <title>Login - <?php echo SITE_NAME; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f0f0f0;
        }
        .container {
            max-width: 400px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border: 1px solid #ddd;
        }
        h2 {
            margin-top: 0;
            color: #333;
        }
        input {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px 0;
            border: 1px solid #ddd;
            box-sizing: border-box;
        }
        button {
            background: #333;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background: #555;
        }
        .error {
            color: red;
            font-size: 14px;
            margin: 5px 0;
        }
        .success {
            color: green;
            font-size: 14px;
            margin: 10px 0;
            padding: 10px;
            background: #e8f5e9;
            border: 1px solid #4caf50;
        }
        .register-link {
            margin-top: 15px;
            text-align: center;
        }
        .register-link a {
            color: #333;
        }
        label {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        
        <?php if($successMsg): ?>
            <div class="success"><?php echo $successMsg; ?></div>
        <?php endif; ?>
        
        <?php if($loginError): ?>
            <div class="error"><?php echo $loginError; ?></div>
        <?php endif; ?>
        
        <form method="post" action="../../controllers/AuthController.php?action=login">
            <div>
                <label>Email:</label><br/>
                <input type="email" name="email" placeholder="Enter email" value="<?php echo htmlspecialchars($email); ?>" required/>
                <?php if($emailError): ?>
                    <div class="error"><?php echo $emailError; ?></div>
                <?php endif; ?>
            </div>
            
            <div>
                <label>Password:</label><br/>
                <input type="password" name="password" placeholder="Enter password" required/>
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