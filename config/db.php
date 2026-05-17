<?php
class Database {
    function openConnection(){
        $db_host = "localhost";
        $db_user = "root";
        $db_password = "";
        $db_name = "ecommerce_lab";
        
        $connection = new mysqli($db_host, $db_user, $db_password, $db_name);
        if($connection->connect_error){
            die("Failed to connect to database. Original Error: " . $connection->connect_error);
        }
        return $connection;
    }
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