-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 16, 2026 at 04:36 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecommerce_lab`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `parent_id`, `name`) VALUES
(1, NULL, 'Electronics'),
(2, NULL, 'Fashion'),
(3, NULL, 'Home & Kitchen'),
(4, 1, 'Smartphones'),
(5, 1, 'Laptops'),
(6, 2, 'Men\'s Clothing'),
(7, 2, 'Women\'s Clothing');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `payment_method` enum('Cash','Card') DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('Pending','Processing','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `shipping_address`, `payment_method`, `total_amount`, `status`, `created_at`) VALUES
(1, 1, NULL, 'Card', 89999.00, 'Delivered', '2026-05-14 16:13:47'),
(2, 2, NULL, 'Cash', 2698.00, 'Processing', '2026-05-14 16:13:47'),
(3, 1, NULL, 'Card', 31998.00, 'Delivered', '2026-05-14 16:18:31'),
(4, 2, NULL, 'Cash', 89500.00, 'Processing', '2026-05-14 16:18:31'),
(5, 1, NULL, 'Card', 5897.00, 'Shipped', '2026-05-14 16:18:31'),
(6, 1, NULL, 'Card', 30498.00, 'Delivered', '2026-05-14 16:19:21'),
(7, 2, NULL, 'Cash', 89500.00, 'Processing', '2026-05-14 16:19:21'),
(8, 1, NULL, 'Card', 5897.00, 'Shipped', '2026-05-14 16:19:21');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `unit_price`) VALUES
(10, 6, 7, 1, 28999.00),
(11, 6, 11, 1, 1499.00),
(12, 7, 8, 1, 89500.00),
(13, 8, 9, 2, 1299.00),
(14, 8, 10, 1, 3299.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock_qty` int(11) DEFAULT NULL,
  `primary_image_path` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock_qty`, `primary_image_path`, `is_available`, `created_at`) VALUES
(1, 4, 'Samsung Galaxy S24', 'Latest Samsung flagship with excellent camera', 89999.00, 25, 'images/products/s24.jpg', 1, '2026-05-14 16:13:05'),
(2, 4, 'iPhone 15 Pro', 'Apple iPhone 15 Pro 128GB', 124999.00, 12, 'images/products/iphone15.jpg', 1, '2026-05-14 16:13:05'),
(3, 5, 'Dell XPS 14', 'Powerful ultrabook with Intel i7', 145000.00, 8, 'images/products/dell-xps.jpg', 1, '2026-05-14 16:13:05'),
(4, 6, 'Men\'s Slim Fit Jeans', 'Premium denim jeans', 1899.00, 50, 'images/products/jeans.jpg', 1, '2026-05-14 16:13:05'),
(5, 7, 'Women\'s Summer Dress', 'Floral printed casual dress', 2499.00, 30, 'images/products/dress.jpg', 1, '2026-05-14 16:13:05'),
(6, 3, 'Stainless Steel Water Bottle', 'Double wall insulated bottle', 899.00, 100, 'images/products/bottle.jpg', 1, '2026-05-14 16:13:05'),
(7, 4, 'Xiaomi Redmi Note 13 Pro', '6.67\" AMOLED, 200MP camera', 28999.00, 40, 'images/products/redmi-note13.jpg', 1, '2026-05-14 16:18:31'),
(8, 5, 'HP Pavilion Gaming Laptop', 'RTX 4050, i5 13th Gen', 89500.00, 15, 'images/products/hp-gaming.jpg', 1, '2026-05-14 16:18:31'),
(9, 6, 'Men\'s Polo T-Shirt', 'Premium cotton', 1299.00, 80, 'images/products/polo-shirt.jpg', 1, '2026-05-14 16:18:31'),
(10, 7, 'Women\'s Denim Jacket', 'Oversized fit', 3299.00, 25, 'images/products/denim-jacket.jpg', 1, '2026-05-14 16:18:31'),
(11, 3, 'Wireless Bluetooth Earbuds', 'Noise cancelling', 1499.00, 60, 'images/products/earbuds.jpg', 1, '2026-05-14 16:18:31'),
(12, 4, 'OnePlus Nord CE 3', 'Smooth 120Hz display', 24999.00, 22, 'images/products/oneplus-nord.jpg', 1, '2026-05-14 16:18:31'),
(13, 4, 'Xiaomi Redmi Note 13 Pro', '6.67\" AMOLED, 200MP camera', 28999.00, 40, 'images/products/redmi-note13.jpg', 1, '2026-05-14 16:19:09'),
(14, 5, 'HP Pavilion Gaming Laptop', 'RTX 4050, i5 13th Gen', 89500.00, 15, 'images/products/hp-gaming.jpg', 1, '2026-05-14 16:19:09'),
(15, 6, 'Men\'s Polo T-Shirt', 'Premium cotton', 1299.00, 80, 'images/products/polo-shirt.jpg', 1, '2026-05-14 16:19:09'),
(16, 7, 'Women\'s Denim Jacket', 'Oversized fit', 3299.00, 25, 'images/products/denim-jacket.jpg', 1, '2026-05-14 16:19:09'),
(17, 3, 'Wireless Bluetooth Earbuds', 'Noise cancelling', 1499.00, 60, 'images/products/earbuds.jpg', 1, '2026-05-14 16:19:09'),
(18, 4, 'OnePlus Nord CE 3', 'Smooth 120Hz display', 24999.00, 22, 'images/products/oneplus-nord.jpg', 1, '2026-05-14 16:19:09');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` tinyint(4) DEFAULT NULL,
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `shipping_addresses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`shipping_addresses`)),
  `remember_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `phone`, `role`, `shipping_addresses`, `remember_token`, `created_at`) VALUES
(1, 'Rahim Khan', 'rahim@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01712345678', 'customer', NULL, NULL, '2026-05-14 16:13:33'),
(2, 'Karim Ahmed', 'karim@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01811223344', 'customer', NULL, NULL, '2026-05-14 16:13:33'),
(3, 'Admin User', 'admin@ecommerce.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01698765432', 'admin', NULL, NULL, '2026-05-14 16:13:33'),
(7, 'Admin', 'admin@test.com', '$2y$10$xayULo/X9LzSneSYXMFzIuyuDIbhFgoIXIxt2Nx/aLw1mUxpcdbHK', NULL, 'admin', NULL, '$2y$10$rXgn0I2sRr1MEs36yJo5k.BjV0SiwBl0Us/tohLcAiIpPXZFEpyLy', '2026-05-14 16:34:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
