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

    $nameError = $_SESSION["nameError"] ?? "";
    $emailError = $_SESSION["emailError"] ?? "";
    $passwordError = $_SESSION["passwordError"] ?? "";
    $confirmError = $_SESSION["confirmError"] ?? "";
    $registerError = $_SESSION["registerError"] ?? "";

    $name = $_SESSION["name"] ?? "";
    $email = $_SESSION["email"] ?? "";
    $phone = $_SESSION["phone"] ?? "";

    // Clear session errors
    unset($_SESSION["nameError"]);
    unset($_SESSION["emailError"]);
    unset($_SESSION["passwordError"]);
    unset($_SESSION["confirmError"]);
    unset($_SESSION["registerError"]);
    unset($_SESSION["name"]);
    unset($_SESSION["email"]);
    unset($_SESSION["phone"]);
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Register - <?php echo SITE_NAME; ?></title>
        <script>
            function checkEmail() {
                let email = document.getElementById("email").value;
                let xhttp = new XMLHttpRequest();
                
                xhttp.onreadystatechange = function() {
                    let errorDiv = document.getElementById("emailError");
                    if (this.readyState == 4 && this.status == 200) {
                        let response = JSON.parse(this.responseText);
                        errorDiv.innerHTML = response.message;
                        errorDiv.style.color = response.available === false ? "red" : "green";
                    }
                };
                
                xhttp.open("POST", "../../api/auth.php?action=checkEmail", true);
                xhttp.setRequestHeader("content-type", "application/x-www-form-urlencoded");
                xhttp.send("email=" + encodeURIComponent(email));
            }
        </script>
        <style>
            body{font-family:Arial;background:#f0f0f0;margin:0;padding:20px;}
            .container{max-width:400px;margin:30px auto;background:white;padding:20px;border:1px solid #ddd;}
            h2{margin:0 0 20px 0;}
            input{width:100%;padding:8px;margin:5px 0 15px 0;border:1px solid #ddd;box-sizing:border-box;}
            button{width:100%;padding:10px;background:#333;color:white;border:none;cursor:pointer;}
            button:hover{background:#555;}
            .error{color:red;font-size:12px;margin:-10px 0 10px 0;}
            .login-link{text-align:center;margin-top:15px;}
            label{font-weight:bold;}
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Create Account</h2>
            
            <?php if($registerError): ?>
                <div class="error" style="margin-bottom:15px;"><?php echo $registerError; ?></div>
            <?php endif; ?>
            
            <form method="post" action="../../controllers/AuthController.php?action=register">
                <div>
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                    <?php if($nameError): ?>
                        <div class="error"><?php echo $nameError; ?></div>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label>Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" onkeyup="checkEmail()" required>
                    <?php if($emailError): ?>
                        <div class="error"><?php echo $emailError; ?></div>
                    <?php endif; ?>
                    <div id="emailError" class="error"></div>
                </div>
                
                <div>
                    <label>Phone (Optional)</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                </div>
                
                <div>
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Min 8 characters" required>
                    <?php if($passwordError): ?>
                        <div class="error"><?php echo $passwordError; ?></div>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>
                    <?php if($confirmError): ?>
                        <div class="error"><?php echo $confirmError; ?></div>
                    <?php endif; ?>
                </div>
                
                <button type="submit">Register</button>
                
                <div class="login-link">
                 Already have an account? <a href="login.php">Go to Login</a>
                </div>
            </form>
        </div>
    </body>
    </html>