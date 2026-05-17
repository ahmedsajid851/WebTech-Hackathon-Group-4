<?php
require_once __DIR__ . '/../../config/helpers.php';
header('Location: ' . BASE_URL . '/controllers/AuthController.php?action=logout');
exit();
?>