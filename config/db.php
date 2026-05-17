<?php
class DatabaseConnection {
    private $host = "localhost";
    private $user = "root";
    private $password = "";  // Empty for XAMPP
    private $dbname = "ecommerce_lab";
    private $connection;

    function openConnection() {
        // Disable automatic error reporting to handle manually
        mysqli_report(MYSQLI_REPORT_OFF);
        
        $this->connection = new mysqli($this->host, $this->user, $this->password, $this->dbname);
        
        // Check connection error
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
        
        // Set charset to UTF-8
        $this->connection->set_charset("utf8");
        
        return $this->connection;
    }

    function closeConnection() {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    // Execute query with prepared statement
    function executeQuery($sql, $types = "", $params = []) {
        $connection = $this->openConnection();
        $stmt = $connection->prepare($sql);
        
        if (!$stmt) {
            die("Prepare failed: " . $connection->error);
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        
        if ($stmt->error) {
            die("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $stmt->close();
        $this->closeConnection();
        
        return $result;
    }

    // Insert data with error checking
    function insert($tableName, $data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = "?" . str_repeat(", ?", count($data) - 1);
        $sql = "INSERT INTO " . $tableName . " (" . $columns . ") VALUES (" . $placeholders . ")";
        
        $types = "";
        $values = [];
        foreach ($data as $key => $value) {
            if (is_int($value)) {
                $types .= "i";
            } elseif (is_float($value)) {
                $types .= "d";
            } else {
                $types .= "s";
            }
            $values[] = $value;
        }
        
        $connection = $this->openConnection();
        $stmt = $connection->prepare($sql);
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param($types, ...$values);
        $result = $stmt->execute();
        
        $insertId = $stmt->insert_id;
        
        $stmt->close();
        $this->closeConnection();
        
        if ($result) {
            return $insertId;
        } else {
            return false;
        }
    }

    // Select data
    function select($tableName, $conditions = [], $limit = null) {
        $sql = "SELECT * FROM " . $tableName;
        $types = "";
        $params = [];
        
        if (!empty($conditions)) {
            $sql .= " WHERE ";
            $conditionParts = [];
            foreach ($conditions as $column => $value) {
                $conditionParts[] = $column . " = ?";
                $types .= is_int($value) ? "i" : "s";
                $params[] = $value;
            }
            $sql .= implode(" AND ", $conditionParts);
        }
        
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        return $this->executeQuery($sql, $types, $params);
    }

    // Update data
    function update($tableName, $data, $conditions) {
        $sql = "UPDATE " . $tableName . " SET ";
        $setParts = [];
        $types = "";
        $params = [];
        
        foreach ($data as $column => $value) {
            $setParts[] = $column . " = ?";
            $types .= is_int($value) ? "i" : "s";
            $params[] = $value;
        }
        $sql .= implode(", ", $setParts);
        
        $sql .= " WHERE ";
        $conditionParts = [];
        foreach ($conditions as $column => $value) {
            $conditionParts[] = $column . " = ?";
            $types .= is_int($value) ? "i" : "s";
            $params[] = $value;
        }
        $sql .= implode(" AND ", $conditionParts);
        
        return $this->executeQuery($sql, $types, $params);
    }

    // Delete data
    function delete($tableName, $conditions) {
        $sql = "DELETE FROM " . $tableName . " WHERE ";
        $types = "";
        $params = [];
        $conditionParts = [];
        
        foreach ($conditions as $column => $value) {
            $conditionParts[] = $column . " = ?";
            $types .= is_int($value) ? "i" : "s";
            $params[] = $value;
        }
        $sql .= implode(" AND ", $conditionParts);
        
        return $this->executeQuery($sql, $types, $params);
    }
}
?>