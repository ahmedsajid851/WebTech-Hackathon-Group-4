<?php
// config/db.php

class Database {
    function openConnection(){
        $db_host = "localhost";
        $db_user = "root";
        $db_password = "";
        $db_name = "ecommerce_lab";
        
        $connection = new mysqli($db_host, $db_user, $db_password, $db_name);
        if($connection->connect_error){
            die("Connection failed: " . $connection->connect_error);
        }
        return $connection;
    }
    
    function closeConnection($connection){
        if($connection){
            $connection->close();
        }
    }
}
?>