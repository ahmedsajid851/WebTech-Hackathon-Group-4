<?php
// views/admin/product_edit.php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../models/Category.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

$database = new DatabaseConnection();
$connection = $database->openConnection();

$productModel = new Product($connection);
$categoryModel = new Category($connection);
$id = $_GET['id'] ?? null;

if(!$id){
    header("Location: products.php");
    exit();
}

$product = $productModel->getById($id);
if(!$product){
    header("Location: products.php");
    exit();
}

$categories = $categoryModel->getCategoryTreeWithLevel();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock_qty = intval($_POST['stock_qty'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);
    
    if(empty($name)) {
        $error = 'Product name required';
    } elseif($price <= 0) {
        $error = 'Price must be positive';
    } elseif($stock_qty < 0) {
        $error = 'Stock quantity cannot be negative';
    } elseif($category_id <= 0) {
        $error = 'Category required';
    } else {
        $data = [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock_qty' => $stock_qty,
            'category_id' => $category_id
        ];
        
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if(in_array($ext, $allowed)) {
                if($_FILES['image']['size'] <= 3 * 1024 * 1024) {
                    $newName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
                    $uploadPath = 'uploads/products/' . $newName;
                    $fullPath = __DIR__ . '/../../public/' . $uploadPath;
                    
                    if(!is_dir(dirname($fullPath))) {
                        mkdir(dirname($fullPath), 0777, true);
                    }
                    
                    if(move_uploaded_file($_FILES['image']['tmp_name'], $fullPath)) {
                        $data['primary_image_path'] = $uploadPath;
                    } else {
                        $error = 'Failed to upload image';
                    }
                } else {
                    $error = 'Image too large (max 3MB)';
                }
            } else {
                $error = 'Only JPG, JPEG, PNG allowed';
            }
        }
        
        if(empty($error)) {
            $result = $productModel->update($id, $data);
            if($result) {
                header("Location: products.php?msg=updated");
                exit();
            } else {
                $error = 'Failed to update product';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
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
        .form-box{background:white;padding:20px;border:1px solid #ddd;max-width:600px;}
        .form-group{margin-bottom:15px;}
        label{display:block;margin-bottom:5px;font-weight:bold;}
        input,select,textarea{width:100%;padding:8px;border:1px solid #ddd;border-radius:3px;}
        textarea{height:100px;}
        .btn{padding:8px 15px;background:#007bff;color:white;border:none;cursor:pointer;border-radius:3px;}
        .btn-secondary{background:#6c757d;}
        .error{background:#f2dede;padding:10px;margin-bottom:20px;color:#a94442;}
        .buttons{display:flex;gap:10px;}
        .current-image{margin-bottom:15px;}
        .current-image img{max-width:150px;border:1px solid #ddd;padding:5px;}
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
            <h2 style="margin-bottom:20px;">Edit Product</h2>
            
            <?php if($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
            
            <div class="form-box">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Price</label>
                        <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Stock Quantity</label>
                        <input type="number" name="stock_qty" value="<?php echo $product['stock_qty']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $product['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo str_repeat('--', $cat['level']) . ' ' . htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Product Image (optional)</label>
                        <?php if($product['primary_image_path']): ?>
                            <div class="current-image">
                                <img src="/WebTech/WebTech-Hackathon-Group-4/<?php echo $product['primary_image_path']; ?>">
                                <p style="font-size:12px;color:#666;">Current image</p>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" accept="image/jpeg,image/png">
                        <p style="font-size:12px;color:#666;">Leave empty to keep current image. Max 3MB.</p>
                    </div>
                    <div class="buttons">
                        <button type="submit" class="btn">Update</button>
                        <a href="products.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>