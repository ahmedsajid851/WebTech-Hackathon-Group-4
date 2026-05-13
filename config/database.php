<?php
require_once __DIR__.'/config.php';
$con = 'mysql:host='.$DB_HOST.';dbname='.$DB_NAME.';charset=utf8mb4';
$pdo=new PDO($con, $DB_USER, $DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);


?>