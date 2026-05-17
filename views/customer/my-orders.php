<?php
// views/customer/my-orders.php
session_start();

// Check if logged in
if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../config/db.php';
require_once '../../models/Order.php';

$database = new Database();
$connection = $database->openConnection();
$orderModel = new Order($connection);

$user_id = $_SESSION['user_id'];
$orders = $orderModel->getUserOrders($user_id);

$database->closeConnection($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; margin-bottom: 20px; }
        .nav { margin-bottom: 20px; padding: 10px; background: #333; border-radius: 5px; }
        .nav a { color: white; text-decoration: none; margin-right: 15px; padding: 5px 10px; }
        .nav a:hover { background: #555; border-radius: 3px; }
        .order-card { border: 1px solid #ddd; margin: 15px 0; padding: 15px; border-radius: 5px; background: white; }
        .order-header { background: #f5f5f5; padding: 10px; cursor: pointer; font-weight: bold; border-radius: 3px; }
        .order-header:hover { background: #e9ecef; }
        .order-details { display: none; padding: 15px; background: #fafafa; margin-top: 10px; border-radius: 3px; }
        .badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-warning { background: #ffc107; color: #000; }
        .badge-info { background: #17a2b8; color: white; }
        .badge-primary { background: #007bff; color: white; }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
        .product-item { border-bottom: 1px solid #eee; padding: 10px 0; }
        .product-item:last-child { border-bottom: none; }
        .review-section { margin-top: 15px; padding: 15px; background: #fff3cd; border-radius: 5px; border: 1px solid #ffc107; }
        .rating { margin: 10px 0; }
        .rating input { margin-right: 5px; }
        .rating label { margin-right: 15px; cursor: pointer; font-size: 20px; }
        textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; margin: 10px 0; font-family: Arial; }
        .submit-review { background: #28a745; color: white; border: none; padding: 8px 15px; cursor: pointer; border-radius: 3px; }
        .submit-review:hover { background: #218838; }
        .success-msg { background: #d4edda; color: #155724; padding: 10px; border-radius: 3px; margin-bottom: 10px; }
        .error-msg { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 3px; margin-bottom: 10px; }
        .total { font-weight: bold; margin-top: 10px; text-align: right; }
        .no-orders { text-align: center; padding: 40px; color: #666; }
    </style>
    <script>
        function toggleOrder(orderId){
            var details = document.getElementById("details-" + orderId);
            if(details.style.display === "none" || details.style.display === ""){
                details.style.display = "block";
                loadOrderDetails(orderId);
            } else {
                details.style.display = "none";
            }
        }
        
        function loadOrderDetails(orderId){
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function(){
                if(this.readyState == 4 && this.status == 200){
                    var order = JSON.parse(this.responseText);
                    displayOrderDetails(order);
                }
            };
            xhttp.open("GET", "../../api/orders.php?id=" + orderId, true);
            xhttp.send();
        }
        
        function displayOrderDetails(order){
            var detailsDiv = document.getElementById("details-" + order.id);
            var itemsHtml = '<h3>Order Items</h3>';
            
            if(order.items && order.items.length > 0){
                for(var i = 0; i < order.items.length; i++){
                    var item = order.items[i];
                    itemsHtml += `
                        <div class="product-item">
                            <strong>${item.name}</strong><br>
                            Quantity: ${item.quantity}<br>
                            Price: $${parseFloat(item.unit_price).toFixed(2)}<br>
                            Subtotal: $${(item.quantity * parseFloat(item.unit_price)).toFixed(2)}
                    `;
                    
                    // Add review form if order is delivered
                    if(order.status === 'Delivered'){
                        itemsHtml += `
                            <div class="review-section">
                                <h4>📝 Leave a Review for ${item.name}</h4>
                                <div class="rating">
                                    <input type="radio" name="rating_${item.product_id}" value="1" id="star1_${item.product_id}">
                                    <label for="star1_${item.product_id}">★</label>
                                    <input type="radio" name="rating_${item.product_id}" value="2" id="star2_${item.product_id}">
                                    <label for="star2_${item.product_id}">★★</label>
                                    <input type="radio" name="rating_${item.product_id}" value="3" id="star3_${item.product_id}">
                                    <label for="star3_${item.product_id}">★★★</label>
                                    <input type="radio" name="rating_${item.product_id}" value="4" id="star4_${item.product_id}">
                                    <label for="star4_${item.product_id}">★★★★</label>
                                    <input type="radio" name="rating_${item.product_id}" value="5" id="star5_${item.product_id}">
                                    <label for="star5_${item.product_id}">★★★★★</label>
                                </div>
                                <textarea id="review_text_${item.product_id}" rows="3" placeholder="Write your review here..."></textarea>
                                <button class="submit-review" onclick="submitReview(${item.product_id}, ${order.id})">Submit Review</button>
                                <div id="review_result_${item.product_id}"></div>
                            </div>
                        `;
                    }
                    
                    itemsHtml += `</div>`;
                }
                itemsHtml += `<div class="total">💰 Total Amount: $${parseFloat(order.total_amount).toFixed(2)}</div>`;
            } else {
                itemsHtml += '<p>No items found for this order.</p>';
            }
            
            detailsDiv.innerHTML = itemsHtml;
        }
        
        function submitReview(productId, orderId){
            var rating = document.querySelector(`input[name="rating_${productId}"]:checked`);
            var reviewText = document.getElementById(`review_text_${productId}`).value;
            var resultDiv = document.getElementById(`review_result_${productId}`);
            
            if(!rating){
                resultDiv.innerHTML = '<div class="error-msg">⚠️ Please select a rating (1-5 stars)</div>';
                return;
            }
            
            if(reviewText.trim() === ""){
                resultDiv.innerHTML = '<div class="error-msg">⚠️ Please write your review</div>';
                return;
            }
            
            resultDiv.innerHTML = '<div style="color:blue;">Submitting...</div>';
            
            var formData = new FormData();
            formData.append("product_id", productId);
            formData.append("rating", rating.value);
            formData.append("review_text", reviewText);
            
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function(){
                if(this.readyState == 4){
                    if(this.status == 200){
                        var response = JSON.parse(this.responseText);
                        if(response.ok){
                            resultDiv.innerHTML = '<div class="success-msg">✅ Review submitted successfully! Thank you!</div>';
                            document.getElementById(`review_text_${productId}`).value = '';
                            var radios = document.querySelectorAll(`input[name="rating_${productId}"]`);
                            radios.forEach(radio => radio.checked = false);
                        } else if(response.error){
                            resultDiv.innerHTML = '<div class="error-msg">❌ ' + response.error + '</div>';
                        }
                    } else {
                        resultDiv.innerHTML = '<div class="error-msg">❌ Error submitting review. Please try again.</div>';
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
        <h1>📦 My Orders</h1>
        <div class="nav">
            <a href="../dashboard.php">🏠 Home</a>
            <a href="my-orders.php">📋 My Orders</a>
            <a href="../profile.php">👤 Profile</a>
            <a href="../../controllers/AuthController.php?action=logout">🚪 Logout</a>
        </div>
        
        <?php if(empty($orders)): ?>
            <div class="no-orders">
                <p>You haven't placed any orders yet.</p>
                <a href="../catalogue.php">🛒 Start Shopping</a>
            </div>
        <?php else: ?>
            <?php foreach($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header" onclick="toggleOrder(<?php echo $order['id']; ?>)">
                        🧾 Order #<?php echo $order['id']; ?> - 
                        📅 Date: <?php echo $order['created_at']; ?> - 
                        💰 Total: $<?php echo number_format($order['total_amount'], 2); ?> - 
                        📊 Status: <span class="badge 
                            <?php 
                                if($order['status'] == 'Pending') echo 'badge-warning';
                                elseif($order['status'] == 'Processing') echo 'badge-info';
                                elseif($order['status'] == 'Shipped') echo 'badge-primary';
                                elseif($order['status'] == 'Delivered') echo 'badge-success';
                                else echo 'badge-danger';
                            ?>">
                            <?php echo $order['status']; ?>
                        </span>
                    </div>
                    <div id="details-<?php echo $order['id']; ?>" class="order-details" style="display:none">
                        ⏳ Loading order details...
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>