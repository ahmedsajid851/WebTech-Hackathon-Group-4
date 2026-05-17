<?php
// Check if session is already active before setting ini
if (session_status() === PHP_SESSION_NONE) {
    // Session ini settings must be set BEFORE session_start()
    ini_set('session.gc_maxlifetime', 1800);
    session_start();
}

// Site configuration
define('SITE_NAME', 'E-Commerce Store');
define('SITE_URL', 'http://localhost/web-tech-hackathon-project/WebTech-Hackathon-Group-4/');
define('BASE_PATH', dirname(__DIR__) . '/');
define('UPLOAD_PATH', BASE_PATH . 'uploads/');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Dhaka');
?>