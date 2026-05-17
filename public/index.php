<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helpers.php';

// If user is logged in, redirect to dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect("views/admin/dashboard.php");
    } else {
        redirect("views/dashboard.php");
    }
} else {
    redirect("views/auth/login.php");
}
?>