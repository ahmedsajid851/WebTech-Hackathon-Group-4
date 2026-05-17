<?php

require_once "../config/db.php";
require_once "../config/helpers.php";

require_login();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
</head>
<body>

<h1>
    Welcome
    <?= $_SESSION['name'] ?>
</h1>

<p>
    Role:
    <?= $_SESSION['role'] ?>
</p>

<a href="../controllers/AuthController.php?action=logout">
    Logout
</a>

</body>
</html>