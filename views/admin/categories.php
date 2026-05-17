<?php
// views/admin/categories.php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Category.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

$categoryModel = new Category($pdo);
$categories = $categoryModel->getCategoryTree();

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    if ($categoryModel->hasChildren($id)) {
        $error = "Cannot delete category. It has child categories.";
    } elseif ($categoryModel->hasProducts($id)) {
        $error = "Cannot delete category. It has products.";
    } else {
        if ($categoryModel->delete($id)) {
            header("Location: categories.php?msg=deleted");
            exit();
        } else {
            $error = "Failed to delete.";
        }
    }
}

$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'created') $message = "Category created.";
    elseif ($_GET['msg'] == 'updated') $message = "Category updated.";
    elseif ($_GET['msg'] == 'deleted') $message = "Category deleted.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Categories</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:Arial;background:#f0f0f0;}
        .top-nav{background:#1a1a1a;color:white;padding:15px 20px;position:sticky;top:0;display:flex;justify-content:space-between;}
        .logout-btn{background:#d9534f;color:white;padding:5px 12px;text-decoration:none;border-radius:3px;}
        .sidebar{width:200px;background:#2c2c2c;position:sticky;top:52px;height:calc(100vh - 52px);}
        .sidebar a{color:#ddd;display:block;padding:12px 20px;text-decoration:none;border-bottom:1px solid #3a3a3a;}
        .sidebar a:hover{background:#3a3a3a;}
        .sidebar a.active{background:#007bff;color:white;}
        .main-container{display:flex;}
        .content{flex:1;padding:20px;}
        .header{display:flex;justify-content:space-between;margin-bottom:20px;}
        .btn{display:inline-block;padding:8px 15px;background:#007bff;color:white;text-decoration:none;border-radius:3px;}
        .btn-danger{background:#d9534f;}
        .btn-edit{background:#5bc0de;}
        .message{background:#d9edf7;padding:10px;margin-bottom:20px;}
        .error{background:#f2dede;padding:10px;margin-bottom:20px;color:#a94442;}
        table{width:100%;background:white;border-collapse:collapse;}
        th,td{padding:10px;text-align:left;border-bottom:1px solid #ddd;}
        th{background:#f5f5f5;}
        .actions{display:flex;gap:8px;}
    </style>
</head>
<body>
    <div class="top-nav">
        <h2>Admin Panel</h2>
        <div>
            <span><?php echo $_SESSION['name'] ?? 'Guest'; ?></span>
            <a href="../../views/auth/logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="main-container">
        <div class="sidebar">
            <a href="dashboard.php">Dashboard</a>
            <a href="categories.php" class="active">Categories</a>
            <a href="products.php">Products</a>
            <a href="orders.php">Orders</a>
        </div>
        
        <div class="content">
            <div class="header">
                <h2>Categories</h2>
                <a href="category_create.php" class="btn">Add Category</a>
            </div>
            
            <?php if($message): ?><div class="message"><?php echo $message; ?></div><?php endif; ?>
            <?php if(isset($error)): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
            
            <table>
                <thead><tr><th>ID</th><th>Name</th><th>Parent</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php if(empty($categories)): ?>
                        <tr><td colspan="4">No categories</td></tr>
                    <?php else: ?>
                        <?php foreach($categories as $cat): ?>
                            <tr>
                                <td><?php echo $cat['id']; ?></td>
                                <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                <td>
                                    <?php 
                                    if($cat['parent_id']){
                                        $parent = $categoryModel->getById($cat['parent_id']);
                                        echo $parent['name'];
                                    } else { echo '-'; }
                                    ?>
                                </td>
                                <td class="actions">
                                    <a href="category_edit.php?id=<?php echo $cat['id']; ?>" class="btn btn-edit" style="padding:4px 10px;">Edit</a>
                                    <a href="?delete=<?php echo $cat['id']; ?>" class="btn btn-danger" style="padding:4px 10px;" onclick="return confirm('Delete?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>