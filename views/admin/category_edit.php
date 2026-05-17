<?php
// views/admin/category_edit.php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Category.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

$categoryModel = new Category($pdo);
$id = $_GET['id'] ?? null;

if(!$id){
    header("Location: categories.php");
    exit();
}

$category = $categoryModel->getById($id);
if(!$category){
    header("Location: categories.php");
    exit();
}

$parentCategories = $categoryModel->getParentCategories($id);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
    
    if (empty($name)) {
        $error = 'Category name required';
    } else {
        if ($categoryModel->update($id, $name, $parent_id)) {
            header("Location: categories.php?msg=updated");
            exit();
        } else {
            $error = 'Failed to update';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Category</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:Arial;background:#f0f0f0;}
        .top-nav{background:#1a1a1a;color:white;padding:15px 20px;position:sticky;top:0;display:flex;justify-content:space-between;}
        .logout-btn{background:#d9534f;color:white;padding:5px 12px;text-decoration:none;border-radius:3px;}
        .sidebar{width:200px;background:#2c2c2c;position:sticky;top:52px;height:calc(100vh - 52px);}
        .sidebar a{color:#ddd;display:block;padding:12px 20px;text-decoration:none;border-bottom:1px solid #3a3a3a;}
        .sidebar a:hover{background:#3a3a3a;}
        .main-container{display:flex;}
        .content{flex:1;padding:20px;}
        .form-box{background:white;padding:20px;border:1px solid #ddd;max-width:500px;}
        .form-group{margin-bottom:15px;}
        label{display:block;margin-bottom:5px;font-weight:bold;}
        input,select{width:100%;padding:8px;border:1px solid #ddd;border-radius:3px;}
        .btn{padding:8px 15px;background:#007bff;color:white;border:none;cursor:pointer;border-radius:3px;}
        .btn-secondary{background:#6c757d;}
        .error{background:#f2dede;padding:10px;margin-bottom:20px;color:#a94442;}
        .buttons{display:flex;gap:10px;}
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
            <a href="categories.php">Categories</a>
            <a href="products.php">Products</a>
            <a href="orders.php">Orders</a>
        </div>
        
        <div class="content">
            <h2 style="margin-bottom:20px;">Edit Category</h2>
            
            <?php if($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
            
            <div class="form-box">
                <form method="POST">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Parent Category</label>
                        <select name="parent_id">
                            <option value="">None (Top Level)</option>
                            <?php foreach($parentCategories as $parent): ?>
                                <option value="<?php echo $parent['id']; ?>" <?php echo $category['parent_id'] == $parent['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($parent['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="buttons">
                        <button type="submit" class="btn">Update</button>
                        <a href="categories.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>