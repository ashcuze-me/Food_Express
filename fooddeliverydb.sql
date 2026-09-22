-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 20, 2026 at 08:37 AM
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
-- Database: `fooddeliverydb`
--

-- --------------------------------------------------------

--
-- Table structure for table `address`
--

CREATE TABLE `address` (
  `AddressID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `HouseNo` varchar(50) DEFAULT NULL,
  `AreaStreet` varchar(150) DEFAULT NULL,
  `Landmark` varchar(150) DEFAULT NULL,
  `City` varchar(80) DEFAULT NULL,
  `State` varchar(80) DEFAULT NULL,
  `PINCode` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `address`
--

INSERT INTO `address` (`AddressID`, `CustomerID`, `HouseNo`, `AreaStreet`, `Landmark`, `City`, `State`, `PINCode`) VALUES
(3, 6, '503', 'VIT Chennai', '', 'Chennai', 'Tamil Nadu', '600127');

-- --------------------------------------------------------

--
-- Table structure for table `administrator`
--

CREATE TABLE `administrator` (
  `AdminID` int(11) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `Email` varchar(150) DEFAULT NULL,
  `PhoneNumber` varchar(20) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `Role` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cashondelivery`
--

CREATE TABLE `cashondelivery` (
  `PaymentID` int(11) NOT NULL,
  `CollectedBy` varchar(100) DEFAULT NULL,
  `CollectionStatus` enum('Not Collected','Collected') DEFAULT 'Not Collected'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `CustomerID` int(11) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `Email` varchar(150) NOT NULL,
  `PhoneNumber` varchar(20) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `RegistrationDate` datetime DEFAULT current_timestamp(),
  `AccountStatus` enum('Active','Blocked') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`CustomerID`, `FullName`, `Email`, `PhoneNumber`, `Password`, `RegistrationDate`, `AccountStatus`) VALUES
(6, 'Arin Sinha', 'sinhaarin19@gmail.com', '7709798201', 'abcd1234', '2026-09-20 11:31:10', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `delivery`
--

CREATE TABLE `delivery` (
  `DeliveryID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `DeliveryPartnerID` int(11) NOT NULL,
  `DeliveryStatus` enum('Assigned','Picked Up','Out for Delivery','Delivered') DEFAULT 'Assigned',
  `PickupTime` datetime DEFAULT NULL,
  `ActualDeliveryTime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery`
--

INSERT INTO `delivery` (`DeliveryID`, `OrderID`, `DeliveryPartnerID`, `DeliveryStatus`, `PickupTime`, `ActualDeliveryTime`) VALUES
(5, 1, 1, 'Delivered', NULL, '2026-09-20 11:32:49'),
(6, 2, 1, 'Delivered', '2026-09-20 11:41:26', '2026-09-20 12:01:31'),
(7, 3, 1, 'Delivered', '2026-09-20 12:01:50', '2026-09-20 12:01:53');

-- --------------------------------------------------------

--
-- Table structure for table `deliverypartner`
--

CREATE TABLE `deliverypartner` (
  `DeliveryPartnerID` int(11) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `PhoneNumber` varchar(20) DEFAULT NULL,
  `VehicleType` varchar(50) DEFAULT NULL,
  `VehicleNumber` varchar(30) DEFAULT NULL,
  `AvailabilityStatus` enum('Available','Busy','Offline') DEFAULT 'Available',
  `Rating` decimal(2,1) DEFAULT 0.0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deliverypartner`
--

INSERT INTO `deliverypartner` (`DeliveryPartnerID`, `FullName`, `PhoneNumber`, `VehicleType`, `VehicleNumber`, `AvailabilityStatus`, `Rating`) VALUES
(1, 'Ravi Kumar', '9876123456', 'Bike', 'TN01AB1234', 'Available', 0.0),
(2, 'Vijay Kumar', '9876123457', 'Bike', 'TN02CD5678', 'Available', 4.5),
(3, 'Karthik Raj', '9876123458', 'Scooter', 'TN03EF9012', 'Available', 4.2);

-- --------------------------------------------------------

--
-- Table structure for table `menuitem`
--

CREATE TABLE `menuitem` (
  `MenuItemID` int(11) NOT NULL,
  `ItemName` varchar(120) NOT NULL,
  `Category` varchar(80) DEFAULT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `Price` decimal(10,2) NOT NULL CHECK (`Price` >= 0),
  `AvailabilityStatus` enum('Available','Unavailable') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menuitem`
--

INSERT INTO `menuitem` (`MenuItemID`, `ItemName`, `Category`, `Description`, `Price`, `AvailabilityStatus`) VALUES
(1, 'Veg Burger', 'Fast Food', 'Fresh vegetable burger', 120.00, 'Available'),
(2, 'French Fries', 'Sides', 'Crispy fries', 80.00, 'Available'),
(3, 'Cold Coffee', 'Beverage', 'Chilled coffee', 100.00, 'Available'),
(4, 'Chicken Burger', 'Fast Food', 'Juicy chicken burger', 180.00, 'Available'),
(5, 'Paneer Burger', 'Fast Food', 'Soft paneer burger with fresh vegetables', 150.00, 'Available'),
(6, 'Veg Pizza', 'Main Course', 'Delicious vegetable pizza with fresh toppings', 220.00, 'Available'),
(7, 'Garlic Bread', 'Sides', 'Toasted garlic bread with butter', 120.00, 'Available'),
(8, 'Peri Peri Fries', 'Sides', 'Crispy fries with spicy peri peri seasoning', 110.00, 'Available'),
(9, 'Veg Sandwich', 'Fast Food', 'Fresh vegetable sandwich', 100.00, 'Available'),
(10, 'Chicken Wrap', 'Fast Food', 'Spicy chicken wrap with fresh vegetables', 180.00, 'Available'),
(11, 'Veg Fried Rice', 'Main Course', 'Flavorful vegetable fried rice', 160.00, 'Available'),
(12, 'Chocolate Shake', 'Beverage', 'Rich and creamy chocolate shake', 140.00, 'Available'),
(13, 'Chocolate Brownie', 'Dessert', 'Soft and fudgy chocolate brownie', 130.00, 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `onlinepayment`
--

CREATE TABLE `onlinepayment` (
  `PaymentID` int(11) NOT NULL,
  `TransactionID` varchar(120) NOT NULL,
  `GatewayName` varchar(80) DEFAULT NULL,
  `PaymentMethod` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `onlinepayment`
--

INSERT INTO `onlinepayment` (`PaymentID`, `TransactionID`, `GatewayName`, `PaymentMethod`) VALUES
(4, 'MOCK_TXN_4_1789886041', 'Mock Gateway', 'UPI');

-- --------------------------------------------------------

--
-- Table structure for table `orderhistory`
--

CREATE TABLE `orderhistory` (
  `ArchiveID` int(11) NOT NULL,
  `OriginalOrderID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `RestaurantID` int(11) NOT NULL,
  `AddressID` int(11) NOT NULL,
  `TotalAmount` decimal(10,2) DEFAULT NULL,
  `FinalStatus` varchar(40) NOT NULL,
  `OrderDate` datetime DEFAULT NULL,
  `CompletedAt` datetime DEFAULT current_timestamp(),
  `ArchivedReason` varchar(100) DEFAULT 'Order completed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderhistory`
--

INSERT INTO `orderhistory` (`ArchiveID`, `OriginalOrderID`, `CustomerID`, `RestaurantID`, `AddressID`, `TotalAmount`, `FinalStatus`, `OrderDate`, `CompletedAt`, `ArchivedReason`) VALUES
(1, 1, 6, 1, 3, 720.00, 'Delivered', '2026-09-20 11:31:57', '2026-09-20 11:32:50', 'Order completed'),
(2, 2, 6, 1, 3, 280.00, 'Delivered', '2026-09-20 11:38:22', '2026-09-20 12:01:52', 'Order completed'),
(3, 3, 6, 1, 3, 80.00, 'Delivered', '2026-09-20 12:01:27', '2026-09-20 12:01:54', 'Order completed');

-- --------------------------------------------------------

--
-- Table structure for table `orderitem`
--

CREATE TABLE `orderitem` (
  `OrderItemID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `MenuItemID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL CHECK (`Quantity` > 0),
  `UnitPrice` decimal(10,2) NOT NULL CHECK (`UnitPrice` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderitem`
--

INSERT INTO `orderitem` (`OrderItemID`, `OrderID`, `MenuItemID`, `Quantity`, `UnitPrice`) VALUES
(5, 1, 10, 4, 180.00),
(6, 2, 2, 1, 80.00),
(7, 2, 3, 1, 100.00),
(8, 2, 9, 1, 100.00),
(9, 3, 2, 1, 80.00),
(10, 4, 3, 1, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `OrderID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `AddressID` int(11) NOT NULL,
  `RestaurantID` int(11) NOT NULL,
  `OrderDate` datetime DEFAULT current_timestamp(),
  `TotalAmount` decimal(10,2) DEFAULT 0.00,
  `OrderStatus` enum('Placed','Accepted','Preparing','Out for Delivery','Delivered','Cancelled') DEFAULT 'Placed',
  `IsArchived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`OrderID`, `CustomerID`, `AddressID`, `RestaurantID`, `OrderDate`, `TotalAmount`, `OrderStatus`, `IsArchived`) VALUES
(1, 6, 3, 1, '2026-09-20 11:31:57', 720.00, 'Delivered', 1),
(2, 6, 3, 1, '2026-09-20 11:38:22', 280.00, 'Delivered', 1),
(3, 6, 3, 1, '2026-09-20 12:01:27', 80.00, 'Delivered', 1),
(4, 6, 3, 2, '2026-09-20 12:04:01', 100.00, 'Placed', 0);

-- --------------------------------------------------------

--
-- Table structure for table `orderstatushistory`
--

CREATE TABLE `orderstatushistory` (
  `HistoryID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `PreviousStatus` varchar(40) DEFAULT NULL,
  `NewStatus` varchar(40) NOT NULL,
  `ChangedAt` datetime DEFAULT current_timestamp(),
  `ChangedBy` varchar(50) DEFAULT 'System'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderstatushistory`
--

INSERT INTO `orderstatushistory` (`HistoryID`, `OrderID`, `PreviousStatus`, `NewStatus`, `ChangedAt`, `ChangedBy`) VALUES
(1, 4, '', 'Placed', '2026-09-20 12:04:01', 'Customer');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PaymentID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `PaymentStatus` enum('Pending','Paid','Failed','Refunded') DEFAULT 'Pending',
  `PaymentDateTime` datetime DEFAULT NULL,
  `PaymentAmount` decimal(10,2) NOT NULL CHECK (`PaymentAmount` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`PaymentID`, `OrderID`, `PaymentStatus`, `PaymentDateTime`, `PaymentAmount`) VALUES
(1, 1, 'Paid', '2026-09-20 11:31:57', 720.00),
(2, 2, 'Pending', '2026-09-20 11:38:22', 280.00),
(3, 3, 'Paid', '2026-09-20 12:01:27', 80.00),
(4, 4, 'Paid', '2026-09-20 12:04:01', 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `restaurant`
--

CREATE TABLE `restaurant` (
  `RestaurantID` int(11) NOT NULL,
  `RestaurantName` varchar(120) NOT NULL,
  `OwnerName` varchar(100) DEFAULT NULL,
  `PhoneNumber` varchar(20) DEFAULT NULL,
  `Email` varchar(150) DEFAULT NULL,
  `AddressLine1` varchar(150) DEFAULT NULL,
  `AddressLine2` varchar(150) DEFAULT NULL,
  `City` varchar(80) DEFAULT NULL,
  `State` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurant`
--

INSERT INTO `restaurant` (`RestaurantID`, `RestaurantName`, `OwnerName`, `PhoneNumber`, `Email`, `AddressLine1`, `AddressLine2`, `City`, `State`) VALUES
(1, 'FoodFlow Restaurant', 'Rahul Sharma', '9876501234', 'restaurant@example.com', NULL, NULL, 'Chennai', 'Tamil Nadu'),
(2, 'Spice Garden', 'Vikram Mehta', '9876543210', 'spicegarden@example.com', '45 Anna Salai', 'Near Central Bus Stand', 'Chennai', 'Tamil Nadu'),
(3, 'Urban Tadka', 'Rohan Kapoor', '9876543211', 'urbantadka@example.com', '22 OMR Road', 'Near Tech Park', 'Chennai', 'Tamil Nadu');

-- --------------------------------------------------------

--
-- Table structure for table `restaurantmenu`
--

CREATE TABLE `restaurantmenu` (
  `RestaurantID` int(11) NOT NULL,
  `MenuItemID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurantmenu`
--

INSERT INTO `restaurantmenu` (`RestaurantID`, `MenuItemID`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(2, 9),
(2, 10),
(2, 11),
(2, 12),
(2, 13),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(3, 6),
(3, 7),
(3, 8),
(3, 9),
(3, 10),
(3, 11),
(3, 12),
(3, 13);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `address`
--
ALTER TABLE `address`
  ADD PRIMARY KEY (`AddressID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `administrator`
--
ALTER TABLE `administrator`
  ADD PRIMARY KEY (`AdminID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `cashondelivery`
--
ALTER TABLE `cashondelivery`
  ADD PRIMARY KEY (`PaymentID`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`CustomerID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `delivery`
--
ALTER TABLE `delivery`
  ADD PRIMARY KEY (`DeliveryID`),
  ADD UNIQUE KEY `OrderID` (`OrderID`),
  ADD KEY `DeliveryPartnerID` (`DeliveryPartnerID`);

--
-- Indexes for table `deliverypartner`
--
ALTER TABLE `deliverypartner`
  ADD PRIMARY KEY (`DeliveryPartnerID`);

--
-- Indexes for table `menuitem`
--
ALTER TABLE `menuitem`
  ADD PRIMARY KEY (`MenuItemID`);

--
-- Indexes for table `onlinepayment`
--
ALTER TABLE `onlinepayment`
  ADD PRIMARY KEY (`PaymentID`),
  ADD UNIQUE KEY `TransactionID` (`TransactionID`);

--
-- Indexes for table `orderhistory`
--
ALTER TABLE `orderhistory`
  ADD PRIMARY KEY (`ArchiveID`);

--
-- Indexes for table `orderitem`
--
ALTER TABLE `orderitem`
  ADD PRIMARY KEY (`OrderItemID`),
  ADD KEY `OrderID` (`OrderID`),
  ADD KEY `MenuItemID` (`MenuItemID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `CustomerID` (`CustomerID`),
  ADD KEY `AddressID` (`AddressID`),
  ADD KEY `RestaurantID` (`RestaurantID`);

--
-- Indexes for table `orderstatushistory`
--
ALTER TABLE `orderstatushistory`
  ADD PRIMARY KEY (`HistoryID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`PaymentID`),
  ADD UNIQUE KEY `OrderID` (`OrderID`);

--
-- Indexes for table `restaurant`
--
ALTER TABLE `restaurant`
  ADD PRIMARY KEY (`RestaurantID`);

--
-- Indexes for table `restaurantmenu`
--
ALTER TABLE `restaurantmenu`
  ADD PRIMARY KEY (`RestaurantID`,`MenuItemID`),
  ADD KEY `MenuItemID` (`MenuItemID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `address`
--
ALTER TABLE `address`
  MODIFY `AddressID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `administrator`
--
ALTER TABLE `administrator`
  MODIFY `AdminID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `delivery`
--
ALTER TABLE `delivery`
  MODIFY `DeliveryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `deliverypartner`
--
ALTER TABLE `deliverypartner`
  MODIFY `DeliveryPartnerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `menuitem`
--
ALTER TABLE `menuitem`
  MODIFY `MenuItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `orderhistory`
--
ALTER TABLE `orderhistory`
  MODIFY `ArchiveID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orderitem`
--
ALTER TABLE `orderitem`
  MODIFY `OrderItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orderstatushistory`
--
ALTER TABLE `orderstatushistory`
  MODIFY `HistoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `restaurant`
--
ALTER TABLE `restaurant`
  MODIFY `RestaurantID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `address`
--
ALTER TABLE `address`
  ADD CONSTRAINT `address_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cashondelivery`
--
ALTER TABLE `cashondelivery`
  ADD CONSTRAINT `cashondelivery_ibfk_1` FOREIGN KEY (`PaymentID`) REFERENCES `payment` (`PaymentID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `delivery`
--
ALTER TABLE `delivery`
  ADD CONSTRAINT `delivery_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `delivery_ibfk_2` FOREIGN KEY (`DeliveryPartnerID`) REFERENCES `deliverypartner` (`DeliveryPartnerID`) ON UPDATE CASCADE;

--
-- Constraints for table `onlinepayment`
--
ALTER TABLE `onlinepayment`
  ADD CONSTRAINT `onlinepayment_ibfk_1` FOREIGN KEY (`PaymentID`) REFERENCES `payment` (`PaymentID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orderitem`
--
ALTER TABLE `orderitem`
  ADD CONSTRAINT `orderitem_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `orderitem_ibfk_2` FOREIGN KEY (`MenuItemID`) REFERENCES `menuitem` (`MenuItemID`) ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`AddressID`) REFERENCES `address` (`AddressID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`RestaurantID`) REFERENCES `restaurant` (`RestaurantID`) ON UPDATE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `restaurantmenu`
--
ALTER TABLE `restaurantmenu`
  ADD CONSTRAINT `restaurantmenu_ibfk_1` FOREIGN KEY (`RestaurantID`) REFERENCES `restaurant` (`RestaurantID`),
  ADD CONSTRAINT `restaurantmenu_ibfk_2` FOREIGN KEY (`MenuItemID`) REFERENCES `menuitem` (`MenuItemID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
