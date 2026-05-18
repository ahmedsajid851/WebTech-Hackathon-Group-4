<?php
// views/admin/categories_delete.php - With confirmation page

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: categories.php");
    exit();
}

$category_id = intval($_GET['id']);
$confirm = isset($_GET['confirm']) ? $_GET['confirm'] : '';

require_once __DIR__ . '/../../config/db.php';

$database = new Database();
$connection = $database->openConnection();

// Get category name
$cat_sql = "SELECT name FROM categories WHERE id = ?";
$cat_stmt = $connection->prepare($cat_sql);
$cat_stmt->bind_param("i", $category_id);
$cat_stmt->execute();
$cat_result = $cat_stmt->get_result();
$category = $cat_result->fetch_assoc();

if(!$category){
    $database->closeConnection($connection);
    header("Location: categories.php");
    exit();
}

// If confirmed, delete
if($confirm == 'yes'){
    // Check if category has child categories
    $child_sql = "SELECT id FROM categories WHERE parent_id = ? LIMIT 1";
    $child_stmt = $connection->prepare($child_sql);
    $child_stmt->bind_param("i", $category_id);
    $child_stmt->execute();
    $child_result = $child_stmt->get_result();
    
    if($child_result->num_rows > 0){
        $database->closeConnection($connection);
        header("Location: categories.php?msg=error");
        exit();
    }
    
    // Check if category has products
    $product_sql = "SELECT id FROM products WHERE category_id = ? LIMIT 1";
    $product_stmt = $connection->prepare($product_sql);
    $product_stmt->bind_param("i", $category_id);
    $product_stmt->execute();
    $product_result = $product_stmt->get_result();
    
    if($product_result->num_rows > 0){
        $database->closeConnection($connection);
        header("Location: categories.php?msg=error");
        exit();
    }
    
    // Delete category
    $delete_sql = "DELETE FROM categories WHERE id = ?";
    $delete_stmt = $connection->prepare($delete_sql);
    $delete_stmt->bind_param("i", $category_id);
    
    if($delete_stmt->execute()){
        header("Location: categories.php?msg=deleted");
    } else {
        header("Location: categories.php?msg=error");
    }
    
    $database->closeConnection($connection);
    exit();
}

$database->closeConnection($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Category</title>
    <style>
        /* Beginner CSS - Simple and Clean */
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 500px;
            margin: 50px auto;
            background-color: white;
            border: 1px solid #ddd;
            padding: 30px;
            text-align: center;
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        .warning {
            color: #856404;
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #ffeeba;
        }
        
        .category-name {
            font-weight: bold;
            color: #d9534f;
            font-size: 18px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px;
            cursor: pointer;
            border: none;
            font-size: 14px;
        }
        
        .btn-danger {
            background-color: #d9534f;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c9302c;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Confirm Delete</h1>
    
    <div class="warning">
        <p>Are you sure you want to delete this category?</p>
        <p class="category-name">"<?php echo htmlspecialchars($category['name']); ?>"</p>
        <p style="font-size: 12px; margin-top: 10px;">This action cannot be undone.</p>
    </div>
    
    <a href="categories_delete.php?id=<?php echo $category_id; ?>&confirm=yes" class="btn btn-danger">Yes, Delete</a>
    <a href="categories.php" class="btn btn-secondary">Cancel</a>
</div>

</body>
</html>