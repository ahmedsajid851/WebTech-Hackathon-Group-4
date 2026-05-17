<?php
// views/admin/product_create.php

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

// Fetch categories for dropdown
$categories = [];
$catResult = $connection->query("SELECT id, name, parent_id FROM categories ORDER BY name");
if($catResult){
    while($row = $catResult->fetch_assoc()){
        $categories[] = $row;
    }
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock_qty = intval($_POST['stock_qty'] ?? 0);
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    // Validation
    $errors = [];
    if(empty($name)){
        $errors[] = "Product name is required";
    }
    if($price <= 0){
        $errors[] = "Price must be greater than 0";
    }
    if($stock_qty < 0){
        $errors[] = "Stock quantity cannot be negative";
    }
    
    // Handle image upload
    $image_path = '';
    if(isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0){
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['product_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)){
            $upload_dir = __DIR__ . '/../../uploads/products/';
            if(!file_exists($upload_dir)){
                mkdir($upload_dir, 0777, true);
            }
            $new_filename = time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $filename);
            $upload_path = $upload_dir . $new_filename;
            
            if(move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)){
                $image_path = 'uploads/products/' . $new_filename;
            } else {
                $errors[] = "Failed to upload image";
            }
        } else {
            $errors[] = "Invalid file type. Allowed: jpg, jpeg, png, gif";
        }
    }
    
    if(empty($errors)){
        $sql = "INSERT INTO products (name, description, price, stock_qty, category_id, primary_image_path, is_available, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ssdiisi", $name, $description, $price, $stock_qty, $category_id, $image_path, $is_available);
        
        if($stmt->execute()){
            $success = "Product created successfully!";
            // Clear form
            $_POST = [];
        } else {
            $error = "Failed to create product: " . $connection->error;
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

$database->closeConnection($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Product</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; margin-bottom: 20px; }
        .nav { margin-bottom: 20px; padding: 10px; background: #333; border-radius: 5px; }
        .nav a { color: white; text-decoration: none; margin-right: 15px; padding: 5px 10px; }
        .nav a:hover { background: #555; border-radius: 3px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"], textarea, select {
            width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;
        }
        textarea { resize: vertical; min-height: 100px; }
        input[type="file"] { padding: 5px; }
        .checkbox-group { display: flex; align-items: center; gap: 10px; }
        .checkbox-group input { width: auto; }
        .btn-submit { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-submit:hover { background: #218838; }
        .btn-cancel { background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin-left: 10px; }
        .btn-cancel:hover { background: #5a6268; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .image-preview { margin-top: 10px; max-width: 200px; }
        .image-preview img { width: 100%; border-radius: 4px; }
    </style>
    <script>
        function previewImage() {
            var file = document.getElementById("product_image").files[0];
            var preview = document.getElementById("image_preview");
            
            if(file){
                var reader = new FileReader();
                reader.onload = function(e){
                    preview.innerHTML = '<img src="' + e.target.result + '" style="width: 150px; border-radius: 4px;">';
                }
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '';
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Create New Product</h1>
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
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Price *</label>
                <input type="number" step="0.01" name="price" value="<?php echo $_POST['price'] ?? ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Stock Quantity</label>
                <input type="number" name="stock_qty" value="<?php echo $_POST['stock_qty'] ?? 0; ?>">
            </div>
            
            <div class="form-group">
                <label>Category</label>
                <select name="category_id">
                    <option value="">-- Select Category --</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo str_repeat('--', $cat['parent_id'] ? 1 : 0); ?>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Product Image</label>
                <input type="file" name="product_image" id="product_image" accept="image/*" onchange="previewImage()">
                <div id="image_preview" class="image-preview"></div>
            </div>
            
            <div class="form-group checkbox-group">
                <input type="checkbox" name="is_available" id="is_available" <?php echo isset($_POST['is_available']) ? 'checked' : 'checked'; ?>>
                <label for="is_available">Available for sale</label>
            </div>
            
            <div>
                <button type="submit" class="btn-submit">Create Product</button>
                <a href="products.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>