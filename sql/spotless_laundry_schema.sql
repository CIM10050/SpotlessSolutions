-- REBUILD spotless_laundry FROM SCRATCH
-- =====================================

-- Drop and recreate the database
DROP DATABASE IF EXISTS `spotless_laundry`;
CREATE DATABASE `spotless_laundry`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
USE `spotless_laundry`;

-- If you're running this inside an existing DB session later, also clear tables:
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_items, orders, bookings, products, services, shop_settings, users;
SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------
-- users
-- ----------------------------
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `user_role` enum('customer','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- admin user (same as your dump)
INSERT INTO `users`
(`user_id`, `full_name`, `email`, `phone`, `password_hash`, `address`, `user_role`, `created_at`)
VALUES
(4, 'Akram Ali', 'admin@spotless.com', '', '$2y$10$Q1D/50JyXdCr7.karlsjAeHBq5rPaQKP5eGOyhErbBkjXUA25ncuK', NULL, 'admin', '2025-07-27 06:23:58');

-- ----------------------------
-- products
-- ----------------------------
CREATE TABLE `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `products`
(`product_id`, `product_name`, `description`, `price`, `stock_quantity`, `image_url`, `is_available`)
VALUES
(1, 'test product', 'Testing the product', 25.00, 23, '1753603942_illustration of best and worst laundry detergents.jpg', 1);

-- ----------------------------
-- services
-- ----------------------------
CREATE TABLE `services` (
  `service_id` int(11) NOT NULL AUTO_INCREMENT,
  `service_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `services`
(`service_id`, `service_name`, `description`, `price`, `image`, `duration_minutes`, `is_active`)
VALUES
(1, 'Test Service 1', 'Test the servie 12', 40.02, '1753599446_istockphoto-1329135522-612x612.jpg', NULL, 1);

-- ----------------------------
-- shop_settings
-- ----------------------------
CREATE TABLE `shop_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- orders
-- ----------------------------
CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `delivery_option` enum('pickup','dropoff','walkin') NOT NULL,
  `address_line1` varchar(150) DEFAULT NULL,
  `suburb` varchar(80) DEFAULT NULL,
  `state` varchar(10) DEFAULT NULL,
  `postcode` varchar(10) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivery_type` enum('walkin','pickup','dropoff') NOT NULL DEFAULT 'walkin',
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- order_items
-- ----------------------------
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `item_type` enum('service','product') NOT NULL,
  `ref_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `line_total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- bookings (optional; unchanged)
-- ----------------------------
CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `service_type` enum('pickup','dropoff','walkin') NOT NULL,
  `address` text DEFAULT NULL,
  `scheduled_date` date NOT NULL,
  `scheduled_time` time NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `special_instructions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`booking_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Recreate the same AUTO_INCREMENT counters you had
ALTER TABLE `orders`       AUTO_INCREMENT = 4;
ALTER TABLE `order_items`  AUTO_INCREMENT = 3;
ALTER TABLE `products`     AUTO_INCREMENT = 2;
ALTER TABLE `services`     AUTO_INCREMENT = 2;
ALTER TABLE `users`        AUTO_INCREMENT = 5;

-- Helpful indexes (optional but recommended)
CREATE INDEX idx_order_items_order_id ON order_items(order_id);
CREATE INDEX idx_order_items_ord_ref_type ON order_items(order_id, ref_id, item_type);
CREATE INDEX idx_orders_user_status ON orders(user_id, status);
CREATE INDEX idx_orders_created ON orders(created_at);
