<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>

<h2>Login</h2>

<form
    method="POST"
    action="../../controllers/AuthController.php?action=login"
>

    Email:
    <input type="email" name="email" required>
    <br><br>

    Password:
    <input type="password" name="password" required>
    <br><br>

    <button type="submit">
        Login
    </button>

</form>

<a href="register.php">
    Register
</a>

</body>
</html>