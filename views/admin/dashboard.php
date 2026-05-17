<?php 
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../models/User.php';

// Check if user is logged in and is admin
if(!isLoggedIn()){
    redirect("views/auth/login.php");
    exit();
}

if(!isAdmin()){
    redirect("views/dashboard.php");
    exit();
}

$userModel = new User();
$allUsers = $userModel->getAllUsers();
$adminName = $_SESSION["user_name"] ?? "";
$adminEmail = $_SESSION["user_email"] ?? "";

// Get users
$recentUsers = [];
$totalUsers = 0;
if($allUsers && $allUsers->num_rows > 0){
    $totalUsers = $allUsers->num_rows;
    while($row = $allUsers->fetch_assoc()){
        $recentUsers[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        
        .header {
            background: #2c3e50;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 20px;
            font-weight: bold;
        }
        
        .nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            padding: 5px 10px;
            cursor: pointer;
        }
        
        .nav a:hover {
            background: #34495e;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .welcome-box {
            background: white;
            padding: 25px;
            border: 1px solid #ddd;
            margin-bottom: 25px;
            border-radius: 5px;
        }
        
        .welcome-box h2 {
            margin-bottom: 10px;
            color: #333;
        }
        
        .welcome-box p {
            margin: 5px 0;
            color: #666;
        }
        
        .card {
            background: white;
            border: 1px solid #ddd;
            margin-bottom: 25px;
            border-radius: 5px;
        }
        
        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            background: #f9f9f9;
        }
        
        .card-header h3 {
            color: #333;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .stat {
            background: white;
            padding: 20px;
            border: 1px solid #ddd;
            flex: 1;
            min-width: 150px;
            text-align: center;
            border-radius: 5px;
        }
        
        .stat .number {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .stat .label {
            color: #666;
            margin-top: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f5f5f5;
            font-weight: bold;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 12px;
            border-radius: 3px;
        }
        
        .badge-admin {
            background: #e74c3c;
            color: white;
        }
        
        .badge-customer {
            background: #27ae60;
            color: white;
        }
        
        .quick-links {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .quick-link {
            background: #ecf0f1;
            padding: 8px 15px;
            text-decoration: none;
            color: #333;
            border-radius: 3px;
        }
        
        .quick-link:hover {
            background: #3498db;
            color: white;
        }
        
        .logout-btn {
            background: #e74c3c;
            padding: 5px 15px;
            border-radius: 3px;
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
    </style>
    <script>
        // Function to logout across all tabs
        function logoutUser() {
            localStorage.setItem('logout', 'true');
            localStorage.removeItem('logout');
            window.location.href = '../../controllers/AuthController.php?action=logout';
        }
        
        // Listen for logout events from other tabs
        window.addEventListener('storage', function(event) {
            if (event.key === 'logout' && event.newValue === 'true') {
                window.location.href = '../../views/auth/login.php';
            }
        });
        
        // Check session every 5 seconds
        setInterval(function() {
            fetch('../../api/auth.php?action=checkSession')
                .then(response => response.json())
                .then(data => {
                    if (!data.loggedIn) {
                        window.location.href = '../../views/auth/login.php';
                    }
                })
                .catch(() => {});
        }, 5000);
    </script>
</head>
<body>
    <div class="header">
        <div class="logo"><?php echo SITE_NAME; ?> - Admin Panel</div>
        <div class="nav">
            <a href="#">Dashboard</a>
            <a href="#">Orders</a>
            <a href="javascript:void(0)" onclick="logoutUser()" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome-box">
            <h2>Welcome, Admin <?php echo htmlspecialchars($adminName); ?>!</h2>
            <p>Email: <?php echo htmlspecialchars($adminEmail); ?></p>
            <p>✓ You are logged in as <strong>Administrator</strong></p>
        </div>
        
        <div class="stats">
            <div class="stat">
                <div class="number"><?php echo $totalUsers; ?></div>
                <div class="label">Total Users</div>
            </div>
            <div class="stat">
                <div class="number">0</div>
                <div class="label">Total Orders</div>
            </div>
            <div class="stat">
                <div class="number">0</div>
                <div class="label">Products</div>
            </div>
            <div class="stat">
                <div class="number">0</div>
                <div class="label">Categories</div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3>Admin Dashboard</h3>
            </div>
            <div class="card-body">
                <p style="margin-bottom: 15px;">✓ Login successful! You can now manage:</p>
                <div class="quick-links">
                    <a href="#" class="quick-link">📦 Orders</a>
                    <a href="#" class="quick-link">🏷️ Categories</a>
                    <a href="#" class="quick-link">📦 Products</a>
                    <a href="#" class="quick-link">👥 Users</a>
                    <a href="#" class="quick-link">⭐ Reviews</a>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3>Users List</h3>
            </div>
            <div class="card-body">
                <?php if(count($recentUsers) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recentUsers as $row): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></dt>
                                <td><?php echo htmlspecialchars($row['name']); ?></dt>
                                <td><?php echo htmlspecialchars($row['email']); ?></dt>
                                <td>
                                    <span class="badge <?php echo $row['role'] === 'admin' ? 'badge-admin' : 'badge-customer'; ?>">
                                        <?php echo ucfirst($row['role']); ?>
                                    </span>
                                 </dt>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No users found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>