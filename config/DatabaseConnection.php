<?php

class DatabaseConnection {

    function openConnection() {
        $db_host = "localhost";
        $db_username = "root";
        $db_password = "";
        $db_name = "ecommerce_lab";

        $connection = new mysqli($db_host, $db_username, $db_password, $db_name);

        if ($connection->connect_error) {
            die("Connection failed: " . $connection->connect_error);
        }

        return $connection;
    }

}

?>