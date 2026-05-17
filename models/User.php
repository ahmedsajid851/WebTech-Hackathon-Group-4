<?php
// models/User.php

class User {
    private $conn;
    private $table_name = "users";
    
    public function __construct($db = null){
        if($db === null){
            $database = new Database();
            $this->conn = $database->openConnection();
        } else {
            $this->conn = $db;
        }
    }
    
    // Authenticate user
    public function authenticate($email, $password){
        $sql = "SELECT * FROM " . $this->table_name . " WHERE email = ? LIMIT 1";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("s", $email);
        $statement->execute();
        
        $result = $statement->get_result();
        
        if($result->num_rows === 1){
            $user = $result->fetch_assoc();
            
            // Verify password
            if(password_verify($password, $user['password_hash'])){
                return $user;
            }
        }
        
        return false;
    }
    
    // Check if email exists
    public function emailExists($email){
        $sql = "SELECT id FROM " . $this->table_name . " WHERE email = ? LIMIT 1";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("s", $email);
        $statement->execute();
        
        $result = $statement->get_result();
        
        return $result->num_rows > 0;
    }
    
    // Create new user
    public function createUser($name, $email, $password_hash, $phone = null){
        $sql = "INSERT INTO " . $this->table_name . " 
                (name, email, password_hash, phone, role, created_at) 
                VALUES (?, ?, ?, ?, 'customer', NOW())";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("ssss", $name, $email, $password_hash, $phone);
        
        if($statement->execute()){
            return $this->conn->insert_id;
        }
        
        return false;
    }
    
    // Get user by ID
    public function getUserById($user_id){
        $sql = "SELECT id, name, email, phone, role, shipping_addresses, created_at 
                FROM " . $this->table_name . " 
                WHERE id = ? LIMIT 1";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("i", $user_id);
        $statement->execute();
        
        $result = $statement->get_result();
        
        if($result->num_rows === 1){
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    // Get user by remember token
    public function getUserByRememberToken($token){
        $sql = "SELECT * FROM " . $this->table_name . " WHERE remember_token = ? LIMIT 1";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("s", $token);
        $statement->execute();
        
        $result = $statement->get_result();
        
        if($result->num_rows === 1){
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    // Set remember me token
    public function setRememberToken($user_id, $token){
        $sql = "UPDATE " . $this->table_name . " SET remember_token = ? WHERE id = ?";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("si", $token, $user_id);
        
        return $statement->execute();
    }
    
    // Update user profile
    public function updateProfile($user_id, $name, $email, $phone, $shipping_addresses = null){
        $sql = "UPDATE " . $this->table_name . " 
                SET name = ?, email = ?, phone = ?, shipping_addresses = ? 
                WHERE id = ?";
        
        $statement = $this->conn->prepare($sql);
        
        $shipping_json = $shipping_addresses ? json_encode($shipping_addresses) : null;
        $statement->bind_param("ssssi", $name, $email, $phone, $shipping_json, $user_id);
        
        return $statement->execute();
    }
    
    // Update password
    public function updatePassword($user_id, $new_password_hash){
        $sql = "UPDATE " . $this->table_name . " SET password_hash = ? WHERE id = ?";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("si", $new_password_hash, $user_id);
        
        return $statement->execute();
    }
    
    // Close connection
    public function closeConnection(){
        if($this->conn){
            $this->conn->close();
        }
    }
}
?>