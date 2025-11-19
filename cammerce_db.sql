-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 19, 2025 at 05:01 PM
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
-- Database: `cammerce_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customer_id` int(11) NOT NULL,
  `title` char(4) DEFAULT NULL,
  `fname` varchar(32) DEFAULT NULL,
  `lname` varchar(32) NOT NULL,
  `addressline` varchar(64) DEFAULT NULL,
  `town` varchar(32) DEFAULT NULL,
  `country` varchar(64) DEFAULT 'Philippines',
  `state` varchar(64) DEFAULT 'Metro Manila',
  `date_of_birth` date DEFAULT NULL,
  `zipcode` char(10) NOT NULL,
  `phone` varchar(16) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customer_id`, `title`, `fname`, `lname`, `addressline`, `town`, `country`, `state`, `date_of_birth`, `zipcode`, `phone`, `user_id`, `email`, `image_path`) VALUES
(78, NULL, 'Ronzhem', 'Dioso', 'Tindalo Street', 'Taguig City', 'Philippines', 'Metro Manila', '2006-08-06', '1630', '09206785416', 68, 'user@gmail.com', ''),
(79, NULL, 'Ronzhem', 'Dioso', 'Tindalo Street', 'Taguig City', 'Philippines', 'Metro Manila', '2006-08-23', '1630', '09206785416', 69, 'ronzhem23@gmail.com', ''),
(80, NULL, 'Ronzhem', 'Dioso', 'Tindalo Street', 'Taguig City', 'Philippines', 'Metro Manila', '2006-08-23', '1630', '09206785416', 70, 'ronzhem12@gmail.com', '');

-- --------------------------------------------------------

--
-- Stand-in structure for view `customer_order_history`
-- (See below for the actual view)
--
CREATE TABLE `customer_order_history` (
`customer_id` int(11)
,`customer_name` varchar(65)
,`email` varchar(255)
,`total_orders` bigint(21)
,`total_spent` decimal(40,2)
,`last_order_date` date
,`avg_order_value` decimal(21,6)
);

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE `item` (
  `item_id` int(11) NOT NULL,
  `description` varchar(64) NOT NULL,
  `short_description` text DEFAULT NULL,
  `specifications` text DEFAULT NULL,
  `category` varchar(64) DEFAULT NULL,
  `cost_price` decimal(7,2) DEFAULT NULL,
  `sell_price` decimal(7,2) DEFAULT NULL,
  `image_path` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `item`
--

INSERT INTO `item` (`item_id`, `description`, `short_description`, `specifications`, `category`, `cost_price`, `sell_price`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES
(32, 'camera', 'smnfz', 'kvmx', 'DSLR Cameras', 120.00, 150.00, '[\"uploads\\/1763531746_7574_homepage.jpg\",\"uploads\\/1763531746_4043_login-bg.jpeg\"]', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `item_sales_performance`
-- (See below for the actual view)
--
CREATE TABLE `item_sales_performance` (
`item_id` int(11)
,`item_name` varchar(64)
,`category` varchar(64)
,`sell_price` decimal(7,2)
,`current_stock` int(11)
,`total_sold` decimal(32,0)
,`total_revenue` decimal(39,2)
,`orders_count` bigint(21)
,`avg_rating` decimal(7,4)
,`review_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `orderinfo`
--

CREATE TABLE `orderinfo` (
  `orderinfo_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `date_placed` date NOT NULL,
  `date_shipped` date DEFAULT NULL,
  `shipping` decimal(7,2) DEFAULT NULL,
  `status` enum('Pending','Processing','Shipped','Delivered','Canceled') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'cash_on_delivery',
  `shipping_method` varchar(50) DEFAULT 'standard'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `orderinfo`
--

INSERT INTO `orderinfo` (`orderinfo_id`, `customer_id`, `date_placed`, `date_shipped`, `shipping`, `status`, `created_at`, `updated_at`, `payment_method`, `shipping_method`) VALUES
(26, 79, '2025-11-17', '2025-11-18', 50.00, 'Pending', NULL, '2025-11-19 04:30:28', 'cod', 'delivery'),
(27, 79, '2025-11-17', '2025-11-19', 50.00, 'Delivered', NULL, '2025-11-19 05:03:22', 'cod', 'delivery'),
(28, 78, '2025-11-19', NULL, 50.00, 'Pending', NULL, NULL, 'cod', 'delivery'),
(29, 78, '2025-11-19', NULL, 50.00, 'Pending', NULL, NULL, 'cod', 'delivery'),
(30, 78, '2025-11-19', NULL, 50.00, 'Processing', NULL, '2025-11-19 05:59:55', 'cod', 'delivery'),
(31, 78, '2025-11-19', NULL, 50.00, 'Pending', NULL, NULL, 'cod', 'delivery'),
(32, 78, '2025-11-19', NULL, 50.00, 'Pending', NULL, NULL, 'cod', 'delivery'),
(33, 78, '2025-11-19', NULL, 50.00, 'Pending', NULL, NULL, 'cod', 'delivery'),
(34, 78, '2025-11-19', '2025-11-19', 50.00, 'Shipped', NULL, '2025-11-19 14:00:46', 'cod', 'delivery'),
(35, 79, '2025-11-19', '2025-11-19', 50.00, 'Delivered', NULL, '2025-11-19 14:50:16', 'cod', 'delivery');

-- --------------------------------------------------------

--
-- Table structure for table `orderline`
--

CREATE TABLE `orderline` (
  `orderinfo_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `orderline`
--

INSERT INTO `orderline` (`orderinfo_id`, `item_id`, `quantity`) VALUES
(26, 31, 1),
(27, 31, 1),
(28, 32, 1),
(28, 31, 1),
(29, 31, 1),
(29, 32, 1),
(30, 31, 1),
(30, 32, 2),
(31, 31, 1),
(32, 31, 1),
(33, 31, 1),
(34, 32, 1),
(35, 32, 1);

-- --------------------------------------------------------

--
-- Stand-in structure for view `order_summary`
-- (See below for the actual view)
--
CREATE TABLE `order_summary` (
`orderinfo_id` int(11)
,`customer_id` int(11)
,`customer_name` varchar(65)
,`customer_email` varchar(255)
,`date_placed` date
,`date_shipped` date
,`order_status` enum('Pending','Processing','Shipped','Delivered','Canceled')
,`payment_method` varchar(50)
,`shipping_method` varchar(50)
,`total_items` bigint(21)
,`total_quantity` decimal(32,0)
,`subtotal` decimal(39,2)
,`shipping` decimal(7,2)
,`grand_total` decimal(40,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `order_transaction_details`
-- (See below for the actual view)
--
CREATE TABLE `order_transaction_details` (
`orderinfo_id` int(11)
,`date_placed` date
,`date_shipped` date
,`shipping` decimal(7,2)
,`order_status` enum('Pending','Processing','Shipped','Delivered','Canceled')
,`payment_method` varchar(50)
,`shipping_method` varchar(50)
,`customer_id` int(11)
,`customer_name` varchar(65)
,`customer_email` varchar(255)
,`customer_phone` varchar(16)
,`full_address` varchar(242)
,`item_id` int(11)
,`item_name` varchar(64)
,`item_short_desc` text
,`item_category` varchar(64)
,`item_price` decimal(7,2)
,`quantity` int(11)
,`subtotal` decimal(17,2)
,`line_total_with_shipping` decimal(18,2)
,`available_stock` int(11)
,`username` varchar(255)
,`user_role` varchar(20)
,`profile_img` varchar(255)
,`customer_image_path` varchar(255)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `pending_orders_detail`
-- (See below for the actual view)
--
CREATE TABLE `pending_orders_detail` (
`orderinfo_id` int(11)
,`date_placed` date
,`days_pending` int(7)
,`customer_name` varchar(65)
,`customer_email` varchar(255)
,`customer_phone` varchar(16)
,`items_count` bigint(21)
,`total_amount` decimal(40,2)
,`payment_method` varchar(50)
,`shipping_method` varchar(50)
);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `orderinfo_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review_title` varchar(200) NOT NULL,
  `review_text` text NOT NULL,
  `is_verified_purchase` tinyint(1) DEFAULT 1,
  `is_approved` tinyint(1) DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `customer_id`, `item_id`, `orderinfo_id`, `rating`, `review_title`, `review_text`, `is_verified_purchase`, `is_approved`, `created_at`, `updated_at`) VALUES
(1, 79, 31, 27, 2, 'HAFBds', 'good product', 1, 1, '2025-11-19 13:52:06', '2025-11-19 13:53:25');

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `stock`
--

INSERT INTO `stock` (`item_id`, `quantity`) VALUES
(32, 10);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'customer',
  `created_at` timestamp NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `profile_img` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`, `active`, `profile_img`) VALUES
(68, 'userako', 'user@gmail.com', '$2y$10$76HV673jRAZ7xRs2ydE3S.u5yk1PaSYJQ3rKzaygf4m4i56B7CWFW', 'admin', '2025-11-16 19:48:37', 1, NULL),
(69, 'usersample', 'ronzhem23@gmail.com', '$2y$10$QyX6sPPajNvtNyjpFG6YKOwjB/xzRNrdLYAxZw9iMBYlEeHWZz9ue', 'customer', '2025-11-16 22:30:08', 1, NULL),
(70, 'dioso-admin', 'ronzhem12@gmail.com', '$2y$10$ZsDbo05l5mavUZCQEMXav.aX03iiqv2JKmV2jc2fJjMFStRg4DWHq', 'customer', '2025-11-19 09:42:13', 1, NULL);

-- --------------------------------------------------------

--
-- Structure for view `customer_order_history`
--
DROP TABLE IF EXISTS `customer_order_history`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `customer_order_history`  AS SELECT `c`.`customer_id` AS `customer_id`, concat(`c`.`fname`,' ',`c`.`lname`) AS `customer_name`, `c`.`email` AS `email`, count(distinct `o`.`orderinfo_id`) AS `total_orders`, sum(`ol`.`quantity` * `i`.`sell_price` + `o`.`shipping`) AS `total_spent`, max(`o`.`date_placed`) AS `last_order_date`, avg(`ol`.`quantity` * `i`.`sell_price`) AS `avg_order_value` FROM (((`customer` `c` left join `orderinfo` `o` on(`c`.`customer_id` = `o`.`customer_id`)) left join `orderline` `ol` on(`o`.`orderinfo_id` = `ol`.`orderinfo_id`)) left join `item` `i` on(`ol`.`item_id` = `i`.`item_id`)) GROUP BY `c`.`customer_id`, `c`.`fname`, `c`.`lname`, `c`.`email` ;

-- --------------------------------------------------------

--
-- Structure for view `item_sales_performance`
--
DROP TABLE IF EXISTS `item_sales_performance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `item_sales_performance`  AS SELECT `i`.`item_id` AS `item_id`, `i`.`description` AS `item_name`, `i`.`category` AS `category`, `i`.`sell_price` AS `sell_price`, `s`.`quantity` AS `current_stock`, coalesce(sum(`ol`.`quantity`),0) AS `total_sold`, coalesce(sum(`ol`.`quantity` * `i`.`sell_price`),0) AS `total_revenue`, coalesce(count(distinct `ol`.`orderinfo_id`),0) AS `orders_count`, coalesce(avg(`r`.`rating`),0) AS `avg_rating`, coalesce(count(`r`.`review_id`),0) AS `review_count` FROM (((`item` `i` left join `orderline` `ol` on(`i`.`item_id` = `ol`.`item_id`)) left join `stock` `s` on(`i`.`item_id` = `s`.`item_id`)) left join `reviews` `r` on(`i`.`item_id` = `r`.`item_id` and `r`.`is_approved` = 1)) WHERE `i`.`deleted_at` is null GROUP BY `i`.`item_id`, `i`.`description`, `i`.`category`, `i`.`sell_price`, `s`.`quantity` ;

-- --------------------------------------------------------

--
-- Structure for view `order_summary`
--
DROP TABLE IF EXISTS `order_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `order_summary`  AS SELECT `o`.`orderinfo_id` AS `orderinfo_id`, `o`.`customer_id` AS `customer_id`, concat(`c`.`fname`,' ',`c`.`lname`) AS `customer_name`, `c`.`email` AS `customer_email`, `o`.`date_placed` AS `date_placed`, `o`.`date_shipped` AS `date_shipped`, `o`.`status` AS `order_status`, `o`.`payment_method` AS `payment_method`, `o`.`shipping_method` AS `shipping_method`, count(`ol`.`item_id`) AS `total_items`, sum(`ol`.`quantity`) AS `total_quantity`, sum(`ol`.`quantity` * `i`.`sell_price`) AS `subtotal`, `o`.`shipping` AS `shipping`, sum(`ol`.`quantity` * `i`.`sell_price`) + `o`.`shipping` AS `grand_total` FROM (((`orderinfo` `o` join `customer` `c` on(`o`.`customer_id` = `c`.`customer_id`)) join `orderline` `ol` on(`o`.`orderinfo_id` = `ol`.`orderinfo_id`)) join `item` `i` on(`ol`.`item_id` = `i`.`item_id`)) GROUP BY `o`.`orderinfo_id`, `o`.`customer_id`, `c`.`fname`, `c`.`lname`, `c`.`email`, `o`.`date_placed`, `o`.`date_shipped`, `o`.`status`, `o`.`payment_method`, `o`.`shipping_method`, `o`.`shipping` ;

-- --------------------------------------------------------

--
-- Structure for view `order_transaction_details`
--
DROP TABLE IF EXISTS `order_transaction_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `order_transaction_details`  AS SELECT `o`.`orderinfo_id` AS `orderinfo_id`, `o`.`date_placed` AS `date_placed`, `o`.`date_shipped` AS `date_shipped`, `o`.`shipping` AS `shipping`, `o`.`status` AS `order_status`, `o`.`payment_method` AS `payment_method`, `o`.`shipping_method` AS `shipping_method`, `c`.`customer_id` AS `customer_id`, concat(`c`.`fname`,' ',`c`.`lname`) AS `customer_name`, `c`.`email` AS `customer_email`, `c`.`phone` AS `customer_phone`, concat_ws(', ',`c`.`addressline`,`c`.`town`,`c`.`state`,`c`.`country`,`c`.`zipcode`) AS `full_address`, `i`.`item_id` AS `item_id`, `i`.`description` AS `item_name`, `i`.`short_description` AS `item_short_desc`, `i`.`category` AS `item_category`, `i`.`sell_price` AS `item_price`, `ol`.`quantity` AS `quantity`, `ol`.`quantity`* `i`.`sell_price` AS `subtotal`, `ol`.`quantity`* `i`.`sell_price` + `o`.`shipping` AS `line_total_with_shipping`, `s`.`quantity` AS `available_stock`, `u`.`username` AS `username`, `u`.`role` AS `user_role`, `u`.`profile_img` AS `profile_img`, `c`.`image_path` AS `customer_image_path` FROM (((((`orderinfo` `o` join `customer` `c` on(`o`.`customer_id` = `c`.`customer_id`)) join `orderline` `ol` on(`o`.`orderinfo_id` = `ol`.`orderinfo_id`)) join `item` `i` on(`ol`.`item_id` = `i`.`item_id`)) left join `stock` `s` on(`i`.`item_id` = `s`.`item_id`)) left join `users` `u` on(`c`.`user_id` = `u`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `pending_orders_detail`
--
DROP TABLE IF EXISTS `pending_orders_detail`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `pending_orders_detail`  AS SELECT `o`.`orderinfo_id` AS `orderinfo_id`, `o`.`date_placed` AS `date_placed`, to_days(curdate()) - to_days(`o`.`date_placed`) AS `days_pending`, concat(`c`.`fname`,' ',`c`.`lname`) AS `customer_name`, `c`.`email` AS `customer_email`, `c`.`phone` AS `customer_phone`, count(`ol`.`item_id`) AS `items_count`, sum(`ol`.`quantity` * `i`.`sell_price`) + `o`.`shipping` AS `total_amount`, `o`.`payment_method` AS `payment_method`, `o`.`shipping_method` AS `shipping_method` FROM (((`orderinfo` `o` join `customer` `c` on(`o`.`customer_id` = `c`.`customer_id`)) join `orderline` `ol` on(`o`.`orderinfo_id` = `ol`.`orderinfo_id`)) join `item` `i` on(`ol`.`item_id` = `i`.`item_id`)) WHERE `o`.`status` = 'Pending' GROUP BY `o`.`orderinfo_id`, `o`.`date_placed`, `c`.`fname`, `c`.`lname`, `c`.`email`, `c`.`phone`, `o`.`shipping`, `o`.`payment_method`, `o`.`shipping_method` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`),
  ADD KEY `fk_customer_user` (`user_id`);

--
-- Indexes for table `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `orderinfo`
--
ALTER TABLE `orderinfo`
  ADD PRIMARY KEY (`orderinfo_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `item`
--
ALTER TABLE `item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `orderinfo`
--
ALTER TABLE `orderinfo`
  MODIFY `orderinfo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `fk_customer_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
