<?php
// views/admin/categories.php

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

require_once __DIR__ . '/../../config/db.php';

$database = new Database();
$connection = $database->openConnection();

// Fetch all categories
$categories = [];
$sql = "SELECT * FROM categories ORDER BY parent_id, name";
$result = $connection->query($sql);
if($result){
    while($row = $result->fetch_assoc()){
        $categories[] = $row;
    }
}

// Function to display category hierarchy
function displayCategoryName($categories, $id) {
    foreach($categories as $cat){
        if($cat['id'] == $id){
            return $cat['name'];
        }
    }
    return 'None';
}

// Function to check if category has children
function hasChildren($categories, $parent_id) {
    foreach($categories as $cat){
        if($cat['parent_id'] == $parent_id){
            return true;
        }
    }
    return false;
}

$database->closeConnection($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Category Management</title>
    <style>
        /* Beginner CSS - Simple and Clean */
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            border: 1px solid #ddd;
            padding: 20px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        /* Navigation Links */
        .nav {
            background-color: #333;
            padding: 10px;
            margin-bottom: 20px;
        }
        
        .nav a {
            color: white;
            text-decoration: none;
            margin-right: 15px;
            padding: 5px 10px;
        }
        
        .nav a:hover {
            background-color: #555;
        }
        
        /* Add Button */
        .btn-add {
            background-color: #28a745;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .btn-add:hover {
            background-color: #218838;
        }
        
        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        
        th {
            background-color: #007bff;
            color: white;
        }
        
        tr:hover {
            background-color: #f5f5f5;
        }
        
        /* Action Buttons */
        .action-buttons a {
            display: inline-block;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 12px;
            margin-right: 5px;
        }
        
        .btn-edit {
            background-color: #007bff;
            color: white;
        }
        
        .btn-edit:hover {
            background-color: #0056b3;
        }
        
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #c82333;
        }
        
        .btn-disabled {
            background-color: #6c757d;
            color: white;
            cursor: not-allowed;
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
        }
        
        /* Messages */
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Admin - Category Management</h1>
    
    <div class="nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="categories.php">Categories</a>
        <a href="products.php">Products</a>
        <a href="orders.php">Orders</a>
        <a href="../../controllers/AuthController.php?action=logout">Logout</a>
    </div>
    
    <?php if(isset($_GET['msg'])): ?>
        <?php if($_GET['msg'] == 'created'): ?>
            <div class="message success">Category created successfully!</div>
        <?php elseif($_GET['msg'] == 'updated'): ?>
            <div class="message success">Category updated successfully!</div>
        <?php elseif($_GET['msg'] == 'deleted'): ?>
            <div class="message success">Category deleted successfully!</div>
        <?php elseif($_GET['msg'] == 'error'): ?>
            <div class="message error">Cannot delete category with child categories or products!</div>
        <?php endif; ?>
    <?php endif; ?>
    
    <a href="category_create.php" class="btn-add">+ Add New Category</a>
    
    <?php if(empty($categories)): ?>
        <p>No categories found. <a href="category_create.php">Add your first category</a></p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Parent Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($categories as $cat): ?>
                    <tr>
                        <td><?php echo $cat['id']; ?></td>
                        <td>
                            <?php 
                            if($cat['parent_id']){
                                echo '&nbsp;&nbsp;&nbsp;↳ ';
                            }
                            echo htmlspecialchars($cat['name']); 
                            ?>
                        </td>
                        <td><?php echo displayCategoryName($categories, $cat['parent_id']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="category_edit.php?id=<?php echo $cat['id']; ?>" class="btn-edit">Edit</a>
                                <?php if(!hasChildren($categories, $cat['id'])): ?>
                                    <a href="categories_delete.php?id=<?php echo $cat['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                                <?php else: ?>
                                    <span class="btn-disabled" title="Cannot delete - has child categories">Delete</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>