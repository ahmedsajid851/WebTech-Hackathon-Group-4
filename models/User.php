<?php
require_once __DIR__ . '/../config/db.php';

class User {
    /**
     * @var DatabaseConnection $db
     */
    private $db;
    
    public function __construct() {
        $this->db = new DatabaseConnection();
    }
    
    /**
     * Register new user
     * @param string $name
     * @param string $email
     * @param string $password
     * @param string|null $phone
     * @return array<string,mixed>
     */
    public function register($name, $email, $password, $phone = null) {
        // Check if email already exists
        $existingUser = $this->findByEmail($email);
        if ($existingUser && $existingUser->num_rows > 0) {
            return ["success" => false, "message" => "Email already registered"];
        }
        
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare data for insertion
        $data = [
            'name' => $name,
            'email' => $email,
            'password_hash' => $password_hash,
            'role' => 'customer'
        ];
        
        // Add phone if provided
        if ($phone && !empty($phone)) {
            $data['phone'] = $phone;
        }
        
        // Insert user
        $result = $this->db->insert('users', $data);
        
        if ($result) {
            return ["success" => true, "message" => "Registration successful"];
        } else {
            return ["success" => false, "message" => "Registration failed. Please try again."];
        }
    }
    
    /**
     * Login user
     * @param string $email
     * @param string $password
     * @return array<string,mixed>
     */
    public function login($email, $password) {
        $result = $this->findByEmail($email);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password_hash'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['isLoggedIn'] = true;
                
                return ["success" => true, "message" => "Login successful", "role" => $user['role']];
            } else {
                return ["success" => false, "message" => "Invalid password"];
            }
        } else {
            return ["success" => false, "message" => "Email not found"];
        }
    }
    
    /**
     * Find user by email
     * @param string $email
     * @return mysqli_result|bool
     */
    public function findByEmail($email) {
        return $this->db->select('users', ['email' => $email]);
    }
    
    /**
     * Find user by ID
     * @param int $id
     * @return mysqli_result|bool
     */
    public function findById($id) {
        return $this->db->select('users', ['id' => $id]);
    }
    
    /**
     * Get all users
     * @return mysqli_result|bool
     */
    public function getAllUsers() {
        $connection = $this->db->openConnection();
        $sql = "SELECT id, name, email, phone, role, created_at FROM users ORDER BY created_at DESC";
        $result = $connection->query($sql);
        $this->db->closeConnection();
        return $result;
    }
    
    /**
     * Update user profile
     * @param int $userId
     * @param string $name
     * @param string|null $phone
     * @return mysqli_result|bool
     */
    public function updateProfile($userId, $name, $phone) {
        return $this->db->update('users', 
            ['name' => $name, 'phone' => $phone], 
            ['id' => $userId]
        );
    }
    
    /**
     * Update user role (admin only)
     * @param int $userId
     * @param string $role
     * @return mysqli_result|bool
     */
    public function updateRole($userId, $role) {
        return $this->db->update('users', ['role' => $role], ['id' => $userId]);
    }
    
    /**
     * Delete user
     * @param int $userId
     * @return mysqli_result|bool
     */
    public function deleteUser($userId) {
        return $this->db->delete('users', ['id' => $userId]);
    }
    
    /**
     * Logout
     * @return array<string,mixed>
     */
    public function logout() {
        session_destroy();
        return ["success" => true, "message" => "Logged out successfully"];
    }
    
    /**
     * Change password
     * @param int $userId
     * @param string $oldPassword
     * @param string $newPassword
     * @return array<string,mixed>
     */
    public function changePassword($userId, $oldPassword, $newPassword) {
        // Get user by ID
        $result = $this->findById($userId);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify old password
            if (password_verify($oldPassword, $user['password_hash'])) {
                // Hash new password
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                
                // Update password in database
                $updateResult = $this->db->update('users', ['password_hash' => $newHash], ['id' => $userId]);
                
                if ($updateResult) {
                    return ["success" => true, "message" => "Password changed successfully"];
                } else {
                    return ["success" => false, "message" => "Failed to update password"];
                }
            } else {
                return ["success" => false, "message" => "Current password is incorrect"];
            }
        } else {
            return ["success" => false, "message" => "User not found"];
        }
    }
}
?>