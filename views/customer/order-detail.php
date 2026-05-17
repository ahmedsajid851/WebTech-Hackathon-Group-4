<?php
// views/customer/order-detail.php
require_once '../../config/helpers.php';
require_login();

$order = $order ?? null;
$items = $items ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1, h2 { color: #333; margin-bottom: 20px; }
        .nav { margin-bottom: 20px; padding: 10px; background: #333; border-radius: 5px; }
        .nav a { color: white; text-decoration: none; margin-right: 15px; padding: 5px 10px; }
        .nav a:hover { background: #555; border-radius: 3px; }
        .order-info { background: #f5f5f5; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .product-item { border-bottom: 1px solid #ddd; padding: 15px; display: flex; align-items: center; }
        .product-image { width: 80px; height: 80px; object-fit: cover; margin-right: 15px; }
        .product-details { flex: 1; }
        .review-section { margin-top: 15px; padding: 15px; background: #f9f9f9; border-radius: 5px; }
        .rating { margin: 10px 0; }
        .rating input { margin-right: 5px; }
        .rating label { margin-right: 15px; cursor: pointer; font-size: 20px; }
        textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; margin: 10px 0; }
        button { background: #28a745; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 3px; }
        button:hover { background: #218838; }
        .message { padding: 10px; margin: 10px 0; border-radius: 3px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .back-link { display: inline-block; margin-top: 20px; color: #007bff; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
    <script>
        function submitReview(productId){
            var rating = document.querySelector('input[name="rating_' + productId + '"]:checked');
            var reviewText = document.getElementById("review_text_" + productId).value;
            
            if(!rating){
                alert("Please select a rating");
                return;
            }
            
            if(reviewText.trim() === ""){
                alert("Please enter your review");
                return;
            }
            
            var formData = new FormData();
            formData.append("product_id", productId);
            formData.append("rating", rating.value);
            formData.append("review_text", reviewText);
            
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function(){
                if(this.readyState == 4){
                    var response = JSON.parse(this.responseText);
                    if(this.status == 200 && response.ok){
                        alert("Review submitted successfully!");
                        location.reload();
                    } else if(response.error){
                        alert(response.error);
                    }
                }
            };
            xhttp.open("POST", "../../api/reviews.php", true);
            xhttp.send(formData);
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Order Details</h1>
        <div class="nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="my-orders.php">My Orders</a>
            <a href="../../controllers/AuthController.php?action=logout">Logout</a>
        </div>
        
        <?php if(isset($_SESSION['review_success'])): ?>
            <div class="message success"><?php echo $_SESSION['review_success']; unset($_SESSION['review_success']); ?></div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['review_error'])): ?>
            <div class="message error"><?php echo $_SESSION['review_error']; unset($_SESSION['review_error']); ?></div>
        <?php endif; ?>
        
        <?php if($order): ?>
            <div class="order-info">
                <p><strong>Order #:</strong> <?php echo $order['id']; ?></p>
                <p><strong>Order Date:</strong> <?php echo $order['created_at']; ?></p>
                <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                <p><strong>Status:</strong> <?php echo $order['status']; ?></p>
            </div>
            
            <h2>Items</h2>
            <?php foreach($items as $item): ?>
                <div class="product-item">
                    <img src="../../uploads/products/<?php echo $item['primary_image_path']; ?>" 
                         alt="<?php echo $item['name']; ?>" 
                         class="product-image"
                         onerror="this.src='../../uploads/default.jpg'">
                    <div class="product-details">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p>Quantity: <?php echo $item['quantity']; ?></p>
                        <p>Price: $<?php echo number_format($item['unit_price'], 2); ?></p>
                        <p>Subtotal: $<?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></p>
                        
                        <?php if($order['status'] == 'Delivered'): ?>
                            <div class="review-section">
                                <h4>Leave a Review</h4>
                                <div class="rating">
                                    <input type="radio" name="rating_<?php echo $item['product_id']; ?>" value="1" id="star1_<?php echo $item['product_id']; ?>">
                                    <label for="star1_<?php echo $item['product_id']; ?>">★</label>
                                    <input type="radio" name="rating_<?php echo $item['product_id']; ?>" value="2" id="star2_<?php echo $item['product_id']; ?>">
                                    <label for="star2_<?php echo $item['product_id']; ?>">★★</label>
                                    <input type="radio" name="rating_<?php echo $item['product_id']; ?>" value="3" id="star3_<?php echo $item['product_id']; ?>">
                                    <label for="star3_<?php echo $item['product_id']; ?>">★★★</label>
                                    <input type="radio" name="rating_<?php echo $item['product_id']; ?>" value="4" id="star4_<?php echo $item['product_id']; ?>">
                                    <label for="star4_<?php echo $item['product_id']; ?>">★★★★</label>
                                    <input type="radio" name="rating_<?php echo $item['product_id']; ?>" value="5" id="star5_<?php echo $item['product_id']; ?>">
                                    <label for="star5_<?php echo $item['product_id']; ?>">★★★★★</label>
                                </div>
                                <textarea id="review_text_<?php echo $item['product_id']; ?>" rows="3" placeholder="Write your review here..."></textarea>
                                <button onclick="submitReview(<?php echo $item['product_id']; ?>)">Submit Review</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Order not found.</p>
        <?php endif; ?>
        
        <a href="my-orders.php" class="back-link">← Back to My Orders</a>
    </div>
</body>
</html>