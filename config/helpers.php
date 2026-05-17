<?php
define('BASE_URL', 'http://localhost/WebTech-Hackathon-Group-4');

function startSecureSession(){
    if(session_status() === PHP_SESSION_NONE){
        session_start();
    }
}

function require_admin(){
    startSecureSession();
    if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
        header('Location: ' . BASE_URL . '/views/auth/login.php');
        exit();
    }
}

function require_login(){
    startSecureSession();
    if(!isset($_SESSION['user_id'])){
        header('Location: ' . BASE_URL . '/views/auth/login.php');
        exit();
    }
}

function getStatusBadgeClass($status){
    $classes = [
        'Pending' => 'badge-warning',
        'Processing' => 'badge-info',
        'Shipped' => 'badge-primary',
        'Delivered' => 'badge-success',
        'Cancelled' => 'badge-danger'
    ];
    return $classes[$status] ?? 'badge-secondary';
}
?>