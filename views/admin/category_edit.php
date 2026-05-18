<?php
// views/admin/category_create.php

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

// ========== FIXED: Use Database class instead of DatabaseConnection ==========
require_once __DIR__ . '/../../config/db.php';

$database = new Database();
$connection = $database->openConnection();

// Fetch parent categories for dropdown
$parentCategories = [];
$sql = "SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY name";
$result = $connection->query($sql);
if($result){
    while($row = $result->fetch_assoc()){
        $parentCategories[] = $row;
    }
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['name'] ?? '');
    $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
    
    // Validation
    if(empty($name)){
        $error = "Category name is required";
    } else {
        // Check if category already exists
        $checkSql = "SELECT id FROM categories WHERE name = ?";
        $checkStmt = $connection->prepare($checkSql);
        $checkStmt->bind_param("s", $name);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if($checkResult->num_rows > 0){
            $error = "Category with this name already exists";
        } else {
            // Insert category
            if($parent_id){
                $insertSql = "INSERT INTO categories (name, parent_id) VALUES (?, ?)";
                $insertStmt = $connection->prepare($insertSql);
                $insertStmt->bind_param("si", $name, $parent_id);
            } else {
                $insertSql = "INSERT INTO categories (name) VALUES (?)";
                $insertStmt = $connection->prepare($insertSql);
                $insertStmt->bind_param("s", $name);
            }
            
            if($insertStmt->execute()){
                $success = "Category created successfully!";
                // Clear form
                $_POST = [];
            } else {
                $error = "Failed to create category: " . $connection->error;
            }
        }
    }
}

$database->closeConnection($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Category</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; margin-bottom: 20px; }
        .nav { margin-bottom: 20px; padding: 10px; background: #333; border-radius: 5px; }
        .nav a { color: white; text-decoration: none; margin-right: 15px; padding: 5px 10px; }
        .nav a:hover { background: #555; border-radius: 3px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], select {
            width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;
        }
        .btn-submit { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-submit:hover { background: #218838; }
        .btn-cancel { background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin-left: 10px; }
        .btn-cancel:hover { background: #5a6268; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create Category</h1>
        <div class="nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="categories.php">Categories</a>
            <a href="products.php">Products</a>
            <a href="orders.php">Orders</a>
            <a href="../../controllers/AuthController.php?action=logout">Logout</a>
        </div>
        
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Category Name *</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Parent Category (Optional)</label>
                <select name="parent_id">
                    <option value="">-- None (Top Level) --</option>
                    <?php foreach($parentCategories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_POST['parent_id']) && $_POST['parent_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <button type="submit" class="btn-submit">Create Category</button>
                <a href="categories.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>