<?php
require_once __DIR__ . '/config.php';

// Get base URL dynamically
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $path = dirname(dirname($scriptName));
    return $protocol . '://' . $host . $path;
}

// Redirect using relative path
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Redirect to login page using absolute URL
function redirectToLogin() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $basePath = '/web-tech-hackathon-project/WebTech-Hackathon-Group-4';
    $url = $protocol . '://' . $host . $basePath . '/views/auth/login.php';
    header("Location: " . $url);
    exit();
}

// Redirect to register page using absolute URL
function redirectToRegister() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $basePath = '/web-tech-hackathon-project/WebTech-Hackathon-Group-4';
    $url = $protocol . '://' . $host . $basePath . '/views/auth/register.php';
    header("Location: " . $url);
    exit();
}

// Redirect to dashboard based on role
function redirectToDashboard() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $basePath = '/web-tech-hackathon-project/WebTech-Hackathon-Group-4';
    
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $url = $protocol . '://' . $host . $basePath . '/views/admin/dashboard.php';
    } else {
        $url = $protocol . '://' . $host . $basePath . '/views/dashboard.php';
    }
    header("Location: " . $url);
    exit();
}

// Show success message and redirect
function showSuccessAndRedirect($message, $redirectUrl, $delay = 3) {
    echo "<html>";
    echo "<head>
            <title>Success</title>
            <meta http-equiv='refresh' content='{$delay};url={$redirectUrl}'>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    text-align: center;
                    padding: 50px;
                    background: #f0f0f0;
                }
                .container {
                    max-width: 500px;
                    margin: 0 auto;
                    background: white;
                    padding: 30px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                }
                h2 {
                    color: green;
                    margin-top: 0;
                }
                .message {
                    margin: 20px 0;
                    font-size: 16px;
                }
                .redirect-link {
                    margin-top: 20px;
                }
                .redirect-link a {
                    color: #333;
                }
            </style>
          </head>";
    echo "<body>";
    echo "<div class='container'>";
    echo "<h2>✓ Success!</h2>";
    echo "<div class='message'>" . htmlspecialchars($message) . "</div>";
    echo "<div class='redirect-link'>Redirecting in {$delay} seconds. <a href='{$redirectUrl}'>Click here if not redirected</a></div>";
    echo "</div>";
    echo "</body>";
    echo "</html>";
    exit();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION["user_id"]) && isset($_SESSION["isLoggedIn"]) && $_SESSION["isLoggedIn"] === true;
}

// Check if user is admin
function isAdmin() {
    return isLoggedIn() && isset($_SESSION["role"]) && $_SESSION["role"] === "admin";
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION["user_id"] ?? null;
}

// Get current user name
function getCurrentUserName() {
    return $_SESSION["user_name"] ?? "";
}

// Set flash message
function setFlash($key, $message) {
    $_SESSION["flash_" . $key] = $message;
}

// Get flash message
function getFlash($key) {
    $message = $_SESSION["flash_" . $key] ?? "";
    unset($_SESSION["flash_" . $key]);
    return $message;
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Generate CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>