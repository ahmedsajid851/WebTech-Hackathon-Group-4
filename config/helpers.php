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
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        die("Access Denied");
    }
}

function startSessionIfNeeded() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function getCurrentUserId() {
    startSessionIfNeeded();
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUserName() {
    startSessionIfNeeded();
    return $_SESSION['name'] ?? 'Guest';
}

function getCurrentUserRole() {
    startSessionIfNeeded();
    return $_SESSION['role'] ?? 'guest';
}

function generateCsrfToken() {
    startSessionIfNeeded();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    startSessionIfNeeded();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Function to get database connection (adjust based on your database.php)
function getDBConnection() {
    require_once __DIR__ . '/database.php';
    return $db;
}
?>