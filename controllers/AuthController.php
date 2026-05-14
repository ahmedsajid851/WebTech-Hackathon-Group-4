<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../models/User.php";

$userModel = new User($pdo);

$action = $_GET['action'] ?? '';

if ($action === 'register') {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $existing = $userModel->findByEmail($email);

    if ($existing) {
        die("Email already exists");
    }

    $userModel->create(
        $name,
        $email,
        $password
    );

    header("Location: ../views/auth/login.php");
    exit;
}

if ($action === 'login') {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = $userModel->findByEmail($email);

    if (
        $user &&
        password_verify(
            $password,
            $user['password_hash']
        )
    ) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        header("Location: ../public/index.php");
        exit;
    }

    die("Invalid login");
}

if ($action === 'logout') {

    session_destroy();

    header("Location: ../views/auth/login.php");
    exit;
}
?>