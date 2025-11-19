-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 19, 2025 at 06:34 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

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
(71, NULL, 'Sample', 'user', 'Tindalo Street', 'Taguig City', 'Philippines', 'Metro Manila', '2006-08-23', '1630', '09206785416', 61, 'user@gmail.com', 'uploads/1763036063_blackldy.png'),
(72, NULL, 'Ronzhem', 'Dioso', 'Tindalo Street', 'Taguig City', 'Philippines', 'Metro Manila', '2006-08-24', '1630', '09206785416', 62, 'user@gmail.com', ''),
(73, NULL, 'Ronzhem', 'Dioso', 'Tindalo Street', 'Taguig City', 'Philippines', 'Metro Manila', '2006-08-22', '1630', '09206785416', 63, 'user1@gmail.com', ''),
(74, NULL, 'kyla', 'baguis', 'taguigi', 'yiyiyi', 'Philippines', 'Metro Manila', '2006-05-17', '345', '13212321', 64, 'root@gmail.com', ''),
(75, NULL, 'sample', 'sample', 'samplesample', 'sample', 'Philippines', 'Metro Manila', '2006-03-23', '1234', '12345', 66, 'sample2@gmail.com', 'uploads/1763261830_1760456637_lea.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE `item` (
  `item_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `category` varchar(255) NOT NULL,
  `description` varchar(64) NOT NULL,
  `short_description` varchar(255) NOT NULL,
  `specifications` text NOT NULL,
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

INSERT INTO `item` (`item_id`, `title`, `category`, `description`, `short_description`, `specifications`, `cost_price`, `sell_price`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES
(36, '', 'DSLR Cameras', 'sample multiple', 'sample', 'sample change', '12.00', '14.00', '[\"uploads\\/1763135002_7826_camera1.jpg\",\"uploads\\/1763135002_6019_camera2.jpg\"]', NULL, NULL, NULL),
(37, '', 'DSLR Cameras', 'lencs', 'sassa', 'sdads', '3.00', '5.00', '[\"uploads\\/1763141010_7852_1760441232_tissue.jpg\",\"uploads\\/1763141010_7219_1760442128_yakult.png\",\"uploads\\/1763141010_2926_1760454546_Iphone1.jpg\"]', NULL, NULL, NULL);

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
  `shipping_method` varchar(50) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` enum('Processing','Delivered','Canceled') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `orderinfo`
--

INSERT INTO `orderinfo` (`orderinfo_id`, `customer_id`, `date_placed`, `date_shipped`, `shipping`, `shipping_method`, `payment_method`, `status`, `created_at`, `updated_at`) VALUES
(26, 74, '2025-11-14', '2025-11-14', '10.00', NULL, NULL, 'Delivered', NULL, NULL),
(36, 74, '2025-11-15', NULL, '50.00', 'delivery', 'cod', 'Delivered', NULL, NULL),
(37, 75, '2025-11-15', NULL, '50.00', 'delivery', 'cod', 'Delivered', NULL, NULL),
(38, 75, '2025-11-15', NULL, '50.00', 'delivery', 'cod', 'Delivered', NULL, NULL),
(39, 75, '2025-11-16', NULL, '50.00', 'delivery', 'cod', '', NULL, NULL),
(41, 75, '2025-11-16', NULL, '50.00', 'delivery', 'cod', '', NULL, NULL),
(42, 75, '2025-11-16', NULL, '0.00', 'pickup', 'card', '', NULL, NULL),
(43, 75, '2025-11-16', NULL, '0.00', 'pickup', 'ewallet', '', NULL, NULL),
(44, 75, '2025-11-16', NULL, '0.00', 'pickup', 'card', '', NULL, NULL);

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
(26, 33, 3),
(26, 32, 3),
(0, 0, 1),
(0, 0, 1),
(36, 38, 1),
(37, 38, 1),
(38, 36, 1),
(39, 36, 1),
(41, 36, 1),
(42, 37, 1),
(43, 36, 1),
(44, 37, 1);

-- --------------------------------------------------------

--
-- Stand-in structure for view `ordertransactiondetails`
-- (See below for the actual view)
--
CREATE TABLE `ordertransactiondetails` (
`orderinfo_id` int(11)
,`date_placed` date
,`date_shipped` date
,`shipping` decimal(7,2)
,`status` enum('Processing','Delivered','Canceled')
,`customer_id` int(11)
,`customer_name` varchar(65)
,`customer_email` varchar(255)
,`item_id` int(11)
,`item_name` text
,`item_price` decimal(7,2)
,`quantity` int(11)
,`total_price` decimal(17,2)
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
(1, 74, 38, 36, 3, 'sa', 'sample sample', 1, 1, '2025-11-15 17:11:09', '2025-11-15 17:11:09');

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
(36, 0),
(37, 0);

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
(61, 'admin', 'admin@gmail.com', 'sample', 'admin', '2025-11-13 05:09:07', 1, NULL),
(62, 'userako', 'user@gmail.com', '$2y$10$S/gXtJK.tflT2ezY6NU92ugynrdl/ThsGgxNAJ.9kcUNCLCJ/h3Ce', 'customer', '2025-11-13 05:22:14', 1, NULL),
(63, 'user', 'user1@gmail.com', '$2y$10$.B78NoOfKKe0hTCBouzN..Zzzifz470lnsNOyrGdSoLG959z6TT06', 'customer', '2025-11-13 06:45:31', 1, NULL),
(64, 'root', 'root@gmail.com', '$2y$10$ZRXqGYGTJ3mXFLNZQ1bBL.KM61MGOR05/3AjwFwd/89xPD4M2vHsa', 'customer', '2025-11-13 18:46:10', 1, NULL),
(65, 'admin', 'admin@example.com', 'secureadminpassword', 'admin', '2025-11-14 13:50:37', 1, 'admin_profile.jpg'),
(66, 'sample', 'sample2@gmail.com', '$2y$10$5jf1Pegm6alNfowo3rqqLumhZLnwli/5Hm0jm4YBwCl2GwHEVFW..', 'customer', '2025-11-14 15:07:28', 1, NULL);

-- --------------------------------------------------------

--
-- Structure for view `ordertransactiondetails`
--
DROP TABLE IF EXISTS `ordertransactiondetails`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `ordertransactiondetails`  AS SELECT `o`.`orderinfo_id` AS `orderinfo_id`, `o`.`date_placed` AS `date_placed`, `o`.`date_shipped` AS `date_shipped`, `o`.`shipping` AS `shipping`, `o`.`status` AS `status`, `c`.`customer_id` AS `customer_id`, concat(`c`.`fname`,' ',`c`.`lname`) AS `customer_name`, `c`.`email` AS `customer_email`, `ol`.`item_id` AS `item_id`, `i`.`title` AS `item_name`, `i`.`sell_price` AS `item_price`, `ol`.`quantity` AS `quantity`, `ol`.`quantity`* `i`.`sell_price` AS `total_price` FROM (((`orderinfo` `o` join `customer` `c` on(`o`.`customer_id` = `c`.`customer_id`)) join `orderline` `ol` on(`o`.`orderinfo_id` = `ol`.`orderinfo_id`)) join `item` `i` on(`ol`.`item_id` = `i`.`item_id`))  ;

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
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `orderinfo_id` (`orderinfo_id`),
  ADD KEY `rating` (`rating`),
  ADD KEY `is_approved` (`is_approved`);

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
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `item`
--
ALTER TABLE `item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `orderinfo`
--
ALTER TABLE `orderinfo`
  MODIFY `orderinfo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

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
