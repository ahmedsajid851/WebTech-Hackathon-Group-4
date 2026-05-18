<?php
// models/User.php

require_once __DIR__ . '/../config/db.php';

class User {
    private $conn;
    private $table_name = "users";
    
    // Constructor - accepts optional database connection
    public function __construct($connection = null){
        if($connection === null){
            $database = new Database();
            $this->conn = $database->openConnection();
        } else {
            $this->conn = $connection;
        }
    }
    
    // ========== AUTHENTICATION METHODS ==========
    
    // Authenticate user during login
    public function authenticate($email, $password){
        $sql = "SELECT * FROM " . $this->table_name . " WHERE email = ? LIMIT 1";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("s", $email);
        $statement->execute();
        
        $result = $statement->get_result();
        
        if($result->num_rows === 1){
            $user = $result->fetch_assoc();
            if(password_verify($password, $user['password_hash'])){
                return $user;
            }
        }
        return false;
    }
    
    // Check if email already exists in database
    public function emailExists($email){
        $sql = "SELECT id FROM " . $this->table_name . " WHERE email = ? LIMIT 1";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("s", $email);
        $statement->execute();
        
        $result = $statement->get_result();
        return $result->num_rows > 0;
    }
    
    // Create new user account
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
    
    // ========== USER DATA RETRIEVAL METHODS ==========
    
    // Get user by ID - IMPORTANT FOR PASSWORD CHANGE
    public function getUserById($user_id){
        $sql = "SELECT id, name, email, phone, role, shipping_addresses, password_hash, created_at 
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
    
    // Get user by remember me token
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
    
    // ========== PROFILE UPDATE METHODS ==========
    
    // Update user profile (name, email, phone)
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
    
    // Set remember me token
    public function setRememberToken($user_id, $token){
        $sql = "UPDATE " . $this->table_name . " SET remember_token = ? WHERE id = ?";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("si", $token, $user_id);
        
        return $statement->execute();
    }
    
    // ========== ADDRESS MANAGEMENT METHODS ==========
    
    // Get shipping addresses as array
    public function getShippingAddresses($user_id){
        $user = $this->getUserById($user_id);
        if($user && !empty($user['shipping_addresses'])){
            return json_decode($user['shipping_addresses'], true);
        }
        return [];
    }
    
    // Add new shipping address
    public function addShippingAddress($user_id, $address){
        $addresses = $this->getShippingAddresses($user_id);
        
        // Maximum 2 addresses allowed
        if(count($addresses) >= 2){
            return false;
        }
        
        $addresses[] = $address;
        $encoded = json_encode($addresses);
        
        $sql = "UPDATE " . $this->table_name . " SET shipping_addresses = ? WHERE id = ?";
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("si", $encoded, $user_id);
        
        return $statement->execute();
    }
    
    // Remove shipping address by index
    public function removeShippingAddress($user_id, $index){
        $addresses = $this->getShippingAddresses($user_id);
        
        if(isset($addresses[$index])){
            array_splice($addresses, $index, 1);
        }
        
        $encoded = json_encode($addresses);
        
        $sql = "UPDATE " . $this->table_name . " SET shipping_addresses = ? WHERE id = ?";
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("si", $encoded, $user_id);
        
        return $statement->execute();
    }
    
    // ========== CLOSE CONNECTION ==========
    
    public function closeConnection(){
        if($this->conn){
            $this->conn->close();
        }
    }
}
?>