-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 24, 2026 at 12:38 PM
-- Server version: 8.0.35
-- PHP Version: 8.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `escapezo_queries`
--

-- --------------------------------------------------------

--
-- Table structure for table `booking_lock_schedule`
--

CREATE TABLE `booking_lock_schedule` (
  `ID` bigint NOT NULL,
  `product_id` bigint NOT NULL,
  `booking_time` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `lock_time` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `calendar_data`
--

CREATE TABLE `calendar_data` (
  `ID` bigint NOT NULL COMMENT '\r\n\r\n',
  `data` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cpc_tracking`
--

CREATE TABLE `cpc_tracking` (
  `ID` bigint NOT NULL,
  `ip` text NOT NULL,
  `medium` text NOT NULL,
  `source` text NOT NULL,
  `terms` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `campaign` text NOT NULL,
  `count` bigint NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `hackers`
--

CREATE TABLE `hackers` (
  `ID` bigint NOT NULL,
  `host` longtext,
  `referer` longtext
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ip_activity`
--

CREATE TABLE `ip_activity` (
  `ip` varchar(45) NOT NULL,
  `product_id` int NOT NULL,
  `last_time` double NOT NULL,
  `blocked` tinyint(1) DEFAULT '0',
  `request_count` int DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `post_view_ip_checker`
--

CREATE TABLE `post_view_ip_checker` (
  `ID` bigint NOT NULL,
  `product_id` bigint NOT NULL,
  `ip` text NOT NULL,
  `view_at` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `ID` int UNSIGNED NOT NULL,
  `product_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `products_data`
--

CREATE TABLE `products_data` (
  `ID` bigint NOT NULL,
  `product_id` bigint DEFAULT NULL,
  `product_type` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notable` tinyint(1) DEFAULT NULL,
  `special` tinyint(1) DEFAULT NULL,
  `active` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monopoly` tinyint(1) NOT NULL DEFAULT '0',
  `brand_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `instant_off` text COLLATE utf8mb4_unicode_ci,
  `geo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `age_limit` int DEFAULT NULL,
  `level` int DEFAULT NULL,
  `schedule` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `duration` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auto_disable` bigint DEFAULT '0',
  `url` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `hood` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city_id` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags_id` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tags_title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `count_min` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `count_max` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pish_person` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `owner_id` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manager_id` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comments_count` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rate` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products_order`
--

CREATE TABLE `products_order` (
  `ID` bigint NOT NULL,
  `recent` text NOT NULL,
  `topsale` text NOT NULL,
  `hottest` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `popular` text NOT NULL,
  `trend` text NOT NULL,
  `nuwruz` longtext,
  `suggested` longtext
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `product_views`
--

CREATE TABLE `product_views` (
  `ID` bigint NOT NULL,
  `product_id` bigint NOT NULL,
  `views` bigint NOT NULL DEFAULT '0',
  `views30` longtext CHARACTER SET latin1 COLLATE latin1_swedish_ci
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `ID` bigint NOT NULL,
  `tag_id` bigint DEFAULT NULL,
  `tag_title` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `products` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `wp_zb_booking_history`
--

CREATE TABLE `wp_zb_booking_history` (
  `booking_id` int UNSIGNED NOT NULL,
  `customer_id` int DEFAULT NULL,
  `wc_order_id` int DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  `room_id` int DEFAULT NULL,
  `booking_time` varchar(15) DEFAULT NULL,
  `booked_time` varchar(15) DEFAULT NULL,
  `name` text,
  `phone` varchar(20) DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `level` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_zb_booking_history_today`
--

CREATE TABLE `wp_zb_booking_history_today` (
  `booking_id` int UNSIGNED NOT NULL,
  `customer_id` int DEFAULT NULL,
  `wc_order_id` int DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  `room_id` int DEFAULT NULL,
  `booking_time` varchar(15) DEFAULT NULL,
  `booked_time` varchar(15) DEFAULT NULL,
  `name` text,
  `phone` varchar(20) DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking_lock_schedule`
--
ALTER TABLE `booking_lock_schedule`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `idx_bl_product_id` (`product_id`);

--
-- Indexes for table `calendar_data`
--
ALTER TABLE `calendar_data`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `cpc_tracking`
--
ALTER TABLE `cpc_tracking`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `hackers`
--
ALTER TABLE `hackers`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `ip_activity`
--
ALTER TABLE `ip_activity`
  ADD PRIMARY KEY (`ip`);

--
-- Indexes for table `post_view_ip_checker`
--
ALTER TABLE `post_view_ip_checker`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `products_data`
--
ALTER TABLE `products_data`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_pd_active_product` (`active`,`product_id`);

--
-- Indexes for table `products_order`
--
ALTER TABLE `products_order`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `product_views`
--
ALTER TABLE `product_views`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `wp_zb_booking_history`
--
ALTER TABLE `wp_zb_booking_history`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `room_id` (`room_id`,`booking_time`),
  ADD KEY `idx_room_time_status` (`room_id`,`booking_time`,`status`),
  ADD KEY `idx_room_time` (`room_id`,`booking_time`);

--
-- Indexes for table `wp_zb_booking_history_today`
--
ALTER TABLE `wp_zb_booking_history_today`
  ADD PRIMARY KEY (`booking_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking_lock_schedule`
--
ALTER TABLE `booking_lock_schedule`
  MODIFY `ID` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `calendar_data`
--
ALTER TABLE `calendar_data`
  MODIFY `ID` bigint NOT NULL AUTO_INCREMENT COMMENT '\r\n\r\n';

--
-- AUTO_INCREMENT for table `cpc_tracking`
--
ALTER TABLE `cpc_tracking`
  MODIFY `ID` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hackers`
--
ALTER TABLE `hackers`
  MODIFY `ID` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_view_ip_checker`
--
ALTER TABLE `post_view_ip_checker`
  MODIFY `ID` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `ID` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products_data`
--
ALTER TABLE `products_data`
  MODIFY `ID` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products_order`
--
ALTER TABLE `products_order`
  MODIFY `ID` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_views`
--
ALTER TABLE `product_views`
  MODIFY `ID` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `ID` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wp_zb_booking_history`
--
ALTER TABLE `wp_zb_booking_history`
  MODIFY `booking_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wp_zb_booking_history_today`
--
ALTER TABLE `wp_zb_booking_history_today`
  MODIFY `booking_id` int UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
