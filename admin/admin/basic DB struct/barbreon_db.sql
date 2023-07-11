-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 02, 2023 at 01:04 PM
-- Server version: 10.3.38-MariaDB-0ubuntu0.20.04.1
-- PHP Version: 8.2.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gm_barbreon_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `sym_business_hours`
--

CREATE TABLE `sym_business_hours` (
  `id` int(11) NOT NULL,
  `name` text DEFAULT NULL,
  `nameEn` text DEFAULT NULL,
  `startTime` text DEFAULT NULL,
  `endTime` text DEFAULT NULL,
  `dateTimeUpdated` text DEFAULT NULL,
  `active` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `sym_business_hours`
--

INSERT INTO `sym_business_hours` (`id`, `name`, `nameEn`, `startTime`, `endTime`, `dateTimeUpdated`, `active`) VALUES
(1, 'Δευτέρα', 'Monday', '12:00', '19:00', '2023-06-26 14:29:09', 1),
(2, 'Τρίτη', 'Tuesday', '12:00', '14:00', '2023-06-26 14:29:09', 1),
(3, 'Τετάρτη', 'Wednesday', '10:00', '20:00', '2023-06-26 14:29:09', 1),
(4, 'Πέμπτη', 'Thursday', '10:00', '03:00', '2023-06-26 14:29:09', 0),
(5, 'Παρασκευή', 'Friday', '10:00', '14:00', '2023-06-26 14:29:09', 1),
(6, 'Σάββατο', 'Saturday', '12:00', '18:00', '2023-06-26 14:29:09', 1),
(7, 'Κυριακή', 'Sunday', '11:49', '03:49', '2023-06-26 14:29:09', 0);

-- --------------------------------------------------------

--
-- Table structure for table `sym_business_images`
--

CREATE TABLE `sym_business_images` (
  `id` int(11) NOT NULL,
  `typeId` text DEFAULT NULL,
  `image` text DEFAULT NULL,
  `dateTimeCreated` datetime DEFAULT current_timestamp(),
  `active` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `sym_business_images`
--
-- --------------------------------------------------------

--
-- Table structure for table `sym_customers`
--

CREATE TABLE `sym_customers` (
  `id` int(11) NOT NULL,
  `name` text DEFAULT NULL,
  `surname` text DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` text DEFAULT NULL,
  `referrer` text DEFAULT NULL,
  `note` text DEFAULT NULL,
  `dateTimeCreated` datetime DEFAULT current_timestamp(),
  `active` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sym_discounts_rules`
--

CREATE TABLE `sym_discounts_rules` (
  `id` int(11) NOT NULL,
  `discountMode` text DEFAULT NULL,
  `clientType` text DEFAULT NULL,
  `discountType` text DEFAULT NULL,
  `discountValue` text DEFAULT NULL,
  `rangeType` text DEFAULT NULL,
  `rangeCategory` text DEFAULT NULL,
  `rangeCategoryValues` text DEFAULT NULL,
  `dateTimeCreated` text DEFAULT NULL,
  `dateTimeUpdated` datetime DEFAULT current_timestamp(),
  `active` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `sym_discounts_rules`
--

-- --------------------------------------------------------

--
-- Table structure for table `sym_haircuts`
--

CREATE TABLE `sym_haircuts` (
  `id` int(11) NOT NULL,
  `customerId` int(11) DEFAULT NULL,
  `serviceId` text DEFAULT NULL,
  `hairCutterId` int(11) DEFAULT NULL,
  `commission` int(11) DEFAULT NULL,
  `discountPercentage` int(11) DEFAULT 0,
  `executionTime` text DEFAULT NULL,
  `dateTimeExecuted` datetime DEFAULT NULL,
  `distance` int(11) DEFAULT 0,
  `isOutcall` int(11) DEFAULT 0,
  `isSOS` int(11) DEFAULT 0,
  `street` text DEFAULT NULL,
  `streetNumber` text DEFAULT NULL,
  `town` text DEFAULT NULL,
  `country` text DEFAULT NULL,
  `floor` text DEFAULT NULL,
  `zipCode` text DEFAULT NULL,
  `lng` text DEFAULT NULL,
  `lat` text DEFAULT NULL,
  `note` text DEFAULT NULL,
  `emailNotificationAccept` int(11) DEFAULT NULL,
  `smsNotificationAccept` int(11) DEFAULT NULL,
  `appointmentAccepted` int(11) DEFAULT 0,
  `appointmentDeclined` int(11) DEFAULT 0,
  `dateTimeCreated` datetime DEFAULT current_timestamp(),
  `knownAreaId` int(11) DEFAULT 0,
  `knownAreaFee` varchar(11) DEFAULT '0',
  `knownAreaFeePercentage` varchar(100) DEFAULT '0',
  `applicationFeePercentage` varchar(100) DEFAULT '0',
  `sosPercentage` varchar(100) DEFAULT '0',
  `barberFee` varchar(100) DEFAULT '0',
  `barberFeePercentage` varchar(100) DEFAULT '0',
  `haircutProviderAmount` varchar(100) DEFAULT '0',
  `totalFeeCharged` varchar(100) DEFAULT '0',
  `knownAreaFeeAC` varchar(100) DEFAULT '0',
  `applicationFee` varchar(100) DEFAULT '0',
  `active` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sym_hair_cutters`
--

CREATE TABLE `sym_hair_cutters` (
  `id` int(11) NOT NULL,
  `userId` int(11) DEFAULT NULL,
  `isSoftwareRenter` int(11) DEFAULT 0,
  `percentageChargedIfNotRenter` int(11) DEFAULT 0,
  `catIdsExecuted` text DEFAULT NULL,
  `name` text DEFAULT NULL,
  `nameEN` text DEFAULT NULL,
  `phone` text DEFAULT NULL,
  `email` text DEFAULT NULL,
  `icon` text DEFAULT NULL,
  `outcall` int(11) DEFAULT 0,
  `incall` int(11) DEFAULT 0,
  `lng` text DEFAULT NULL,
  `lat` text DEFAULT NULL,
  `maxDistance` int(11) DEFAULT 0,
  `pricePerKm` text DEFAULT 0,
  `dateTimeCreated` datetime DEFAULT current_timestamp(),
  `active` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sym_images_types`
--

CREATE TABLE `sym_images_types` (
  `id` int(11) NOT NULL,
  `typeName` text DEFAULT NULL,
  `dateTimeCreated` datetime DEFAULT current_timestamp(),
  `active` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `sym_images_types`
--

INSERT INTO `sym_images_types` (`id`, `typeName`, `dateTimeCreated`, `active`) VALUES
(1, 'businessLogo', '2023-06-22 21:27:13', 1),
(2, 'businessCover', '2023-06-22 21:27:13', 1),
(3, 'portfolio', '2023-06-22 21:27:13', 1);

-- --------------------------------------------------------

--
-- Table structure for table `sym_known_areas`
--

CREATE TABLE `sym_known_areas` (
  `id` int(11) NOT NULL,
  `name` text DEFAULT NULL,
  `urlParam` text DEFAULT NULL,
  `feePercentage` varchar(11) DEFAULT '0',
  `lng` text DEFAULT NULL,
  `lat` text DEFAULT NULL,
  `active` int(11) DEFAULT 1,
  `dateTimeCreated` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sym_profit_stats`
--

CREATE TABLE `sym_profit_stats` (
  `id` int(11) NOT NULL,
  `month` text DEFAULT NULL,
  `monthlyTimeCount` int(11) DEFAULT NULL,
  `monthlyAppointmentsCount` int(11) DEFAULT NULL,
  `sumEurProfit` varchar(300) DEFAULT NULL,
  `dayAnalysis` text DEFAULT NULL,
  `dateTimeUpdated` datetime DEFAULT current_timestamp(),
  `active` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sym_services`
--

CREATE TABLE `sym_services` (
  `id` int(11) NOT NULL,
  `categoryId` int(11) NOT NULL,
  `name` text DEFAULT NULL,
  `nameEn` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `descriptionEn` text DEFAULT NULL,
  `avExecution` text DEFAULT NULL,
  `avExecutionPrint` int(11) DEFAULT 1,
  `avExecutionStandAlone` varchar(20) DEFAULT NULL,
  `price` varchar(20) DEFAULT NULL,
  `priceStandAlone` varchar(20) DEFAULT NULL,
  `icon` text DEFAULT NULL,
  `previewOnServices` int(11) DEFAULT 1,
  `availableOnAreas` text DEFAULT NULL,
  `type` text NOT NULL,
  `short` int(11) DEFAULT 0,
  `active` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `sym_services`
--

INSERT INTO `sym_services` (`id`, `categoryId`, `name`, `nameEn`, `description`, `descriptionEn`, `avExecution`, `avExecutionPrint`, `avExecutionStandAlone`, `price`, `priceStandAlone`, `icon`, `previewOnServices`, `availableOnAreas`, `type`, `short`, `active`) VALUES
(1, 1, 'Κούρεμα', NULL, '', NULL, '35', 1, '45', '8', '10', 'haircut.jpg', 1, '[\"0\"]', 'incall', 100, 1),
(2, 1, 'Ξύρισμα - Τριμάρισμα', NULL, '', NULL, '10', 1, '15', '2', '3', 'beard.jpg', 1, '[\"0\"]', 'incall', 100, 1),
(3, 1, 'Λούσιμο', NULL, '', NULL, '15', 1, '15', '5', '5', 'unnamed-3.jpg', 1, '[\"0\"]', 'incall', 100, 0),
(4, 1, 'Περιποίηση προσώπου', NULL, '', NULL, '30', 1, '30', '5', '8', 'unnamed-4.jpg', 1, '[\"0\"]', 'incall', 100, 0);

-- --------------------------------------------------------

--
-- Table structure for table `sym_services_categories`
--

CREATE TABLE `sym_services_categories` (
  `id` int(11) NOT NULL,
  `name` text DEFAULT NULL,
  `nameEn` text DEFAULT NULL,
  `icon` text DEFAULT NULL,
  `dateTimeCreated` datetime DEFAULT current_timestamp(),
  `short` int(11) DEFAULT 0,
  `active` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `sym_services_categories`
--

INSERT INTO `sym_services_categories` (`id`, `name`, `nameEn`, `icon`, `dateTimeCreated`, `short`, `active`) VALUES
(1, 'Ανδρικό Κούρεμα', 'Mens Haircut', '', '2023-05-17 11:59:37', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sym_settings`
--

CREATE TABLE `sym_settings` (
  `id` int(11) NOT NULL,
  `setting` varchar(99) DEFAULT NULL,
  `value` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `sym_settings`
--

INSERT INTO `sym_settings` (`id`, `setting`, `value`) VALUES
(1, 'website_url', 'https://gm.barbreon.com'),
(2, 'server_ip', '3.64.13.196'),
(4, 'images', '/images/products/'),
(7, 'invoice_email', 'maraslisdim@gmail.com'),
(9, 'secret', 'maraslakos'),
(10, 'appointmentStep', '60'),
(11, 'discountSystem', '1'),
(12, 'appointmentAutoEnd', '0'),
(13, 'locationTrackAppointment', '1'),
(14, 'mainColor', '#2d2d2d'),
(15, 'secondaryColor', '#1d1d1d'),
(16, 'extraColor', '#0dda92'),
(17, 'brandIcon', 'DBlogo.png'),
(18, 'barberIconsPath', '/images/barbers/'),
(19, 'defaultServiceProviderImage', 'barber.png'),
(20, 'autoHolidaysSOS', '0'),
(21, 'storeLocation', 'gr'),
(22, 'sosAddPercentage', '100'),
(23, 'guestCheckout', '1'),
(24, 'userCheckout', '0'),
(25, 'emailIsRequired', '0'),
(26, 'addressIsRequired', '0'),
(27, 'cityIsRequired', '0'),
(28, 'emailPreview', '1'),
(29, 'addressPreview', '0'),
(30, 'cityPreview', '0'),
(31, 'notePreview', '1'),
(32, 'noteRequired', '0'),
(33, 'applicationBranding', '0'),
(34, 'applicationOutcallPercentageFee', '5'),
(35, 'isNormalEmployerCharge', '1'),
(36, 'businessAddress', ''),
(37, 'businessLng', ''),
(38, 'businessLat', ''),
(39, 'businessName', 'BarberON'),
(40, 'businessPhone', ''),
(41, 'businessLogo', 'logo.jpeg'),
(42, 'outcallNeedsConfirm', '0'),
(43, 'showCMSCalendarFullBooks', '0');

-- --------------------------------------------------------

--
-- Table structure for table `sym_shop_menu`
--

CREATE TABLE `sym_shop_menu` (
  `id` int(11) NOT NULL,
  `name` text DEFAULT NULL,
  `nameEn` text DEFAULT NULL,
  `url` text DEFAULT NULL,
  `icon` text DEFAULT NULL,
  `dateTimeCreated` datetime DEFAULT current_timestamp(),
  `active` int(11) DEFAULT 1,
  `short` int(11) DEFAULT 50
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `sym_shop_menu`
--

INSERT INTO `sym_shop_menu` (`id`, `name`, `nameEn`, `url`, `icon`, `dateTimeCreated`, `active`, `short`) VALUES
(1, 'Book Appointment', 'Κλείστε ραντεβού', '/index', '<i class=\"customFA fa fa-scissors\"></i>', '2023-06-05 05:23:12', 1, 48),
(2, 'E-shop', 'E-shop', '/eshop', '<i class=\"customFA fa fa-shopping-cart\" aria-hidden=\"true\"></i>', '2023-06-05 05:23:12', 1, 49),
(3, 'QR Code Share', 'Μοιραστείτε τον κωδικό QR', '/qrcode', '<i class=\"customFA fa fa-qrcode\" aria-hidden=\"true\"></i>', '2023-06-05 05:23:12', 1, 50);

-- --------------------------------------------------------

--
-- Table structure for table `sym_sos_approval_list`
--

CREATE TABLE `sym_sos_approval_list` (
  `id` int(11) NOT NULL,
  `hairCutterId` int(11) DEFAULT NULL,
  `customerId` int(11) DEFAULT NULL,
  `commission` varchar(100) DEFAULT NULL,
  `onDate` varchar(100) DEFAULT NULL,
  `slot` text DEFAULT NULL,
  `serviceId` text DEFAULT NULL,
  `knownAreaId` int(11) DEFAULT 0,
  `address` text DEFAULT NULL,
  `lng` text DEFAULT NULL,
  `lat` text DEFAULT NULL,
  `clientNote` text DEFAULT NULL,
  `serviceNote` text DEFAULT NULL,
  `dateTimeCreated` datetime DEFAULT current_timestamp(),
  `accepted` int(11) DEFAULT 0,
  `declined` int(11) DEFAULT 0,
  `active` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Table structure for table `sym_sos_business_hours`
--

CREATE TABLE `sym_sos_business_hours` (
  `id` int(11) NOT NULL,
  `name` text DEFAULT NULL,
  `nameEn` text DEFAULT NULL,
  `startTime` text DEFAULT NULL,
  `endTime` text DEFAULT NULL,
  `dateTimeUpdated` text DEFAULT NULL,
  `active` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `sym_sos_business_hours`
--

INSERT INTO `sym_sos_business_hours` (`id`, `name`, `nameEn`, `startTime`, `endTime`, `dateTimeUpdated`, `active`) VALUES
(1, 'Δευτέρα', 'Monday', '12:00', '19:00', '2023-06-26 14:29:09', 1),
(2, 'Τρίτη', 'Tuesday', '12:00', '14:00', '2023-06-26 14:29:09', 1),
(3, 'Τετάρτη', 'Wednesday', '10:00', '20:00', '2023-06-26 14:29:09', 1),
(4, 'Πέμπτη', 'Thursday', '10:00', '03:00', '2023-06-26 14:29:09', 0),
(5, 'Παρασκευή', 'Friday', '10:00', '14:00', '2023-06-26 14:29:09', 1),
(6, 'Σάββατο', 'Saturday', '12:00', '18:00', '2023-06-26 14:29:09', 1),
(7, 'Κυριακή', 'Sunday', '11:49', '03:49', '2023-06-26 14:29:09', 0);

-- --------------------------------------------------------

--
-- Table structure for table `sym_users`
--

CREATE TABLE `sym_users` (
  `id` int(11) NOT NULL,
  `hairCutterId` int(11) DEFAULT 0,
  `email` varchar(50) DEFAULT NULL,
  `nickname` text DEFAULT NULL,
  `password` varchar(99) DEFAULT NULL,
  `role` int(11) DEFAULT NULL,
  `latest_login` varchar(11) DEFAULT NULL,
  `last_login` varchar(11) DEFAULT NULL,
  `todayProfits` text DEFAULT NULL,
  `image` text DEFAULT NULL,
  `isLogged` int(11) DEFAULT NULL,
  `active` int(11) DEFAULT NULL,
  `lastMove` text DEFAULT 'NULL',
  `balancesPreview` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sym_business_hours`
--
ALTER TABLE `sym_business_hours`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sym_business_hours_id_uindex` (`id`);

--
-- Indexes for table `sym_business_images`
--
ALTER TABLE `sym_business_images`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sym_business_images_id_uindex` (`id`);

--
-- Indexes for table `sym_customers`
--
ALTER TABLE `sym_customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sym_customers_id_uindex` (`id`),
  ADD UNIQUE KEY `sym_customers_phone_uindex` (`phone`);

--
-- Indexes for table `sym_discounts_rules`
--
ALTER TABLE `sym_discounts_rules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sym_discounts_rules_id_uindex` (`id`);

--
-- Indexes for table `sym_haircuts`
--
ALTER TABLE `sym_haircuts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sym_haircuts_id_uindex` (`id`);

--
-- Indexes for table `sym_hair_cutters`
--
ALTER TABLE `sym_hair_cutters`
  ADD UNIQUE KEY `sym_hair_cutters_id_uindex` (`id`);

--
-- Indexes for table `sym_images_types`
--
ALTER TABLE `sym_images_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sym_images_types_id_uindex` (`id`);

--
-- Indexes for table `sym_known_areas`
--
ALTER TABLE `sym_known_areas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sym_known_areas_id_uindex` (`id`);

--
-- Indexes for table `sym_profit_stats`
--
ALTER TABLE `sym_profit_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sym_profit_stats_id_uindex` (`id`);

--
-- Indexes for table `sym_services`
--
ALTER TABLE `sym_services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sym_services_id_uindex` (`id`);

--
-- Indexes for table `sym_services_categories`
--
ALTER TABLE `sym_services_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sym_services_categories_id_uindex` (`id`);

--
-- Indexes for table `sym_settings`
--
ALTER TABLE `sym_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sym_settings_id_uindex` (`id`);

--
-- Indexes for table `sym_shop_menu`
--
ALTER TABLE `sym_shop_menu`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sym_shop_menu_id_uindex` (`id`);

--
-- Indexes for table `sym_sos_approval_list`
--
ALTER TABLE `sym_sos_approval_list`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sym_sos_approval_list_id_uindex` (`id`);

--
-- Indexes for table `sym_sos_business_hours`
--
ALTER TABLE `sym_sos_business_hours`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sym_sos_business_hours_id_uindex` (`id`);

--
-- Indexes for table `sym_users`
--
ALTER TABLE `sym_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sym_users_id_uindex` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sym_business_hours`
--
ALTER TABLE `sym_business_hours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `sym_business_images`
--
ALTER TABLE `sym_business_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT for table `sym_customers`
--
ALTER TABLE `sym_customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sym_discounts_rules`
--
ALTER TABLE `sym_discounts_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT for table `sym_haircuts`
--
ALTER TABLE `sym_haircuts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sym_hair_cutters`
--
ALTER TABLE `sym_hair_cutters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sym_images_types`
--
ALTER TABLE `sym_images_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT for table `sym_known_areas`
--
ALTER TABLE `sym_known_areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sym_profit_stats`
--
ALTER TABLE `sym_profit_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sym_services`
--
ALTER TABLE `sym_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT for table `sym_services_categories`
--
ALTER TABLE `sym_services_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT for table `sym_settings`
--
ALTER TABLE `sym_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT for table `sym_shop_menu`
--
ALTER TABLE `sym_shop_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT for table `sym_sos_approval_list`
--
ALTER TABLE `sym_sos_approval_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT for table `sym_sos_business_hours`
--
ALTER TABLE `sym_sos_business_hours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT for table `sym_users`
--
ALTER TABLE `sym_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
