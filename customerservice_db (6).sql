-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 12, 2026 at 05:58 AM
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
-- Database: `customerservice_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_admins`
--

CREATE TABLE `tbl_admins` (
  `AdminID` int(11) NOT NULL,
  `Username` varchar(150) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` enum('Super Admin','Admin') NOT NULL DEFAULT 'Admin',
  `DateCreated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_admins`
--

INSERT INTO `tbl_admins` (`AdminID`, `Username`, `Password`, `Role`, `DateCreated`) VALUES
(6, 'andy', '$2y$10$KzMl1kUDnETCTpkLBtz65.8nBTHPTwd/ygQ7Sss.XrQIAwodNSKnO', 'Super Admin', '2026-01-24 10:39:49'),
(7, 'Admin', '$2y$10$K.bm1rI.R2f8FRY7bhcZDOajYtmlzyntoppZp2sa4u0Kuj.lWBYGu', 'Admin', '2026-01-24 10:39:57');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cart`
--

CREATE TABLE `tbl_cart` (
  `ProductNumber` int(100) NOT NULL,
  `CustomerNumber` varchar(100) NOT NULL,
  `ProductName` varchar(255) NOT NULL,
  `ProductCategory` varchar(255) NOT NULL,
  `UnitPrice` float(10,2) NOT NULL DEFAULT 0.00,
  `ProductPrice` float(100,2) NOT NULL,
  `Discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ProductQuantity` varchar(255) NOT NULL,
  `ProductSize` varchar(255) NOT NULL,
  `ProductSugarLevel` varchar(255) NOT NULL,
  `CustomerNickname` varchar(255) NOT NULL,
  `OrderType` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_cart`
--

INSERT INTO `tbl_cart` (`ProductNumber`, `CustomerNumber`, `ProductName`, `ProductCategory`, `UnitPrice`, `ProductPrice`, `Discount`, `ProductQuantity`, `ProductSize`, `ProductSugarLevel`, `CustomerNickname`, `OrderType`, `created_at`) VALUES
(57, 'CTMR-63FAB9', 'asdf', 'Frappe', 2.00, 2.00, 0.00, '1', 'Small', '50%', 'andy', 'Dine In', '2026-02-06 17:55:27'),
(64, 'CTMR-8E05AD', 'asdf', 'Frappe', 50.00, 50.00, 0.00, '1', 'Large', '50%', 'andy', 'Take Out', '2026-02-09 20:14:44');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_categories`
--

CREATE TABLE `tbl_categories` (
  `CategoryID` int(11) NOT NULL,
  `CategoryName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_categories`
--

INSERT INTO `tbl_categories` (`CategoryID`, `CategoryName`) VALUES
(10, 'asdasd'),
(2, 'Coffee-Based'),
(1, 'Frappe'),
(4, 'Fruit Series/Yogurt Series'),
(5, 'Fruit Smoothies/Yogurt Smoothies'),
(7, 'Iced Latte'),
(6, 'Milk Tea'),
(3, 'Specialty Frappe');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_feedbacks`
--

CREATE TABLE `tbl_feedbacks` (
  `CustomerID` int(11) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `EmailAddress` varchar(255) NOT NULL,
  `Address` varchar(255) NOT NULL,
  `Ratings` int(11) NOT NULL,
  `Message` text NOT NULL,
  `Pictures` varchar(255) NOT NULL,
  `DateCreated` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_finishedorders`
--

CREATE TABLE `tbl_finishedorders` (
  `ProductNumber` int(100) NOT NULL,
  `CustomerNumber` varchar(100) NOT NULL,
  `ProductName` varchar(255) NOT NULL,
  `ProductCategory` varchar(255) NOT NULL,
  `UnitPrice` float(10,2) NOT NULL DEFAULT 0.00,
  `ProductPrice` float(100,2) NOT NULL,
  `Discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `PWDdiscount` decimal(10,2) NOT NULL,
  `ProductQuantity` varchar(255) NOT NULL,
  `ProductSize` varchar(255) NOT NULL,
  `ProductSugarLevel` varchar(255) NOT NULL,
  `CustomerNickname` varchar(255) NOT NULL,
  `OrderType` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_ordershistory`
--

CREATE TABLE `tbl_ordershistory` (
  `ProductNumber` int(100) NOT NULL,
  `CustomerNumber` varchar(100) NOT NULL,
  `ProductName` varchar(255) NOT NULL,
  `ProductCategory` varchar(255) NOT NULL,
  `UnitPrice` float(10,2) NOT NULL DEFAULT 0.00,
  `ProductPrice` float(100,2) NOT NULL,
  `Discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `PWDdiscount` decimal(10,2) NOT NULL,
  `ProductQuantity` varchar(255) NOT NULL,
  `ProductSize` varchar(255) NOT NULL,
  `ProductSugarLevel` varchar(255) NOT NULL,
  `CustomerNickname` varchar(255) NOT NULL,
  `OrderType` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_ordershistory`
--

INSERT INTO `tbl_ordershistory` (`ProductNumber`, `CustomerNumber`, `ProductName`, `ProductCategory`, `UnitPrice`, `ProductPrice`, `Discount`, `PWDdiscount`, `ProductQuantity`, `ProductSize`, `ProductSugarLevel`, `CustomerNickname`, `OrderType`, `created_at`) VALUES
(20, 'CTMR-CC205C', 'asdf', 'Frappe', 2.00, 2.00, 0.00, 0.00, '1', 'Small', '50%', 'Guest', 'Take Out', '2026-02-06 16:12:59'),
(21, 'CTMR-415925', 'asdf', 'Frappe', 2.00, 2.00, 0.00, 0.00, '1', 'Small', '50%', 'Guest', 'Dine In', '2026-02-06 16:16:11'),
(35, 'CTMR-771E94', 'asdf', 'Frappe', 2.00, 14.00, 0.00, 0.00, '7', 'Small', '50%', 'Guest', 'Dine In', '2026-02-06 18:10:55'),
(36, 'CTMR-771E94', 'coffe ni andy asfdasdfasdfasdfasfdasdfasdfasdfasdfasf', 'Fruit Smoothies/Yogurt Smoothies', 2.00, 15.68, 2.00, 0.00, '8', 'Small', '50%', 'Guest', 'Dine In', '2026-02-06 18:10:55'),
(37, 'CTMR-771E94', 'asdf', 'Frappe', 2.00, 2.00, 0.00, 0.00, '1', 'Small', '50%', 'Guest', 'Dine In', '2026-02-06 18:10:55'),
(38, 'CTMR-771E94', 'coffe ni andy asfdasdfasdfasdfasfdasdfasdfasdfasdfasf', 'Fruit Smoothies/Yogurt Smoothies', 2.00, 1.96, 2.00, 0.00, '1', 'Small', '50%', 'Guest', 'Dine In', '2026-02-06 18:10:55'),
(39, 'CTMR-A10F71', 'andy', 'asdasd', 12.00, 11.76, 2.00, 20.00, '1', 'Small', '50%', 'Guest', 'Dine In', '2026-02-12 12:09:13'),
(40, 'CTMR-69D0AF', 'andy', 'asdasd', 12.00, 11.76, 2.00, 21.00, '1', 'Small', '50%', 'Guest', 'Dine In', '2026-02-12 12:34:19'),
(41, 'CTMR-D68DA6', 'andy', 'asdasd', 12.00, 11.76, 2.00, 0.00, '1', 'Small', '50%', 'Guest', 'Dine In', '2026-02-12 12:34:59'),
(42, 'CTMR-A47AE2', 'andyaa', 'Frappe', 1.00, 1.00, 0.00, 90.00, '1', 'Small', '50%', 'Guest', 'Dine In', '2026-02-12 12:37:53');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pendingorders`
--

CREATE TABLE `tbl_pendingorders` (
  `ProductNumber` int(11) NOT NULL,
  `CustomerNumber` varchar(100) NOT NULL,
  `ProductName` varchar(255) NOT NULL,
  `ProductCategory` varchar(255) NOT NULL,
  `UnitPrice` float(10,2) NOT NULL DEFAULT 0.00,
  `ProductPrice` float(100,2) NOT NULL,
  `Discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `PWDdiscount` decimal(10,2) NOT NULL,
  `ProductQuantity` varchar(255) NOT NULL,
  `ProductSize` varchar(255) NOT NULL,
  `ProductSugarLevel` varchar(255) NOT NULL,
  `CustomerNickname` varchar(255) NOT NULL,
  `OrderType` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_products`
--

CREATE TABLE `tbl_products` (
  `ProductNumber` varchar(255) NOT NULL,
  `ProductName` varchar(255) NOT NULL,
  `ProductCategory` varchar(255) NOT NULL,
  `ProductDescription` varchar(255) NOT NULL,
  `ProductPriceSmall` varchar(255) NOT NULL,
  `ProductPriceMedium` varchar(255) NOT NULL,
  `ProductPriceLarge` varchar(255) NOT NULL,
  `ProductImage` varchar(255) NOT NULL,
  `Discount` decimal(5,2) DEFAULT 0.00,
  `Availability` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_products`
--

INSERT INTO `tbl_products` (`ProductNumber`, `ProductName`, `ProductCategory`, `ProductDescription`, `ProductPriceSmall`, `ProductPriceMedium`, `ProductPriceLarge`, `ProductImage`, `Discount`, `Availability`) VALUES
('PROD-254A3A', 'andyaa', 'Frappe', 'adf', '1', '', '1', 'prod_698d4f5647906.jpg', 0.00, 'Available'),
('PROD-CA488C', 'andy', 'asdasd', 'asdfasdfasdf', '12', '', '2', 'prod_698d4fa673d7c.jpg', 2.00, 'Available');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_admins`
--
ALTER TABLE `tbl_admins`
  ADD PRIMARY KEY (`AdminID`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- Indexes for table `tbl_cart`
--
ALTER TABLE `tbl_cart`
  ADD PRIMARY KEY (`ProductNumber`);

--
-- Indexes for table `tbl_categories`
--
ALTER TABLE `tbl_categories`
  ADD PRIMARY KEY (`CategoryID`),
  ADD UNIQUE KEY `CategoryName` (`CategoryName`);

--
-- Indexes for table `tbl_feedbacks`
--
ALTER TABLE `tbl_feedbacks`
  ADD PRIMARY KEY (`CustomerID`);

--
-- Indexes for table `tbl_finishedorders`
--
ALTER TABLE `tbl_finishedorders`
  ADD PRIMARY KEY (`ProductNumber`);

--
-- Indexes for table `tbl_ordershistory`
--
ALTER TABLE `tbl_ordershistory`
  ADD PRIMARY KEY (`ProductNumber`);

--
-- Indexes for table `tbl_pendingorders`
--
ALTER TABLE `tbl_pendingorders`
  ADD PRIMARY KEY (`ProductNumber`);

--
-- Indexes for table `tbl_products`
--
ALTER TABLE `tbl_products`
  ADD PRIMARY KEY (`ProductNumber`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_admins`
--
ALTER TABLE `tbl_admins`
  MODIFY `AdminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_cart`
--
ALTER TABLE `tbl_cart`
  MODIFY `ProductNumber` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `tbl_categories`
--
ALTER TABLE `tbl_categories`
  MODIFY `CategoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_feedbacks`
--
ALTER TABLE `tbl_feedbacks`
  MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `tbl_finishedorders`
--
ALTER TABLE `tbl_finishedorders`
  MODIFY `ProductNumber` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `tbl_ordershistory`
--
ALTER TABLE `tbl_ordershistory`
  MODIFY `ProductNumber` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `tbl_pendingorders`
--
ALTER TABLE `tbl_pendingorders`
  MODIFY `ProductNumber` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
