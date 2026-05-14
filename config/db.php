<?php

$pdo = new PDO(
    "mysql:host=localhost;dbname=ecommerce_lab",
    "root",
    "",
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]
);

session_start();
?>