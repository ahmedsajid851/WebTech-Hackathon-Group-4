<?php 


if(session_status() == PHP_SESSION_NONE) {
    session_start();
}
$cartCount = 0;
if (isset($_SESSION["cart"])) {
    foreach($_SESSION["cart"] as $qty) {
        $cartCount += $qty;
    }
}





?>
<!DOCTYPE html>
<html lang="en">    
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Product Catalogue</title>
        <style>
            body{
                font-family: Arial, sans-serif;
                margin: 20px;
                padding: 0;
                background-color: #f4f4f4;
            }
            .navbar {
                background-color: #333;
                color: #fff;
                padding: 10px 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
            }

            .cart-icon{
                font-size: 18px;
               
            }
            .navbar a{
                
                color: #ffffffde;
                text-decoration: none;
                margin-left: 15px;
            }

            .filters {
                display: flex;
                gap: 15px;
                margin-bottom: 20px;
            }
            .filters input, .filter select {
                padding: 8px 12px;
                border: 1px solid #ccc;
                
                border-radius: 4px ;
                width: 250px;
            }
            .product-grid {
                display: grid;
                grid-template-columns: repeat(3 , 1fr);
                gap: 20px;
            }
            .product-card {
                background-color: #fff;
                border: 1px solid #ddd;
                border-radius: 6px;
                padding: 15px;
                text-align: center;
            }
            .product-card img {
                max-width: 100%;
                height: 180px;
                object-fit: cover;
                border-radius: 4px;
                margin-bottom: 10px;
            }
            .product-card h3 {
                font-size: 16px;
                margin: 8px 0;
            }
            .product-card .price {
                font-size: 18px;
                font-weight: bold;

                color: rgba(51, 51, 51, 0.97);
                margin-bottom: 10px ;
            }
            .product-card .rating{
                color: #ff9800;
                margin-bottom: 8px;

            }
            .product-card a {
                display: inline-block;
                margin-top: 8px;
                padding: 8px 12px;
                background-color: #28a745;
                color: #333;
                text-decoration: none;
                border-radius: 4px;
                font-size: 13px;
            }
            .btn-cart{
                background-color: #007bff;
                color: #fff;
                border: none;
                padding: 8px 16px;
                border-radius: 4px;
                cursor: pointer;
                width: 100%;
                font-size: 14px;
            }
            .btn-cart:hover{
                background-color: #555;
            }
            .no-products{
                text-align: center;
                font-size: 16px;
                color: #888;
                grid-column: 1/-1;
            }
        </style>
    </head>
    <body>
        <!-- Navigation Bar -->
        <div class="navbar">
            <span><strong>My E-Commerce Store</strong></span>
            <div>
                <?php if(isset($_SESSION["name"])): ?>
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?></span>
                    <?php endif; ?>
                    <a href="../controllers/CartController.php">🛒 Cart <span id="cart-count"><?php echo $cartCount; ?></span></a>
                    <a href="../views/customer/login.php">Login</a>
                
            </div>
        </div>



        <!-- Filters -->
        <div class="filters">
            <input type="text" id="search-input" placeholder="Search products..." onkeyup="searchProducts(this.value)">

            <select id="category-filter" onchange="filterByCategory(this.value)">
                <option value="0">All Categories</option>
                <?php if (!empty($categories)): ?>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option
                         value="<?php echo $cat['id']; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>

            </select>
        </div>


        <!-- Product Grid -->
        <div class="product-grid" id="product-grid">
            <?php if (!empty($products)): ?>
                <?php while ($product = $products->fetch_assoc()): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($product['primary_image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <div class="price">$<?php echo number_format($product['price'], 2); ?></div>
                        <a href="../controllers/ProductController.php?action=view&id=<?php echo $product['id']; ?>">View Details</a>
                        <button class="btn-cart" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-products">No products available.</div>
            <?php endif; ?>
        </div>

        <script>
            function renderProducts(products) {
                var grid = document.getElementById("product-grid");
                grid.innerHTML = "";

                if(!products || products.length === 0) {
                    grid.innerHTML = '<div class="no-products">No products found.</div>';
                    return;
                }   

                products.forEach(function(product) {
                    var card = document.createElement("div");
                    card.className = "product-card";

                    var img = document.createElement("img");
                    img.src = product.primary_image_path;
                    img.alt = product.name;
                    

                    var title = document.createElement("h3");
                    title.textContent = product.name;
                    

                    var priceDiv = document.createElement("div");
                    priceDiv.className = "price";
                    priceDiv.textContent = "$" + parseFloat(product.price).toFixed(2);
                    

                    var detailsLink = document.createElement("a");
                    detailsLink.href = "../controllers/ProductController.php?action=view&id=" + product.id;
                    detailsLink.textContent = "View Details";
                    

                    var btn = document.createElement("button");
                    btn.className = "btn-cart";
                    btn.textContent = "Add to Cart";
                    btn.onclick = function() { addToCart(product.id); };
                    card.appendChild(img);
                    card.appendChild(title);
                    card.appendChild(priceDiv);
                    card.appendChild(detailsLink);
                    card.appendChild(btn);


                    grid.appendChild(card);
                });
     
            }

            function searchProducts(keyword) {
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "../controllers/ProductController.php?action=search&q=" + encodeURIComponent(keyword), true);
                xhr.onreadystatechange = function() {
                    if (xhr.status === 200 && xhr.readyState === 4) {
                        try{
                        var products = JSON.parse(xhr.responseText);
                        renderProducts(products);
                   }
                        catch(e){
                            console.log("Invalid JSON from search:", e);
                        }
                    }
                };
                xhr.send();
            }

            function filterByCategory(categoryId) {
                if (categoryId == 0) {
                    //server reload korbe...
                    window.location.href = "../controllers/ProductController.php";
                    return;
                  
                }
                
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "../controllers/ProductController.php?action=filter&category_id=" + categoryId, true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        try{
                        var products = JSON.parse(xhr.responseText);
                        renderProducts(products);
                    }
                    catch(e){
                        console.log("Invalid JSON from filter:", e);
                    }
                    }
       
            };
                xhr.send();
            }


            function addToCart(productId) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "../controllers/CartController.php?action=add", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                var response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    var cartCountSpan = document.getElementById("cart-count");
                                    if (cartCountSpan) {
                                        cartCountSpan.innerText = response.cartCount;
                                    }
                                } else {
                                    alert(response.message || "Could not add to cart");
                                }
                            } catch (e) {
                                console.log("Invalid JSON from cart add", e);
                            }
                        } else {
                            alert("Error adding to cart");
                        }
                    }
                };

                xhr.send("product_id=" + encodeURIComponent(productId));
            }

                            
          
        </script>
                 
    </body>
</html>
