<?php

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {

    if (!is_logged_in()) {
        header("Location: /views/auth/login.php");
        exit;
    }
}

function require_admin() {

    if (
        !isset($_SESSION['role']) ||
        $_SESSION['role'] !== 'admin'
    ) {
        die("Access Denied");
    }
}
?>