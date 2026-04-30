-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 30, 2026 at 06:27 AM
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
-- Database: `cobaekspor`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Elektronik', 'Produk elektronik seperti HP, laptop, dll', '2026-03-08 09:39:01'),
(2, 'Fashion', 'Pakaian, sepatu, aksesoris', '2026-03-08 09:39:01'),
(3, 'Makanan & Minuman', 'Produk makanan dan minuman', '2026-03-08 09:39:01'),
(4, 'Rumah Tangga', 'Peralatan rumah tangga', '2026-03-08 09:39:01'),
(5, 'Olahraga', 'Perlengkapan olahraga', '2026-03-08 09:39:01');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `status` enum('pending','paid','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `total_amount`, `status`, `payment_method`, `shipping_address`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'ORD-20250309-001', 1, 3500000.00, 'delivered', 'Transfer Bank', 'Jl. Contoh No. 123, Jakarta', NULL, '2026-03-09 09:05:09', '2026-03-09 09:05:09'),
(2, 'ORD-20250309-002', 2, 335000.00, 'processing', 'COD', 'Jl. Test No. 456, Bandung', NULL, '2026-03-09 09:05:09', '2026-03-09 09:05:09'),
(3, 'ORD-20250309-003', 3, 45000.00, 'pending', 'Transfer Bank', 'Jl. Demo No. 789, Surabaya', NULL, '2026-03-09 09:05:09', '2026-03-09 09:05:09');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES
(1, 1, 1, 1, 3500000.00, 3500000.00),
(2, 2, 3, 2, 85000.00, 170000.00),
(3, 2, 5, 1, 45000.00, 45000.00),
(4, 2, 8, 1, 120000.00, 120000.00),
(5, 3, 5, 1, 45000.00, 45000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(15,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT 'default-product.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image`, `created_at`, `updated_at`) VALUES
(1, 1, 'Smartphone XYZ', 'Smartphone dengan spesifikasi tinggi', 3500000.00, 15, 'default-product.jpg', '2026-03-08 09:39:03', '2026-03-08 09:39:03'),
(2, 1, 'Laptop ABC', 'Laptop untuk kerja dan gaming', 8500000.00, 7, 'default-product.jpg', '2026-03-08 09:39:03', '2026-03-08 09:39:03'),
(3, 2, 'Kaos Polos', 'Kaos katun nyaman dipakai', 85000.00, 50, 'default-product.jpg', '2026-03-08 09:39:03', '2026-03-08 09:39:03'),
(4, 2, 'Sepatu Running', 'Sepatu untuk olahraga lari', 250000.00, 20, 'default-product.jpg', '2026-03-08 09:39:03', '2026-03-08 09:39:03'),
(5, 3, 'Kopi Arabika', 'Kopi asli dari Gayo', 45000.00, 100, 'default-product.jpg', '2026-03-08 09:39:03', '2026-03-08 09:39:03'),
(6, 3, 'Cokelat Premium', 'Cokelat impor berkualitas', 65000.00, 75, 'default-product.jpg', '2026-03-08 09:39:03', '2026-03-08 09:39:03'),
(7, 4, 'Panci Serbaguna', 'Panci anti lengket', 120000.00, 30, 'default-product.jpg', '2026-03-08 09:39:03', '2026-03-08 09:39:03'),
(8, 5, 'Bola Sepak', 'Bola ukuran standar FIFA', 150000.00, 25, 'default-product.jpg', '2026-03-08 09:39:03', '2026-03-08 09:39:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `avatar` varchar(255) DEFAULT 'default.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `address`, `phone`, `role`, `avatar`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@cobaekspor.com', '0192023a7bbd73250516f069df18b500', 'Administrator', NULL, NULL, 'admin', 'default.jpg', '2026-03-08 09:39:01', '2026-03-08 09:39:01'),
(2, 'john_doe', 'john@example.com', '482c811da5d5b4bc6d497ffa98491e38', 'John Doe', NULL, NULL, 'user', 'default.jpg', '2026-03-08 09:39:01', '2026-03-08 09:39:01'),
(3, 'jane_smith', 'jane@example.com', '96b33694c4bb7dbd07391e0be54745fb', 'Jane Smith', NULL, NULL, 'user', 'default.jpg', '2026-03-08 09:39:01', '2026-03-08 09:39:01'),
(4, 'rouf', 'rouf@gmail.com', '880e004cdb4c14b1faa60ca23ee20379', 'roufmz', NULL, NULL, 'user', 'default.jpg', '2026-03-09 04:12:02', '2026-03-09 04:12:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
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
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
