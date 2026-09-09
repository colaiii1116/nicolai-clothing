-- Nicolai Clothing Full Database Schema & Data Dump
-- Database: `nicolai_clothing`
-- Generated: 2026-09-08 11:06:32

CREATE DATABASE IF NOT EXISTS `nicolai_clothing` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `nicolai_clothing`;

-- Table structure for `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(191) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('customer','admin','student') NOT NULL DEFAULT 'customer',
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postal_code` varchar(30) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Philippines',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `users`
INSERT INTO `users` (`id`, `email`, `password_hash`, `name`, `role`, `phone`, `address`, `city`, `postal_code`, `country`, `created_at`, `updated_at`) VALUES ('1', 'admin@nicolai.local', '$2y$10$xcbV6.gz87r6uYakMXe7QOAwTaBjdUG1bITYdt5JdpvIhsUpJhA0.', 'Nicolai Administrator', 'admin', '+63 912 345 6789', '123 Nicolai Boulevard', 'Dumaguete City', '6200', 'Philippines', '2026-09-08 11:35:08', '2026-09-08 11:54:38');
INSERT INTO `users` (`id`, `email`, `password_hash`, `name`, `role`, `phone`, `address`, `city`, `postal_code`, `country`, `created_at`, `updated_at`) VALUES ('2', 'student@nicolai.local', '$2y$10$Eo3UQpROp0pvAvJQKwLbLOBEQwQkJ73uFC6WC03CnFwtJ1mh5WL/G', 'Nicolai Student', 'customer', '+63 918 765 4321', '456 University Way', 'Dumaguete City', '6200', 'Philippines', '2026-09-08 11:35:08', '2026-09-08 11:54:38');

-- Table structure for `products`
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` varchar(64) NOT NULL,
  `name` varchar(150) NOT NULL,
  `base_name` varchar(150) NOT NULL,
  `category` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `color` varchar(50) NOT NULL,
  `colors_json` text DEFAULT NULL,
  `sizes_json` varchar(100) DEFAULT '["S","M","L","XL"]',
  `description` text NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 50,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `products`
INSERT INTO `products` (`id`, `name`, `base_name`, `category`, `price`, `image`, `color`, `colors_json`, `sizes_json`, `description`, `stock_quantity`, `is_active`, `featured`, `created_at`, `updated_at`) VALUES ('nc-classic-tee-beige', 'NC CLASSIC TEE - BEIGE', 'NC CLASSIC TEE', 'TOPS', '100.00', 'classic-tee-beige.png', 'Beige', '{\"Beige\":\"classic-tee-beige.png\",\"Black\":\"classic-tee.png\",\"Olive\":\"classic-tee-olive.png\"}', '[\"S\",\"M\",\"L\",\"XL\"]', 'Minimalist everyday tee in warm beige with embroidered NC monogram.', '55', '1', '0', '2026-09-08 11:35:08', '2026-09-08 11:35:08');
INSERT INTO `products` (`id`, `name`, `base_name`, `category`, `price`, `image`, `color`, `colors_json`, `sizes_json`, `description`, `stock_quantity`, `is_active`, `featured`, `created_at`, `updated_at`) VALUES ('nc-classic-tee-black', 'NC CLASSIC TEE - BLACK', 'NC CLASSIC TEE', 'TOPS', '100.00', 'classic-tee.png', 'Black', '{\"Black\":\"classic-tee.png\",\"Olive\":\"classic-tee-olive.png\",\"Beige\":\"classic-tee-beige.png\"}', '[\"S\",\"M\",\"L\",\"XL\"]', 'A timeless essential. Crafted from premium cotton for all-day comfort and effortless style.', '70', '1', '1', '2026-09-08 11:35:08', '2026-09-08 11:35:08');
INSERT INTO `products` (`id`, `name`, `base_name`, `category`, `price`, `image`, `color`, `colors_json`, `sizes_json`, `description`, `stock_quantity`, `is_active`, `featured`, `created_at`, `updated_at`) VALUES ('nc-classic-tee-olive', 'NC CLASSIC TEE - OLIVE', 'NC CLASSIC TEE', 'TOPS', '100.00', 'classic-tee-olive.png', 'Olive', '{\"Olive\":\"classic-tee-olive.png\",\"Black\":\"classic-tee.png\",\"Beige\":\"classic-tee-beige.png\"}', '[\"S\",\"M\",\"L\",\"XL\"]', 'A timeless luxury essential crafted from premium cotton in earthy olive.', '60', '1', '1', '2026-09-08 11:35:08', '2026-09-08 11:35:08');
INSERT INTO `products` (`id`, `name`, `base_name`, `category`, `price`, `image`, `color`, `colors_json`, `sizes_json`, `description`, `stock_quantity`, `is_active`, `featured`, `created_at`, `updated_at`) VALUES ('nc-essential-hoodie-beige', 'NC ESSENTIAL HOODIE - BEIGE', 'NC ESSENTIAL HOODIE', 'HOODIES', '120.00', 'essential-hoodie-beige.png', 'Beige', '{\"Beige\":\"essential-hoodie-beige.png\",\"Black\":\"essential-hoodie-black.png\",\"Olive\":\"essential-hoodie-olive.png\"}', '[\"S\",\"M\",\"L\",\"XL\"]', 'Signature heavyweight hoodie crafted in warm natural beige.', '50', '1', '0', '2026-09-08 11:35:08', '2026-09-08 11:35:08');
INSERT INTO `products` (`id`, `name`, `base_name`, `category`, `price`, `image`, `color`, `colors_json`, `sizes_json`, `description`, `stock_quantity`, `is_active`, `featured`, `created_at`, `updated_at`) VALUES ('nc-essential-hoodie-black', 'NC ESSENTIAL HOODIE - BLACK', 'NC ESSENTIAL HOODIE', 'HOODIES', '120.00', 'essential-hoodie-black.png', 'Black', '{\"Black\":\"essential-hoodie-black.png\",\"Olive\":\"essential-hoodie-olive.png\",\"Beige\":\"essential-hoodie-beige.png\"}', '[\"S\",\"M\",\"L\",\"XL\"]', 'Premium comfort with a clean, elevated silhouette in signature black.', '50', '1', '1', '2026-09-08 11:35:08', '2026-09-08 11:54:38');
INSERT INTO `products` (`id`, `name`, `base_name`, `category`, `price`, `image`, `color`, `colors_json`, `sizes_json`, `description`, `stock_quantity`, `is_active`, `featured`, `created_at`, `updated_at`) VALUES ('nc-essential-hoodie-olive', 'NC ESSENTIAL HOODIE - OLIVE', 'NC ESSENTIAL HOODIE', 'HOODIES', '120.00', 'essential-hoodie-olive.png', 'Olive', '{\"Olive\":\"essential-hoodie-olive.png\",\"Black\":\"essential-hoodie-black.png\",\"Beige\":\"essential-hoodie-beige.png\"}', '[\"S\",\"M\",\"L\",\"XL\"]', 'Elevated heavyweight comfort in timeless earthy olive tone.', '50', '1', '0', '2026-09-08 11:35:08', '2026-09-08 11:35:08');
INSERT INTO `products` (`id`, `name`, `base_name`, `category`, `price`, `image`, `color`, `colors_json`, `sizes_json`, `description`, `stock_quantity`, `is_active`, `featured`, `created_at`, `updated_at`) VALUES ('nc-premium-cap-beige', 'NC PREMIUM CAP - BEIGE', 'NC PREMIUM CAP', 'ACCESSORIES', '100.00', 'premium-cap-beige.png', 'Beige', '{\"Beige\":\"premium-cap-beige.png\",\"Olive\":\"premium-cap-olive.png\",\"Black\":\"premium-cap.png\"}', '[\"ONE SIZE\"]', 'Structured lifestyle cap in neutral beige with dual NC monogram embroidery.', '35', '1', '0', '2026-09-08 11:35:08', '2026-09-08 11:35:08');
INSERT INTO `products` (`id`, `name`, `base_name`, `category`, `price`, `image`, `color`, `colors_json`, `sizes_json`, `description`, `stock_quantity`, `is_active`, `featured`, `created_at`, `updated_at`) VALUES ('nc-premium-cap-black', 'NC PREMIUM CAP - BLACK', 'NC PREMIUM CAP', 'ACCESSORIES', '100.00', 'premium-cap.png', 'Black', '{\"Black\":\"premium-cap.png\",\"Olive\":\"premium-cap-olive.png\",\"Beige\":\"premium-cap-beige.png\"}', '[\"ONE SIZE\"]', 'A polished everyday accessory with signature Nicolai monogram branding.', '50', '1', '1', '2026-09-08 11:35:08', '2026-09-08 11:35:08');
INSERT INTO `products` (`id`, `name`, `base_name`, `category`, `price`, `image`, `color`, `colors_json`, `sizes_json`, `description`, `stock_quantity`, `is_active`, `featured`, `created_at`, `updated_at`) VALUES ('nc-premium-cap-olive', 'NC PREMIUM CAP - OLIVE', 'NC PREMIUM CAP', 'ACCESSORIES', '100.00', 'premium-cap-olive.png', 'Olive', '{\"Olive\":\"premium-cap-olive.png\",\"Beige\":\"premium-cap-beige.png\",\"Black\":\"premium-cap.png\"}', '[\"ONE SIZE\"]', 'Polished streetwear accessory featuring the gold-stitched Nicolai monogram in olive.', '35', '1', '0', '2026-09-08 11:35:08', '2026-09-08 11:35:08');
INSERT INTO `products` (`id`, `name`, `base_name`, `category`, `price`, `image`, `color`, `colors_json`, `sizes_json`, `description`, `stock_quantity`, `is_active`, `featured`, `created_at`, `updated_at`) VALUES ('nc-signature-jacket-black', 'NC SIGNATURE JACKET - BLACK', 'NC SIGNATURE JACKET', 'OUTERWEAR', '150.00', 'signature-jacket-black.png', 'Black', '{\"Black\":\"signature-jacket-black.png\",\"Olive\":\"signature-jacket-olive.png\"}', '[\"S\",\"M\",\"L\",\"XL\"]', 'A refined outer layer designed for everyday confidence in bold black.', '45', '1', '1', '2026-09-08 11:35:08', '2026-09-08 11:35:08');
INSERT INTO `products` (`id`, `name`, `base_name`, `category`, `price`, `image`, `color`, `colors_json`, `sizes_json`, `description`, `stock_quantity`, `is_active`, `featured`, `created_at`, `updated_at`) VALUES ('nc-signature-jacket-olive', 'NC SIGNATURE JACKET - OLIVE', 'NC SIGNATURE JACKET', 'OUTERWEAR', '150.00', 'signature-jacket-olive.png', 'Olive', '{\"Olive\":\"signature-jacket-olive.png\",\"Black\":\"signature-jacket-black.png\"}', '[\"S\",\"M\",\"L\",\"XL\"]', 'Tailored outerwear combining military olive tones with luxury finish.', '40', '1', '0', '2026-09-08 11:35:08', '2026-09-08 11:35:08');

-- Table structure for `carts`
DROP TABLE IF EXISTS `carts`;
CREATE TABLE `carts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(128) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `carts`
INSERT INTO `carts` (`id`, `session_id`, `user_id`, `created_at`, `updated_at`) VALUES ('1', 'test_session_732d12fff5ed', '2', '2026-09-08 11:52:32', '2026-09-08 11:52:32');
INSERT INTO `carts` (`id`, `session_id`, `user_id`, `created_at`, `updated_at`) VALUES ('2', '6soajbiqdi1j0agif6nfotv7pe', NULL, '2026-09-08 11:53:46', '2026-09-08 11:53:46');
INSERT INTO `carts` (`id`, `session_id`, `user_id`, `created_at`, `updated_at`) VALUES ('3', 'taaojjo817kehkchp1057jbsjd', NULL, '2026-09-08 11:53:47', '2026-09-08 11:53:47');
INSERT INTO `carts` (`id`, `session_id`, `user_id`, `created_at`, `updated_at`) VALUES ('4', '95d78ka4mc478db8oi4acicosg', NULL, '2026-09-08 11:57:15', '2026-09-08 11:57:15');

-- Table structure for `cart_items`
DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cart_id` int(11) NOT NULL,
  `product_id` varchar(64) NOT NULL,
  `color` varchar(50) NOT NULL,
  `size` varchar(20) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cart_item` (`cart_id`,`product_id`,`color`,`size`),
  KEY `fk_item_product` (`product_id`),
  CONSTRAINT `fk_item_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_item_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `cart_items`
INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `color`, `size`, `quantity`, `unit_price`, `created_at`, `updated_at`) VALUES ('1', '1', 'nc-essential-hoodie-black', 'Black', 'M', '3', '120.00', '2026-09-08 11:52:32', '2026-09-08 11:53:28');

-- Table structure for `orders`
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(32) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(191) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `shipping_address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `postal_code` varchar(30) NOT NULL,
  `country` varchar(100) NOT NULL DEFAULT 'Philippines',
  `subtotal` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_status` enum('Pending','Confirmed','Paid','Failed') NOT NULL DEFAULT 'Pending',
  `order_status` enum('Pending','Processing','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `idx_order_user` (`user_id`),
  KEY `idx_order_status` (`order_status`),
  CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `orders`
INSERT INTO `orders` (`id`, `order_number`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `city`, `postal_code`, `country`, `subtotal`, `shipping_fee`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `notes`, `created_at`, `updated_at`) VALUES ('1', 'NC-2026-8CFB52', '2', 'Nicolai Test Customer', 'student@nicolai.local', '+63 912 345 6789', '789 Rizal Boulevard', 'Dumaguete City', '6200', 'Philippines', '360.00', '0.00', '360.00', 'Cash on Delivery (COD)', 'Confirmed', 'Processing', 'Test verification order', '2026-09-08 11:52:32', '2026-09-08 11:52:32');
INSERT INTO `orders` (`id`, `order_number`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `city`, `postal_code`, `country`, `subtotal`, `shipping_fee`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `notes`, `created_at`, `updated_at`) VALUES ('2', 'NC-2026-25064F', '2', 'Nicolai Test Customer', 'student@nicolai.local', '+63 912 345 6789', '789 Rizal Boulevard', 'Dumaguete City', '6200', 'Philippines', '360.00', '0.00', '360.00', 'Cash on Delivery (COD)', 'Confirmed', 'Processing', 'Test verification order', '2026-09-08 11:53:28', '2026-09-08 11:53:28');

-- Table structure for `order_items`
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` varchar(64) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `product_image` varchar(255) NOT NULL,
  `color` varchar(50) NOT NULL,
  `size` varchar(20) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `line_total` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_order_item` (`order_id`),
  CONSTRAINT `fk_order_item` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `order_items`
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `product_image`, `color`, `size`, `quantity`, `unit_price`, `line_total`) VALUES ('1', '1', 'nc-essential-hoodie-black', 'NC ESSENTIAL HOODIE - BLACK', 'essential-hoodie-black.png', 'Black', 'M', '3', '120.00', '360.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `product_image`, `color`, `size`, `quantity`, `unit_price`, `line_total`) VALUES ('2', '2', 'nc-essential-hoodie-black', 'NC ESSENTIAL HOODIE - BLACK', 'essential-hoodie-black.png', 'Black', 'M', '3', '120.00', '360.00');

-- Table structure for `contact_messages`
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(191) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('New','Read','Replied') NOT NULL DEFAULT 'New',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `contact_messages`
INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `status`, `created_at`) VALUES ('1', 'Juan Dela Cruz', 'juan@example.ph', 'Sizing Inquiry', 'Does the hoodie run true to size?', 'New', '2026-09-08 11:52:32');
INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `status`, `created_at`) VALUES ('2', 'Juan Dela Cruz', 'juan@example.ph', 'Sizing Inquiry', 'Does the hoodie run true to size?', 'New', '2026-09-08 11:53:28');

-- Table structure for `newsletter_subscribers`
DROP TABLE IF EXISTS `newsletter_subscribers`;
CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(191) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `newsletter_subscribers`
INSERT INTO `newsletter_subscribers` (`id`, `email`, `created_at`) VALUES ('1', 'vip-buyer@example.ph', '2026-09-08 11:52:32');

