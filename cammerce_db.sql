-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 13, 2025 at 04:47 PM
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
(71, NULL, 'Sample', 'user', 'Tindalo Street', 'Taguig City', 'Philippines', 'Metro Manila', '2006-08-23', '1630', '09206785416', 61, 'user@gmail.com', 'uploads/1763036063_blackldy.png'),
(72, NULL, 'Ronzhem', 'Dioso', 'Tindalo Street', 'Taguig City', 'Philippines', 'Metro Manila', '2006-08-24', '1630', '09206785416', 62, 'user@gmail.com', ''),
(73, NULL, 'Ronzhem', 'Dioso', 'Tindalo Street', 'Taguig City', 'Philippines', 'Metro Manila', '2006-08-22', '1630', '09206785416', 63, 'user1@gmail.com', '');

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE `item` (
  `item_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `description` varchar(64) NOT NULL,
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

INSERT INTO `item` (`item_id`, `title`, `description`, `cost_price`, `sell_price`, `image_path`, `created_at`, `updated_at`, `deleted_at`) VALUES
(30, '', 'Rubik Cube', 120.00, 130.00, 'uploads/1762795239_woodpuzzle.jpg', NULL, NULL, NULL);

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
  `status` enum('Processing','Delivered','Canceled') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orderline`
--

CREATE TABLE `orderline` (
  `orderinfo_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
(30, 12);

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
(61, 'admin', 'admin@gmail.com', '$2y$10$XqLeurhmVsEbKVKj4kQiMOVzcr5ii.mUQaGH2FZ6IP8iklY/.YUIK', 'admin', '2025-11-13 05:09:07', 1, NULL),
(62, 'userako', 'user@gmail.com', '$2y$10$S/gXtJK.tflT2ezY6NU92ugynrdl/ThsGgxNAJ.9kcUNCLCJ/h3Ce', 'customer', '2025-11-13 05:22:14', 1, NULL),
(63, 'user', 'user1@gmail.com', '$2y$10$.B78NoOfKKe0hTCBouzN..Zzzifz470lnsNOyrGdSoLG959z6TT06', 'customer', '2025-11-13 06:45:31', 1, NULL);

-- --------------------------------------------------------

--
-- Structure for view `ordertransactiondetails`
--
DROP TABLE IF EXISTS `ordertransactiondetails`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `ordertransactiondetails`  AS SELECT `o`.`orderinfo_id` AS `orderinfo_id`, `o`.`date_placed` AS `date_placed`, `o`.`date_shipped` AS `date_shipped`, `o`.`shipping` AS `shipping`, `o`.`status` AS `status`, `c`.`customer_id` AS `customer_id`, concat(`c`.`fname`,' ',`c`.`lname`) AS `customer_name`, `c`.`email` AS `customer_email`, `ol`.`item_id` AS `item_id`, `i`.`title` AS `item_name`, `i`.`sell_price` AS `item_price`, `ol`.`quantity` AS `quantity`, `ol`.`quantity`* `i`.`sell_price` AS `total_price` FROM (((`orderinfo` `o` join `customer` `c` on(`o`.`customer_id` = `c`.`customer_id`)) join `orderline` `ol` on(`o`.`orderinfo_id` = `ol`.`orderinfo_id`)) join `item` `i` on(`ol`.`item_id` = `i`.`item_id`)) ;

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
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `item`
--
ALTER TABLE `item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `orderinfo`
--
ALTER TABLE `orderinfo`
  MODIFY `orderinfo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

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
