<?php
// views/admin/products.php

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
$products = $productModel->getAll();
$categories = $categoryModel->getCategoryTreeWithLevel();

$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'created') $message = "Product created.";
    elseif ($_GET['msg'] == 'updated') $message = "Product updated.";
    elseif ($_GET['msg'] == 'deleted') $message = "Product deleted.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Products</title>
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
        table{width:100%;background:white;border-collapse:collapse;}
        th,td{padding:10px;text-align:left;border-bottom:1px solid #ddd;}
        th{background:#f5f5f5;}
        .actions{display:flex;gap:8px;}
        .low-stock{background:#ffeb3b;}
        .badge{display:inline-block;padding:3px 8px;border-radius:3px;font-size:12px;}
        .badge-success{background:#5cb85c;color:white;}
        .badge-danger{background:#d9534f;color:white;}
        .toggle-btn{cursor:pointer;}
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
            <a href="products.php" class="active">Products</a>
            <a href="orders.php">Orders</a>
        </div>
        
        <div class="content">
            <div class="header">
                <h2>Products</h2>
                <a href="product_create.php" class="btn">Add Product</a>
            </div>
            
            <?php if($message): ?><div class="message"><?php echo $message; ?></div><?php endif; ?>
            
            <table>
                <thead>
                    <tr><th>ID</th><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Avg Rating</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if(empty($products)): ?>
                        <tr><td colspan="9">No products</td></tr>
                    <?php else: ?>
                        <?php foreach($products as $p): ?>
                            <tr class="<?php echo $p['stock_qty'] <= 5 ? 'low-stock' : ''; ?>">
                                <td><?php echo $p['id']; ?></td>
                                <td>
                                    <?php if($p['primary_image_path']): ?>
                                        <img src="../../public/<?php echo $p['primary_image_path']; ?>" width="50" height="50" style="object-fit:cover;">
                                    <?php else: ?>
                                        No img
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($p['name']); ?></td>
                                <td><?php echo htmlspecialchars($p['category_name'] ?? '-'); ?></td>
                                <td>$<?php echo number_format($p['price'], 2); ?></td>
                                <td><?php echo $p['stock_qty']; ?></td>
                                <td><?php echo round($p['avg_rating'], 1); ?> / 5</td>
                                <td>
                                    <span class="badge <?php echo $p['is_available'] ? 'badge-success' : 'badge-danger-toggle'; ?>" data-id="<?php echo $p['id']; ?>" style="cursor:pointer;">
                                        <?php echo $p['is_available'] ? 'In Stock' : 'Out of Stock'; ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="product_edit.php?id=<?php echo $p['id']; ?>" class="btn btn-edit" style="padding:4px 10px;">Edit</a>
                                    <a href="product_delete.php?id=<?php echo $p['id']; ?>" class="btn btn-danger" style="padding:4px 10px;" onclick="return confirm('Delete this product?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        document.querySelectorAll('.badge-success, .badge-danger-toggle').forEach(badge => {
            badge.addEventListener('click', async function() {
                const id = this.dataset.id;
                const response = await fetch('/WebTech/WebTech-Hackathon-Group-4/api/products/toggle.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: id})
                });
                const data = await response.json();
                if(data.success) {
                    this.textContent = data.is_available ? 'In Stock' : 'Out of Stock';
                    this.className = 'badge ' + (data.is_available ? 'badge-success' : 'badge-danger');
                } else {
                    alert('Failed to update status');
                }
            });
        });
    </script>
</body>
</html>