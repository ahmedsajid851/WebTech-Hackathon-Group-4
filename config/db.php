<?php
// config/db.php

class Database {
    // Your existing method
    function openConnection(){
        $db_host = "localhost";
        $db_user = "root";
        $db_password = ""; // Your password
        $db_name = "ecommerce_lab";
        
        $connection = new mysqli($db_host, $db_user, $db_password, $db_name);
        if($connection->connect_error){
            die("Failed to connect to database. Original Error: " . $connection->connect_error);
        }
        return $connection;
    }
    
    // Add this method for compatibility with code that uses getConnection()
    function getConnection(){
        return $this->openConnection();
    }
    
    function closeConnection($connection){
        if($connection){
            $connection->close();
        }
    }
}
?>