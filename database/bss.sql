-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 08, 2025 at 05:54 PM
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
-- Database: `bss`
--

-- --------------------------------------------------------

--
-- Table structure for table `accesspoint`
--

CREATE TABLE `accesspoint` (
  `id` int(11) NOT NULL,
  `asset_tag` varchar(100) DEFAULT NULL,
  `property_equipment` varchar(100) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `assigned_person` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `unit_price` decimal(12,2) DEFAULT NULL,
  `date_acquired` date DEFAULT NULL,
  `useful_life` varchar(100) DEFAULT NULL,
  `hardware_specifications` text DEFAULT NULL,
  `software_specifications` text DEFAULT NULL,
  `high_value_ics_no` varchar(100) DEFAULT NULL,
  `inventory_item_no` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accesspoint`
--

INSERT INTO `accesspoint` (`id`, `asset_tag`, `property_equipment`, `department`, `assigned_person`, `location`, `unit_price`, `date_acquired`, `useful_life`, `hardware_specifications`, `software_specifications`, `high_value_ics_no`, `inventory_item_no`, `remarks`) VALUES
(1, 'ICT-LC_4LTSPF-AP001', 'Wireless Accesspoint', 'PFMO', 'ICT Services', 'LTSB/4th floor/PFMO', 5400.00, '2022-10-27', NULL, '2.4 GHz', NULL, 'LI37-HV-ICTE-2022-0037', NULL, 'Working Unit'),
(2, 'IT-002', 'Wireless Accesspoint', 'CICS', NULL, 'LTSB/4th floor/CICS', 5400.00, '2022-10-27', NULL, '2.4 GHz', NULL, 'LI37-HV-ICTE-2022-0041', NULL, 'Working Unit'),
(3, 'ICT-LC_1VMBGS-AP001', 'Wireless Accesspoint', 'GSO', 'GSO', 'VMB/1st floor/GSO', NULL, NULL, NULL, 'Large', NULL, 'N/A', NULL, 'Working Unit'),
(4, 'ICT-LC_2VMBAA-AP001', 'Wireless Accesspoint', 'VCAA', 'VCAA', 'VMB/2nd Floor/VCAA', NULL, NULL, NULL, 'Small', NULL, 'N/A', NULL, 'Working Unit'),
(5, 'ICT-LC_2VMBAC-AP001', 'Wireless Accesspoint', 'ACCREDITATION', 'ACCREDITATION', 'VMB/2nd Floor/AC', NULL, NULL, NULL, 'Large', NULL, 'N/A', NULL, 'Working Unit'),
(6, 'ICT-LC_2VMBNS-AP001', 'Wireless Accesspoint', 'NSTP', 'NSTP', 'VMB/2nd Floor/NSTP', 5400.00, '2022-10-27', NULL, 'Small', '2.4 GHz', 'LI37-HV-ICTE-2022-0038', NULL, 'Working Unit'),
(7, 'ICT-LC_2VMBCT-AP001', 'Wireless Accesspoint', 'CTE', 'Dr. Emerita T. Generoso', 'VMB/3rd Floor/CET', NULL, NULL, NULL, 'Small', NULL, 'N/A', NULL, 'Working Unit'),
(8, 'ICT-LC_VMBSP-AP001', 'Wireless Accesspoint', 'SPORTS', 'SPORTS', 'VMB/1st Floor/SPORTS', NULL, NULL, NULL, 'Small', NULL, 'N/A', NULL, 'Working Unit'),
(9, 'ICT-LC_VMBCE-AP001', 'Wireless Accesspoint', 'CTE', 'CTE', 'VMB/3rd Floor/CTE', NULL, NULL, NULL, 'Small', NULL, 'N/A', NULL, 'Working Unit'),
(10, 'ICT-LC_4VMBCA-AP001', 'Wireless Accesspoint', 'CAS', 'CAS', 'VMB/4th Floor/CAS', NULL, NULL, NULL, 'Large', NULL, 'N/A', NULL, 'Working Unit'),
(11, 'ICT-LC_5VMBRE-AP002', 'Wireless Accesspoint', 'RESEARCH', 'RESEARCH', 'VMB/5th Floor/RESEARCH', NULL, NULL, NULL, 'Large', NULL, 'N/A', NULL, 'Working Unit'),
(12, 'ICT-LC_3GZBOJ-AP001', 'Wireless Accesspoint', 'OJT', 'OJT', 'GZB/2nd Floor/OJT', 5400.00, '2022-10-27', NULL, 'Large', '2.4 GHz', 'LI37-HV-ICTE-2022-0035', NULL, 'Working Unit'),
(13, 'ICT-LC_3GZBOS-AP001', 'Wireless Accesspoint', 'OSD', 'OSD', 'GZB/3rd Floor/OSD', 5400.00, '2018-02-07', NULL, 'Large', 'UAP-AC-LITE', '0422-18', NULL, 'Working Unit'),
(14, 'ICT-LC_1FCSE-AP001', 'Wireless Accesspoint', 'SECURITY', 'SECURITY', '1st Floor/SECURITY', NULL, NULL, NULL, 'Large', NULL, 'N/A', NULL, 'Working Unit'),
(15, 'ICT-LI_2ABBHS-AP001', 'Wireless Accesspoint', 'HS', 'HS', 'ABB/2nd Floor/HS', NULL, NULL, NULL, 'Large', NULL, 'N/A', NULL, 'Working Unit'),
(16, 'ICT-LI_3ABBCA-AP001', 'Wireless Accesspoint', 'CABE', 'CABE', 'ABB/3rd Floor/CABE', NULL, NULL, NULL, 'Small', NULL, 'N/A', NULL, 'Working Unit'),
(17, 'ICT-LI_4ABBOG-AP001', 'Wireless Accesspoint', 'OGC', 'OGC', 'ABB/4th Floor/OGC', NULL, NULL, NULL, 'Large', NULL, 'N/A', NULL, 'Working Unit'),
(18, 'ICT-LI_5ABBLI-AP001', 'Wireless Accesspoint', 'LIBRARY', 'LIBRARY', 'ABB/5th Floor/LIBRARY', NULL, NULL, NULL, 'Large', NULL, 'N/A', NULL, 'Working Unit'),
(19, 'ICT-LI_5ABBLI-AP002', 'Wireless Accesspoint', 'LIBRARY', 'LIBRARY', 'ABB/5th Floor/LIBRARY', NULL, NULL, NULL, 'Large', NULL, 'N/A', NULL, 'Working Unit'),
(20, 'ICT-LC_4LTSPF-AP001', 'Wireless Accesspoint', 'PFMO', 'ICT Services', 'LTSB/4th floor/PFMO', 5400.00, '2022-10-27', NULL, '2.4 GHz', NULL, 'LI37-HV-ICTE-2022-0037', NULL, 'Working Unit'),
(21, 'IT-002', 'Wireless Accesspoint', 'CICS', NULL, 'LTSB/4th floor/CICS', 5400.00, '2022-10-27', NULL, '2.4 GHz', NULL, 'LI37-HV-ICTE-2022-0041', NULL, 'Working Unit'),
(22, 'ICT-LC_1VMBGS-AP001', 'Wireless Accesspoint', 'GSO', 'GSO', 'VMB/1st floor/GSO', NULL, NULL, NULL, 'Large', NULL, NULL, NULL, 'Working Unit'),
(23, 'ICT-LC_2VMBAA-AP001', 'Wireless Accesspoint', 'VCAA', 'VCAA', 'VMB/2nd Floor/VCAA', NULL, NULL, NULL, 'Small', NULL, NULL, NULL, 'Working Unit'),
(24, 'ICT-LC_2VMBAC-AP001', 'Wireless Accesspoint', 'ACCREDITATION', 'ACCREDITATION', 'VMB/2nd Floor/AC', NULL, NULL, NULL, 'Large', NULL, NULL, NULL, 'Working Unit'),
(25, 'ICT-LC_2VMBNS-AP001', 'Wireless Accesspoint', 'NSTP', 'NSTP', 'VMB/2nd Floor/NSTP', 5400.00, '2022-10-27', NULL, 'Small', '2.4 GHz', 'LI37-HV-ICTE-2022-0038', NULL, 'Working Unit'),
(26, 'ICT-LC_2VMBCT-AP001', 'Wireless Accesspoint', 'CTE', 'Dr. Emerita T. Generoso', 'VMB/3rd Floor/CET', NULL, NULL, NULL, 'Small', NULL, NULL, NULL, 'Working Unit'),
(27, 'ICT-LC_VMBSP-AP001', 'Wireless Accesspoint', 'SPORTS', 'SPORTS', 'VMB/1st Floor/SPORTS', NULL, NULL, NULL, 'Small', NULL, NULL, NULL, 'Working Unit'),
(28, 'ICT-LC_VMBCE-AP001', 'Wireless Accesspoint', 'CTE', 'CTE', 'VMB/3rd Floor/CTE', NULL, NULL, NULL, 'Small', NULL, NULL, NULL, 'Working Unit'),
(29, 'ICT-LC_4VMBCA-AP001', 'Wireless Accesspoint', 'CAS', 'CAS', 'VMB/4th Floor/CAS', NULL, NULL, NULL, 'Large', NULL, NULL, NULL, 'Working Unit'),
(30, 'ICT-LC_5VMBRE-AP002', 'Wireless Accesspoint', 'RESEARCH', 'RESEARCH', 'VMB/5th Floor/RESEARCH', NULL, NULL, NULL, 'Large', NULL, NULL, NULL, 'Working Unit'),
(31, 'ICT-LC_3GZBOJ-AP001', 'Wireless Accesspoint', 'OJT', 'OJT', 'GZB/2nd Floor/OJT', 5400.00, '2022-10-27', NULL, 'Large', '2.4 GHz', 'LI37-HV-ICTE-2022-0035', NULL, 'Working Unit'),
(32, 'ICT-LC_3GZBOS-AP001', 'Wireless Accesspoint', 'OSD', 'OSD', 'GZB/3rd Floor/OSD', 5400.00, '2018-02-07', NULL, 'Large', 'UAP-AC-LITE', '0422-18', NULL, 'Working Unit'),
(33, 'ICT-LC_1FCSE-AP001', 'Wireless Accesspoint', 'SECURITY', 'SECURITY', '1st Floor/SECURITY', NULL, NULL, NULL, 'Large', NULL, NULL, NULL, 'Working Unit'),
(34, 'ICT-LI_2ABBHS-AP001', 'Wireless Accesspoint', 'HS', 'HS', 'ABB/2nd Floor/HS', NULL, NULL, NULL, 'Large', NULL, NULL, NULL, 'Working Unit'),
(35, 'ICT-LI_3ABBCA-AP001', 'Wireless Accesspoint', 'CABE', 'CABE', 'ABB/3rd Floor/CABE', NULL, NULL, NULL, 'Small', NULL, NULL, NULL, 'Working Unit'),
(36, 'ICT-LI_4ABBOG-AP001', 'Wireless Accesspoint', 'OGC', 'OGC', 'ABB/4th Floor/OGC', NULL, NULL, NULL, 'Large', NULL, NULL, NULL, 'Working Unit'),
(37, 'ICT-LI_5ABBLI-AP001', 'Wireless Accesspoint', 'LIBRARY', 'LIBRARY', 'ABB/5th Floor/LIBRARY', NULL, NULL, NULL, 'Large', NULL, NULL, NULL, 'Working Unit'),
(38, 'ICT-LI_5ABBLI-AP002', 'Wireless Accesspoint', 'LIBRARY', 'LIBRARY', 'ABB/5th Floor/LIBRARY', NULL, NULL, NULL, 'Large', NULL, NULL, NULL, 'Working Unit');

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(255) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `admin_id`, `admin_name`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 4, 'sasasa', 'Login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-26 14:02:48'),
(2, 4, NULL, 'Task', 'Assigned a task to4', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-26 14:06:49'),
(3, 4, 'sasasa', 'Task', 'Assigned a task to4', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-26 14:07:11'),
(4, 4, 'sasasa', 'Task', 'Deleted a task to', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-26 14:07:26'),
(5, 4, 'sasasa', 'Task', 'Deleted a task.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-26 14:07:40'),
(6, 4, 'sasasa', 'Login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-26 15:35:33'),
(7, 4, 'sasasa', 'Login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-26 15:55:32'),
(8, 4, 'sasasa', 'Login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-26 15:55:38'),
(9, 4, 'sasasa', 'Generated Report', 'ICT SERVICE REQUEST FORM', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-26 16:17:16'),
(10, 4, 'sasasa', 'Generated Report', 'RWEBSITE POSTING REQUEST FORM', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-26 16:19:56'),
(11, 4, 'sasasa', 'Login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-26 16:38:57'),
(12, 4, 'sasasa', 'Login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-26 16:39:36'),
(13, 4, 'sasasa', 'Login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-26 16:39:53'),
(14, 5, 'sample', 'Login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-26 16:39:58'),
(15, 5, 'sample', 'Logout', 'Admin logged out', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-26 16:40:23'),
(16, 4, 'sasasa', 'Login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-26 16:40:27'),
(17, 4, 'sasasa', 'Login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 11:48:55'),
(18, 4, 'sasasa', 'Generated Report', 'Preventive Maintenance of ICT-Related Equipment Index Card', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 11:50:25'),
(19, 4, 'sasasa', 'Generated Report', 'RWEBSITE POSTING REQUEST FORM', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 11:50:36'),
(20, 4, 'sasasa', 'Generated Report', 'EXISTING INTERNET SERVICE PROVIDER\'S EVALUATION', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 11:50:54'),
(21, 4, 'sasasa', 'Generated Report', 'Preventive Maintenance of ICT-Related Equipment Index Card', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 11:51:07'),
(22, 4, 'sasasa', 'Task', 'Assigned a task to6', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 12:03:43'),
(23, 4, 'sasasa', 'Task', 'Assigned a task to6', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 12:10:31'),
(24, 4, 'sasasa', 'Task', 'Assigned a task to6', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 12:10:51'),
(25, 4, 'sasasa', 'Task', 'Assigned a task to6', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 12:11:05'),
(26, 4, 'sasasa', 'Task', 'Assigned a task to6', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 12:11:26'),
(27, 4, 'sasasa', 'Task', 'Assigned a task to user ID 6', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 12:15:14'),
(28, 4, 'sasasa', 'Task', 'Assigned a task to user ID 6', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 12:15:24'),
(29, 4, 'sasasa', 'Task', 'Assigned a task to user ID 5', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 12:18:57'),
(30, 4, 'sasasa', 'Task', 'Assigned a task to user ID 5', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 12:20:02'),
(31, 4, 'sasasa', 'Task', 'Assigned a task to user ID 5', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 12:20:14'),
(32, 4, 'sasasa', 'Task', 'Assigned a task to user ID 5', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 12:20:39'),
(33, 4, 'sasasa', 'Task', 'Assigned a task to user ID 7', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 12:25:25'),
(34, 7, 'ccc', 'User', 'User logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 12:38:16'),
(35, 7, 'ccc', 'User', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 12:39:21'),
(36, 7, 'ccc', 'User', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 12:39:38'),
(37, 4, 'sasasa', 'Logout', 'Admin logged out', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 14:05:52'),
(38, 4, 'sasasa', 'Login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 14:05:57'),
(39, 4, 'sasasa', 'Logout', 'Admin logged out', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 14:06:00'),
(40, 4, 'sasasa', 'Login', 'Admin logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0', '2025-09-27 14:15:57'),
(41, 2, 'Emmanuel M. Dimaculangan', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 03:25:02'),
(42, 2, 'Emmanuel M. Dimaculangan', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 03:28:09'),
(43, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 03:28:25'),
(44, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 04:11:48'),
(45, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 04:13:34'),
(46, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 04:13:58'),
(47, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 04:15:59'),
(48, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 04:36:10'),
(49, 8, 'Emman', 'Task', 'Assigned a task to user ID 9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 04:37:16'),
(50, 8, 'Emman', 'Task', 'Assigned a task to user ID 10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 04:38:47'),
(51, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 05:42:23'),
(52, 9, 'sir', 'User', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 05:44:06'),
(53, 8, 'Emman', 'Generated Report', 'EXISTING INTERNET SERVICE PROVIDER\'S EVALUATION', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 05:52:19'),
(54, 8, 'Emman', 'Generated Report', 'PREVENTIVE MAINTENANCE PLAN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 06:00:10'),
(55, 8, 'Emman', 'Generated Report', 'Preventive Maintenance of ICT-Related Equipment Index Card', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 06:01:28'),
(56, 8, 'Emman', 'Generated Report', 'ICT SERVICE REQUEST FORM', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 06:02:18'),
(57, 8, 'Emman', 'Generated Report', 'REQUEST FOR POSTING OF ANNOUNCEMENTS / GREETINGS', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 06:02:44'),
(58, 8, 'Emman', 'Task', 'Assigned a task to user ID 9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 06:08:52'),
(59, 9, 'sir', 'User', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 06:10:50'),
(60, 8, 'Emman', 'Task', 'Assigned a task to user ID 9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 06:15:01'),
(61, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 07:22:57'),
(62, 9, 'sir', 'User', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 07:23:31'),
(63, 8, 'Emman', 'Generated Report', 'Preventive Maintenance of ICT-Related Equipment Index Card', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 07:25:19'),
(64, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 07:36:17'),
(65, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 07:36:45'),
(66, 8, 'Emman', 'Task', 'Assigned a task to user ID 9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 07:38:28'),
(67, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 07:14:39'),
(68, 8, 'Emman', 'Task', 'Assigned a task to user ID 10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 07:16:00'),
(69, 9, 'sir', 'User', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 07:17:04'),
(70, 8, 'Emman', 'Generated Report', 'PREVENTIVE MAINTENANCE PLAN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 07:18:19'),
(71, 8, 'Emman', 'Task', 'Assigned a task to user ID 9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 07:48:14'),
(72, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:17:17'),
(73, 9, 'sir', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:29:23'),
(74, 9, 'sir', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:32:13'),
(75, 9, 'sir', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:32:35'),
(76, 9, 'sir', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:33:50'),
(77, 9, 'sir', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:34:04'),
(78, 9, 'sir', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:34:09'),
(79, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:34:25'),
(80, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:34:44'),
(81, 9, 'sir', 'User', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:35:11'),
(82, 9, 'sir', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:38:02'),
(83, 9, 'sir', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:41:27'),
(84, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:44:01'),
(85, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:44:12'),
(86, 9, 'sir', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:44:24'),
(87, 9, 'sir', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:46:05'),
(88, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:46:24'),
(89, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:46:28'),
(90, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:46:36'),
(91, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:46:39'),
(92, 9, 'sir', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:46:48'),
(93, 9, 'sir', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:47:33'),
(94, 9, 'sir', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:47:40'),
(95, 9, 'sir', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:49:30'),
(96, 9, 'sir', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:50:00'),
(97, 9, 'sir', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:52:14'),
(98, 9, 'sir', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:52:31'),
(99, 9, 'sir', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:53:41'),
(100, 9, 'sir', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:53:56'),
(101, 9, 'sir', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:58:11'),
(102, 9, 'sir', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:58:22'),
(103, 9, 'sir', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 09:02:42'),
(104, 9, 'sir', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 09:04:23'),
(105, 9, 'sir', 'User', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 09:08:16'),
(106, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 09:12:25'),
(107, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 09:16:57'),
(108, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 11:01:56'),
(109, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 11:02:36'),
(110, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 11:03:17'),
(111, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 11:03:54'),
(112, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 11:06:22'),
(113, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 11:11:21'),
(114, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 11:11:35'),
(115, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 11:11:55'),
(116, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 11:12:34'),
(117, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 11:13:42'),
(118, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 11:13:57'),
(119, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 11:14:25'),
(120, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 11:19:04'),
(121, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 11:23:05'),
(122, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 11:26:18'),
(123, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 12:12:33'),
(124, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 12:14:45'),
(125, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 12:14:53'),
(126, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 12:20:22'),
(127, 2, 'Emmanuel M. Dimaculangan', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 12:22:12'),
(128, 2, 'Emmanuel M. Dimaculangan', 'Task', 'Assigned a task to user ID 9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 12:22:41'),
(129, 2, 'Emmanuel M. Dimaculangan', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 12:47:14'),
(130, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 12:47:45'),
(131, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 13:09:45'),
(132, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 13:09:56'),
(133, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 13:14:59'),
(134, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 13:15:13'),
(135, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 13:16:32'),
(136, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 13:16:54'),
(137, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 13:17:20'),
(138, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 13:18:55'),
(139, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 13:20:57'),
(140, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 13:21:13'),
(141, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 13:23:16'),
(142, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 13:24:16'),
(143, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 13:31:07'),
(144, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 13:31:22'),
(145, 9, 'sir', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 13:39:32'),
(146, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 13:40:54'),
(147, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 13:45:34'),
(148, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 10:23:52'),
(149, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 10:31:36'),
(150, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 10:31:48'),
(151, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 10:33:08'),
(152, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 10:33:41'),
(153, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 10:33:58'),
(154, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 03:38:31'),
(155, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 04:50:11'),
(156, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 05:39:54'),
(157, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 05:42:01'),
(158, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 05:42:52'),
(159, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 05:42:54'),
(160, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:19:26'),
(161, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:19:28'),
(162, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:22:16'),
(163, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:22:20'),
(164, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:25:23'),
(165, 8, 'Emman', 'Task', 'Deleted a task.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:28:12'),
(166, 8, 'Emman', 'Task', 'Deleted a task.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:28:17'),
(167, 8, 'Emman', 'Task', 'Deleted a task.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:28:20'),
(168, 8, 'Emman', 'Task', 'Deleted a task.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:28:22'),
(169, 8, 'Emman', 'Task', 'Deleted a task.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:28:27'),
(170, 8, 'Emman', 'Task', 'Deleted a task.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:28:29'),
(171, 8, 'Emman', 'Task', 'Deleted a task.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:28:32'),
(172, 8, 'Emman', 'Task', 'Deleted a task.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:28:34'),
(173, 8, 'Emman', 'Task', 'Deleted a task.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:28:37'),
(174, 8, 'Emman', 'Task', 'Deleted a task.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:28:39'),
(175, 8, 'Emman', 'Task', 'Deleted a task.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:28:41'),
(176, 8, 'Emman', 'Task', 'Deleted a task.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:28:44'),
(177, 8, 'Emman', 'Task', 'Deleted a task.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:28:46'),
(178, 8, 'Emman', 'Task', 'Deleted a task.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:28:48'),
(179, 8, 'Emman', 'Task', 'Deleted a task.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:28:50'),
(180, 8, 'Emman', 'Task', 'Deleted a task.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:28:53'),
(181, 8, 'Emman', 'Task', 'Deleted a task.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:28:55'),
(182, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:41:21'),
(183, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:41:35'),
(184, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 06:41:37'),
(185, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 07:58:12'),
(186, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-04 08:03:58'),
(187, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-06 13:36:15'),
(188, 8, 'Emman', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 03:17:15'),
(189, 15, 'Emmanuel M. Dimaculangan', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 08:13:21'),
(190, 15, 'Emmanuel M. Dimaculangan', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 08:13:23'),
(191, 15, 'Emmanuel M. Dimaculangan', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 08:13:51'),
(192, 15, 'Emmanuel M. Dimaculangan', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 08:15:09'),
(193, 15, 'Emmanuel M. Dimaculangan', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 08:42:12'),
(194, 15, 'Emmanuel M. Dimaculangan', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 08:42:36'),
(195, 16, 'barron', 'Generated Report', 'Preventive Maintenance of ICT-Related Equipment Index Card', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 09:01:58'),
(196, 16, 'barron', 'Generated Report', 'Preventive Maintenance of ICT-Related Equipment Index Card', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 09:02:04'),
(197, 16, 'barron', 'Generated Report', 'PREVENTIVE MAINTENANCE PLAN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 10:06:06'),
(198, 16, 'barron', 'Generated Report', 'PREVENTIVE MAINTENANCE PLAN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 10:06:35'),
(199, 16, 'barron', 'Generated Report', 'PREVENTIVE MAINTENANCE PLAN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 10:09:46'),
(200, 16, 'barron', 'Generated Report', 'PREVENTIVE MAINTENANCE PLAN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 10:17:33'),
(201, 16, 'barron', 'Generated Report', 'PREVENTIVE MAINTENANCE PLAN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 10:20:50'),
(202, 15, 'Emmanuel M. Dimaculangan', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 10:21:53'),
(203, 15, 'Emmanuel M. Dimaculangan', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 10:26:51'),
(204, 16, 'barron', 'Generated Report', 'PREVENTIVE MAINTENANCE PLAN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 10:27:11'),
(205, 15, 'Emmanuel M. Dimaculangan', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 10:29:50'),
(206, 15, 'Emmanuel M. Dimaculangan', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 10:31:30'),
(207, 16, 'barron', 'Generated Report', 'PREVENTIVE MAINTENANCE PLAN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 10:32:01'),
(208, 15, 'Emmanuel M. Dimaculangan', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 11:30:08'),
(209, 8, 'Emman', 'Login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 15:32:37'),
(210, 8, 'Emman', 'Generated Report', 'PREVENTIVE MAINTENANCE PLAN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 15:32:46'),
(211, 8, 'Emman', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 15:40:29'),
(212, 15, 'Emmanuel M. Dimaculangan', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 15:40:54'),
(213, 15, 'Emmanuel M. Dimaculangan', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 15:41:00'),
(214, 16, 'barron', 'Generated Report', 'PREVENTIVE MAINTENANCE PLAN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 15:41:18'),
(215, 16, 'barron', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 15:45:15'),
(216, 15, 'Emmanuel M. Dimaculangan', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 15:45:38'),
(217, 15, 'Emmanuel M. Dimaculangan', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 15:47:28'),
(218, 16, 'barron', 'Generated Report', 'PREVENTIVE MAINTENANCE PLAN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 15:48:25'),
(219, 16, 'barron', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 15:48:40'),
(220, 15, 'Emmanuel M. Dimaculangan', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 15:48:53'),
(221, 15, 'Emmanuel M. Dimaculangan', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 15:49:05'),
(222, 15, 'Emmanuel M. Dimaculangan', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 15:49:17'),
(223, 15, 'Emmanuel M. Dimaculangan', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 16:03:11'),
(224, 16, 'barron', 'Generated Report', 'PREVENTIVE MAINTENANCE PLAN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 16:03:26'),
(225, 16, 'barron', 'Generated Report', 'PREVENTIVE MAINTENANCE PLAN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-07 16:04:22'),
(226, 16, 'barron', 'Generated Report', 'PREVENTIVE MAINTENANCE PLAN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 02:32:07'),
(227, 16, 'barron', 'Generated Report', 'SYSTEM REQUEST FORM', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 02:34:11'),
(228, 15, 'Emmanuel M. Dimaculangan', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 02:35:57'),
(229, 15, 'Emmanuel M. Dimaculangan', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 02:39:35'),
(230, 15, 'Emmanuel M. Dimaculangan', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 06:34:09'),
(231, 15, 'Emmanuel M. Dimaculangan', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 06:34:25'),
(232, 15, 'Emmanuel M. Dimaculangan', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 06:36:26'),
(233, 15, 'Emmanuel M. Dimaculangan', 'Add Technician', 'Added technician 22-34593@g.batstate-u.edu.ph', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 06:37:12'),
(234, 15, 'Emmanuel M. Dimaculangan', 'Logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 06:38:30'),
(235, 17, 'carlos', 'User', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 06:40:12'),
(236, 17, 'carlos', 'User', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 06:40:56'),
(237, 17, 'carlos', 'Task', 'Assigned a task to user ID 17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 06:44:37'),
(238, 17, 'carlos', 'User', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 06:52:21'),
(239, 17, 'carlos', 'User', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 06:55:25'),
(240, 15, 'Emmanuel M. Dimaculangan', 'Login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 07:08:20'),
(241, 16, 'barron', 'Generated Report', 'PREVENTIVE MAINTENANCE PLAN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 15:24:10'),
(242, 16, 'barron', 'Generated Report', 'PREVENTIVE MAINTENANCE PLAN', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 15:32:02'),
(243, 16, 'barron', 'Generated Report', 'Preventive Maintenance of ICT-Related Equipment Index Card', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-08 15:51:34');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `building` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `building`, `location`, `created_at`) VALUES
(1, 'College of Accountancy, Business & Economics', 'Main Building', 'Ground Floor', '2025-07-27 00:07:33'),
(2, 'College of Arts and Sciences', 'Science Building', 'Second Floor', '2025-07-27 00:07:33'),
(3, 'College of Engineering', 'Engineering Building', 'First Floor', '2025-07-27 00:07:33'),
(4, 'College of Engineering Technology (CIT)', 'CIT Building', 'Third Floor', '2025-07-27 00:07:33'),
(5, 'College of Informatics & Computing Sciences', 'Computer Science Building', 'Fourth Floor', '2025-07-27 00:07:33');

-- --------------------------------------------------------

--
-- Table structure for table `desktop`
--

CREATE TABLE `desktop` (
  `id` int(11) NOT NULL,
  `asset_tag` varchar(100) DEFAULT NULL,
  `property_equipment` varchar(100) DEFAULT NULL,
  `department_office` varchar(255) DEFAULT NULL,
  `assigned_person` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `unit_price` decimal(12,2) DEFAULT NULL,
  `date_acquired` date DEFAULT NULL,
  `useful_life` varchar(100) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `hard_drive` varchar(255) DEFAULT NULL,
  `ram` varchar(50) DEFAULT NULL,
  `gpu` varchar(255) DEFAULT NULL,
  `keyboard` varchar(100) DEFAULT NULL,
  `mouse` varchar(100) DEFAULT NULL,
  `avr` varchar(100) DEFAULT NULL,
  `processor` varchar(255) DEFAULT NULL,
  `operating_system` varchar(100) DEFAULT NULL,
  `licensed` enum('Yes','No') DEFAULT NULL,
  `software_applications` text DEFAULT NULL,
  `high_value_ics_no` varchar(100) DEFAULT NULL,
  `inventory_item_no` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `desktop`
--

INSERT INTO `desktop` (`id`, `asset_tag`, `property_equipment`, `department_office`, `assigned_person`, `location`, `unit_price`, `date_acquired`, `useful_life`, `model`, `hard_drive`, `ram`, `gpu`, `keyboard`, `mouse`, `avr`, `processor`, `operating_system`, `licensed`, `software_applications`, `high_value_ics_no`, `inventory_item_no`, `remarks`) VALUES
(1, 'ICT-LC_4LTSEM-PC001', 'Desktop Computer', 'EMU', 'Jemalyn Canaberal', 'LTSB/4th floor/EMU', 38999.00, '2021-12-14', NULL, 'Acer TC-875', 'HDD 1TB WDC WD10EZEX-21WN4A0; SSD 128GB SanDisk Sd9SN8W128G1014', '8GB', 'NVIDIA GeForce GT 730', 'Acer', 'HP', 'Secure', NULL, 'Windows 11 Home Single Language', 'Yes', 'Microsoft Office (Licensed)', NULL, 'LI44-HV-ICTE-2021-0103', 'Working Unit'),
(2, 'ICT-LC_4LTSPF-PC002', 'Desktop Computer', 'PFMO', 'Laiza Silang', 'LTSB/4th floor/PFMO', 42500.00, '2017-03-28', NULL, 'HP 280G4 MT', 'HDD Toshiba DT01ACA100', '8GB', 'Intel UHD Graphics 630', 'HP', 'Logitech', 'Echo Power', 'Intel Core i5-8400 2.80GHz', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Not Licensed)', NULL, 'LI12-HV-ICTE-2018-0005', 'Working Unit'),
(3, 'ICT-LC_4LTSPF-PC001', 'Desktop Computer', 'PFMO', 'Gerald Foliente', 'LTSB/4th floor/PFMO', 42500.00, '2018-11-09', NULL, 'HP 280G4 MT', 'HDD Toshiba DT01ACA100', '8GB', 'Intel UHD Graphics 630', 'HP', 'HP', 'Echo Power', 'Intel Core i5-7400 2.80GHz', 'Windows 10 Enterprise', 'No', 'Microsoft Office (Not Licensed)', NULL, 'LI12-HV-ICTE-2018-0005', 'Working Unit'),
(4, 'ICT-LC_4LTSEM-PC002', 'Desktop Computer', 'EMU', 'Maricel Lacbay', 'LTSB/4th floor/EMU', 42500.00, '2018-11-09', NULL, 'HP 280G4 MT', 'HDD Toshiba DT01ACA100 1TB', '8GB', 'Intel UHD Graphics 630', 'HP', 'Acer', NULL, NULL, 'Windows 10 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, 'LI08-HV-ICTE-2018-0012', 'Working Unit'),
(5, 'ICT-LC_4LTSPR-PC001', 'Desktop Computer', 'Procurement', 'Limuel', 'LTSB/4th floor/Procurement', 38500.00, '2021-02-03', NULL, 'Xitrix DeskFrame GL1 H510', 'HDD 1TB ST1000DM010-2EP102', '16GB DIMM', 'NVIDIA GeForce GT 710', 'Xitrix', 'Xitrix', 'Secure', 'Intel Core i5-9400F 2.9GHz', 'Windows 11 Pro', 'Yes', NULL, NULL, 'CA-001-21', 'Working Unit (AVR replaced by UPS from PSO)'),
(6, 'ICT-LC_4LTSPR-PC002', 'Desktop Computer', 'Procurement', 'Evangeline Garcia', 'LTSB/4th floor/Procurement', 47880.00, '2020-06-24', NULL, 'HIP', 'HDD 1TB ST1000DM010-2EP102', '8GB DIMM', 'Intel UHD Graphics 630', 'A4TECH', 'A4TECH', 'Secure', 'Intel Core i7-9700K 3.6GHz', 'Windows 10 Pro', 'Yes', NULL, NULL, 'LI11-HV-ICTE-2020-0028', 'Working Unit'),
(7, 'ICT-LC_4LTSRE-PC002', 'Desktop Computer', 'Record Management Supply', 'Marilyn Torculas', 'LTSB/4th floor/Records', 47880.00, '2020-06-24', NULL, NULL, 'HDD ST1000DM010-2EP102 1TB', '8GB DIMM', NULL, 'HP', 'HP', NULL, 'Intel Core i7-9700K 3.6GHz', NULL, 'Yes', 'Microsoft Office (Not Licensed)', NULL, '21020', 'Working Unit'),
(8, 'ICT-LC_4LTSRE-PC001', 'Desktop Computer', 'Record Management Supply', 'Marilyn Torculas', 'LTSB/4th floor/Records', 32590.00, '2021-03-29', NULL, 'Neutron Fit', 'HDD Toshiba HDWD110 1TB', '8GB DIMM', NULL, 'Rapoo', 'Rapoo', 'Eco Power', 'Intel Core i5-10400 2.9GHz', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, 'CA-0013-21', 'Working Unit'),
(9, 'ICT-LC_4LTSPS-PC001', 'Desktop Computer', 'PSO', 'Sheena Mendez', 'LTSB/4th floor/PSO', 47880.00, '2020-06-24', NULL, 'HIP Gigabyte Technology Co.', 'HDD ST1000DM010-2EP102 1TB', '8GB DIMM', 'Intel UHD Graphics 630', 'Promax', 'A4TECH', 'Eco Power', 'Intel Core i7-9700K 3.6GHz', 'Windows 10 Pro', 'Yes', NULL, NULL, '0215-20', 'Working Unit'),
(10, 'ICT-LC_4LTSCI-PC001', 'Desktop Computer', 'CICS', 'Dr. Ryndel Amorado', 'LTSB/4th floor/CICS', 47880.00, '2020-06-24', NULL, 'Z390 UD', 'HDD ST1000DM010-2EP102', '8GB', 'Intel UHD Graphics 630', 'A4TECH', 'A4TECH', NULL, 'Intel Core i7-9700K 3.60GHz', 'Windows 10 Pro', NULL, NULL, NULL, 'LI21-HV-ICTE-2020-0034', 'Working Unit (No AVR)'),
(11, 'ICT-LC_4LTSHR-PC003', 'Desktop Computer', 'HR', 'Renzi', 'LTSB/3rd floor/HR', 42500.00, '2018-11-09', NULL, 'HP 280 G4 MT Business PC', 'HDD Toshiba DT01ACA 1TB', '16GB DIMM', 'Intel UHD Graphics 630', 'HP', 'HP', NULL, 'Intel Core i5-8400 2.80GHz', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, 'LI10-HV-ICTE-2018-0001', 'Working Unit (No AVR)'),
(12, 'ICT-LC_4LTSHR-PC001', 'Desktop Computer', 'HR', 'Amie', 'LTSB/3rd floor/HR', 39240.00, '2017-03-26', NULL, 'Coolmaster', NULL, NULL, NULL, 'HP', 'HP', 'Secure', NULL, 'Windows 7 Professional', 'Yes', 'Microsoft Office (Licensed)', NULL, 'LI10-HV-ICTE-2018-0004', 'Working Unit'),
(13, 'ICT-LC_4LTSHR-PC002', 'Desktop Computer', 'HR', 'Ian', 'LTSB/3rd floor/HR', 42500.00, '2018-11-09', NULL, 'HP 280 G4 MT', 'HDD Toshiba DT01ACA100 1TB', '8GB DIMM', 'Intel UHD Graphics 630', 'HP', 'HP', 'Eco Power', 'Intel Core i5-8400 2.80GHz', 'Windows 10 Pro', 'Yes', 'WPS', NULL, 'LI10-HV-ICTE-2018-0001', 'Working Unit'),
(14, 'ICT-LC_4LTSHR-PC004', 'Desktop Computer', 'HR', 'Ester Iglopas', 'LTSB/3rd floor/HR', 47880.00, '2020-06-24', NULL, NULL, 'HDD ST1000DM010-2EP102 1TB', '8GB DIMM', 'Intel UHD Graphics 630', 'A4TECH', 'A4TECH', 'Secure', 'Intel Core i7-9700K 3.60GHz', 'Windows 10 Pro', NULL, NULL, NULL, 'LI10-HV-ICTE-2020-0019', 'Working Unit'),
(15, 'ICT-LC_2LTSAF-PC001', 'Desktop Computer', 'OVCAF', 'Lovely', 'LTSB/2nd floor/OVCAF', 47880.00, '2020-06-24', NULL, 'C393', 'HDD ST1000DM010-2EP102 1TB', '8GB DIMM', 'Intel UHD Graphics 630', 'A4TECH', 'A4TECH', 'Eco Power', 'Intel Core i7-9700K 3.60GHz', 'Windows 10 Pro', 'Yes', NULL, NULL, 'LI03-HV-ICTE-2020-0022', 'Working Unit'),
(16, 'ICT-LC_2LTSAF-PC002', 'Desktop Computer', 'OVCAF', 'Lovely', 'LTSB/2nd floor/OVCAF', 47880.00, '2020-06-24', NULL, 'Z390 UD', 'HDD ST1000DM010-2EP102 1TB', '4GB DIMM', 'Intel UHD Graphics 630', 'A4TECH', 'A4TECH', 'Eco Power', 'Intel Core i7-9700K 3.60GHz', 'Windows 10 Pro', 'Yes', NULL, NULL, 'LI03-HV-ICTE-2020-0023', 'Working Unit'),
(17, 'ICT 10033', 'Desktop Computer', 'Extension Services Office', NULL, 'LTSB/3rd floor/OVCRDES', 24380.00, '2017-04-10', NULL, NULL, 'HDD ST500DM002-1SB1QA 200GB', '4GB', 'Intel HD Graphics', 'Rapoo', 'Xitrix', 'Secure', 'Intel Core i5-8400 2.80GHz', 'Windows 7 Ultimate', NULL, NULL, NULL, 'L142-HV-ICTE-2017-0013', 'Working Unit'),
(18, 'ICT 10077', 'Desktop Computer', 'Extension Services Office', NULL, 'LTSB/3rd floor/OVCRDES', NULL, NULL, NULL, NULL, 'HDD ST1000DM010-2EP102 1TB', '4GB DIMM', 'Intel HD Graphics 630', 'A4TECH', NULL, 'Delkin', 'Intel Core i5-7400 3.00GHz', 'Windows 10 Home', NULL, NULL, NULL, NULL, 'Working Unit'),
(19, 'ICT-LC_4LTSRD-PC001', 'Desktop Computer', 'OVCRDES', 'Bryan', 'LTSB/3rd floor/OVCRDES', NULL, NULL, NULL, NULL, 'HDD ST1000DM010-2EP102 1TB', '16GB DIMM', 'NVIDIA GeForce GT 710', 'Logitech', 'AULA', 'Secure', 'Intel Core i5-9400F 2.90GHz', 'Windows 11 Pro', NULL, NULL, NULL, NULL, 'Working Unit / No Property Tag'),
(20, 'ICT 10025', 'Desktop Computer', 'INTERNAL AUDIT', 'Leoven Austria', 'LTSB/2nd floor/OC', 42500.00, '2018-11-09', NULL, 'HP 280 G4 MT Business PC', 'HDD ST500DM002-1BD142 500GB', '8GB DIMM', 'Intel UHD Graphics 630', 'HP', 'HP', NULL, 'Intel Core i5-8400 2.80GHz', 'Windows 10 Pro', 'Yes', NULL, NULL, 'LI02-HV-ICTE-2018-0007', 'Working Unit'),
(21, 'ICT 10026', 'Desktop Computer', 'INTERNAL AUDIT', 'Leoven Austria', 'LTSB/2nd floor/OC', 47880.00, NULL, NULL, 'Z390', 'HDD ST1000DM010-2EP102 1TB', '8GB DIMM', 'Intel UHD Graphics 630', 'A4TECH', 'A4TECH', 'Secure', 'Intel Core i7-10700KF 3.60GHz', 'Windows 10 Enterprise', 'Yes', NULL, NULL, 'LI02-HV-ICTE-2020-0025', 'Working Unit'),
(22, 'ICT 10023', 'Desktop Computer', 'Sustainable Development Office', 'Richelle Sulit', 'LTSB/2nd floor/OC', 71762.00, '2022-03-22', NULL, 'Xitrix DeskFrame GL1 H510', 'HDD ST1000DM010-2EP102 1TB', '8GB DIMM', 'Intel Core i7-10700KF 3.60GHz', 'Xitrix', 'Xitrix', 'Secure', 'Intel Core i7-10700KF 3.60GHz', 'Windows 11 Pro', 'Yes', NULL, NULL, '2022-06-03-0046(1)-LI37', 'Working Unit'),
(23, 'ICT-LC_2LTSDE-PC001', 'Desktop Computer', 'Planning and Development', 'Ron', 'LTSB/2nd floor/VCDEA', 47880.00, '2020-06-24', NULL, 'Z390 UD', 'HDD ST1000DM010-2EP102 1TB', '8GB DIMM', 'Intel UHD Graphics 630', 'A4TECH', 'A4TECH', 'Secure', 'Intel Core i7-9700K 3.69GHz', 'Windows 11 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, 'LI35-HV-ICTE-2020-0015', 'Working Unit'),
(24, 'ICT-LC_2LTSDE-PC002', 'Desktop Computer', 'Planning and Development', 'Aira', 'LTSB/2nd floor/VCDEA', 47880.00, '2020-06-24', NULL, 'Z390 UD', 'HDD 1TB', '8GB DIMM', 'Intel UHD Graphics 630', 'A4TECH', 'A4TECH', 'Eco Power', 'Intel Core i7-9700K 3.60GHz', 'Windows 10 Home', 'Yes', 'Microsoft Office (Not Licensed)', NULL, 'LI35-HV-ICTE-2020-0016', 'Working Unit'),
(25, 'COMP 08', 'Desktop Computer', 'SFAO', NULL, NULL, NULL, NULL, NULL, 'All Series', 'HDD ST500DM002-1SB1QA 500GB', '4GB DDR3', 'Intel UHD Graphics 630', 'A4TECH', 'A4TECH', 'Secure', 'Intel Core i5-4690 3.80GHz', 'Windows 10 Home', 'No', 'WPS', NULL, NULL, 'Working Unit / No Property Tag'),
(26, 'ICT-LC_1LTSTA-PC001', 'Desktop Computer', 'TAO', 'Kristine Joy Tibayan', 'LTSB/1st floor/TAO', 47880.00, '2020-06-24', NULL, 'Z390 UD', 'HDD ST1000DM010-2EP102 1TB', '8GB DIMM', 'Intel UHD Graphics 630', 'A4TECH', 'A4TECH', 'Paramount', 'Intel Core i7-9700K 3.60GHz', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Not Licensed)', NULL, NULL, 'Working Unit'),
(27, 'ICT-LC_1LTSBU-PC001', 'Desktop Computer', 'BUDGET', 'Mark Ben', 'LTSB/1st floor/Budget', 47880.00, '2020-06-24', NULL, 'Z390 UD', 'Lexar SSD NS100 256GB', '8GB DIMM', 'Intel UHD Graphics 630', 'A4TECH', 'A4TECH', 'Secure', 'Intel Core i7-9700K 3.60GHz', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, '0221-20', 'Working Unit'),
(28, 'ICT-LC_1LTSBU-PC002', 'Desktop Computer', 'BUDGET', 'Nikka', 'LTSB/1st floor/Budget', 47880.00, '2020-06-24', NULL, 'HP 280 G4 MT Business PC', 'HDD Toshiba DT01ACA100 1TB', '8GB DIMM', 'Intel UHD Graphics 630', 'A4TECH', 'A4TECH', 'Eco Power', 'Intel Core i5-8400 2.80GHz', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, 'LI05-HV-ICTE-2020-0032', 'Working Unit'),
(29, 'ICT-LC_1LTSBU-PC003', 'Desktop Computer', 'BUDGET', 'Mariel', 'LTSB/1st floor/Budget', 47880.00, '2020-06-24', NULL, 'Z390 UD', 'HDD ST1000DM010-2EP102 1TB', '8GB DIMM', 'Intel UHD Graphics 630', 'A4TECH', 'A4TECH', 'Eco Power', 'Intel Core i7-9700K 3.60GHz', 'Windows 10 Pro', 'Yes', 'WPS', NULL, 'LI05-HV-ICTE-2020-0032', 'Working Unit'),
(30, 'ICT-LC_1LTSCA-PC001', 'Desktop Computer', 'CASHIER', 'Sahara Jane Guno', 'LTSB/1st floor/Cashier', 53880.00, '2020-06-24', NULL, 'DELL', 'HDD ST1000DM010-2GH172 1TB', '8GB SODIMM', 'NVIDIA GeForce MX110', 'DELL', 'DELL', 'Monster', 'Intel Core i5-10210 1.60GHz', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, '2020-06-03-0001(1)-LI06', 'Working Unit'),
(31, 'ICT-LC_1LTSCA-PC002', 'Desktop Computer', 'CASHIER', 'Janina', 'LTSB/1st floor/Cashier', NULL, NULL, NULL, 'HP 280 G4 MT Business PC', 'HDD WDC WD10EZEX-60WN4A0 149GB', '8GB DIMM', 'Intel UHD Graphics 630', 'HP', 'HP', 'Secure', 'Intel Core i5-8400 2.80GHz', 'Windows 10 Pro', NULL, NULL, NULL, NULL, 'Working Unit'),
(32, 'ICT-LC_1LTSAC-PC001', 'Desktop Computer', 'ACCOUNTING', 'Kristel', 'LTSB/1st floor/Accounting', 47500.00, '2018-11-09', NULL, 'HP 280 G4 MT Business PC', 'HDD Toshiba DT01ACA100 1TB', '8GB DIMM', 'Intel UHD Graphics 630', 'HP', 'Xitrix', 'Monster', 'Intel Core i5-8400 2.80GHz', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, 'LI02-HV-ICTE-2018-0009', 'Working Unit'),
(33, 'ICT-LC_1LTSAC-PC002', 'Desktop Computer', 'ACCOUNTING', 'Keen', 'LTSB/1st floor/Accounting', 47880.00, '2020-06-24', NULL, 'HIP', 'HDD ST1000DM010-2EP102 1TB', '8GB DIMM', 'Intel UHD Graphics 630', 'A4TECH', 'A4TECH', 'Eco Power', 'Intel Core i7-9700K 3.60GHz', 'Windows 10 Pro', 'No', NULL, NULL, 'LI04-HV-ICTE-2020-0014', 'Working Unit'),
(34, 'ICT-LC_1LTSAC-PC003', 'Desktop Computer', 'ACCOUNTING', NULL, 'LTSB/1st floor/Accounting', 47880.00, '2020-06-24', NULL, 'C390 UNIT', 'HDD ST1000DM010-2EP102 1TB', '8GB DIMM', 'Intel UHD Graphics 630', 'A4TECH', 'A4TECH', NULL, 'Intel Core i7-9700K 3.60GHz', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, 'LI04-HV-ICTE-2020-0013', 'Working Unit / No AVR'),
(35, 'ICT-LC_1LTSAC-PC004', 'Desktop Computer', 'ACCOUNTING', 'Vivian', 'LTSB/1st floor/Accounting', 61585.00, '2022-09-29', NULL, 'DeskFrame GL1', 'HDD WDC WD10EZEX-00BBHA0 1TB', '8GB DIMM', 'NVIDIA GeForce GT 730', 'Xitrix', 'Xitrix', 'Secure', 'Intel Core i7-10700K 3.80GHz', 'Windows 11 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, '2022-06-03-0067(1)-LI37', 'Slow PC / HDD'),
(36, 'ICT-LC_1LTSRR-PC001', 'Desktop Computer', 'REGISTRAR', 'Jay-Rhen', 'LTSB/1st floor/Registrar', 25137.00, '2015-12-10', NULL, 'All Series', 'HDD Toshiba DT01ACA100 1TB', '2GB', NULL, 'A4TECH', 'A4TECH', 'KOBI', 'Intel Core i5-4690K 3.50GHz', 'Windows 7 Professional', 'Yes', 'Microsoft Office (Licensed)', NULL, '01314-16', 'Working Unit'),
(37, 'ICT-LC_1LTSRR-PC002', 'Desktop Computer', 'REGISTRAR', 'Aira', 'LTSB/1st floor/Registrar', 20200.00, '2011-01-24', NULL, 'ASUS', 'HDD ST325031AS ATA 500GB', '2GB', 'Intel G33/G31 Express Chipset Family', 'A4TECH', 'Xitrix', NULL, 'Intel Core Duo E7500 2.93GHz', NULL, 'Yes', 'Microsoft Office (Licensed)', NULL, 'LI30-HV-ICTE-2011-0003', 'Working Unit / No AVR'),
(38, 'ICT-LC_1LTSRR-PC003', 'Desktop Computer', 'REGISTRAR', 'Jenelyn Gutierrez', 'LTSB/1st floor/Registrar', 58512.00, '2022-02-22', NULL, 'Xitrix DeskFrame GL1 H510', 'HDD WDC WD10EZEX-60WN4A0 1TB', '8GB DIMM', 'NVIDIA GeForce GT 740', 'XITRIX', 'A4TECH', 'Advance Power', 'Intel Core i7-10700KF 3.80GHz', 'Windows 11 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, 'CA-ICT-0044-22', 'Working Unit'),
(39, 'ICT-LC_1LTSRR-PC004', 'Desktop Computer', 'REGISTRAR', 'Jem', 'LTSB/1st floor/Registrar', 20200.00, '2011-01-24', NULL, 'HP 280 G4 MT Business PC', 'HDD Toshiba DT01ACA100 1TB', '8GB DIMM', 'Intel UHD Graphics 630', 'HP', 'HP', 'AWD', 'Intel Core i5-8400 2.80GHz', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Not Licensed)', NULL, '8610-11', 'Working Unit'),
(40, 'ICT-LC_1LTSRR-PC005', 'Desktop Computer', 'REGISTRAR', NULL, 'LTSB/1st floor/Registrar', 20206.00, '2011-01-24', NULL, 'All Series', 'HDD WDC WD10EZEX-22MRA1 1TB', '2GB', 'Intel G31', 'RAPOO', 'RAPOO', 'Monster', 'Intel Core Duo E7500 2.93GHz', 'Windows 7 Ultimate', 'Yes', 'Microsoft Office (Licensed)', NULL, '8610-11', 'Super Slow'),
(41, 'ICT-LC_4LTSHR-PC004', 'Desktop Computer', 'COA', NULL, 'LTSB/3rd floor/COA', NULL, '2013-09-11', NULL, 'HIP', 'HDD 500GB', '4GB', 'NVIDIA GeForce GT 610', 'A4TECH', 'A4TECH', 'Eco Power', 'Intel Core i3', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, NULL, 'Slow PC (ICT property tag is different)'),
(42, 'ICT-LC_2LTSOC-PC001', 'Desktop Computer', 'OC', NULL, 'LTSB/2nd floor/OC', 42500.00, '2018-11-09', NULL, 'HP', 'HDD Toshiba DT01ACA100 1TB', '8GB DIMM', 'Intel UHD Graphics 630', 'HP', 'HP', 'Secure', 'Intel Core i5-8400 2.80GHz', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Not Licensed)', NULL, 'LI02-HV-ICTE-2018-008', 'Working Unit'),
(43, 'ICT-LC_4LTSIC-PC001', 'Desktop Computer', 'ICT', 'Carlo Kristan Catud', 'LTSB/4th floor/ICT', NULL, NULL, NULL, 'Xitrix DeskFrame E-305', 'HDD ST1000DM010-2EP102 1TB', '8GB DIMM', 'NVIDIA GeForce GTX 1050 Ti', 'XITRIX', 'HP', 'Secure', 'Intel Core i7-10700KF 3.80GHz', 'Windows 11 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, NULL, 'Slow PC (For Biometrics Only) / No Property Tag'),
(44, 'CA-0012-21', 'Desktop Computer', 'GSO', NULL, 'VMB/1st Floor/GSO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ICTLI-PC-30004', 'Defective Motherboard; Broken PC'),
(45, 'ICT-LC_1VMBGS-PC001', 'Desktop Computer', 'GSO', 'Mr. Jerry P. Lumbera', 'VMB/1st Floor/GSO', 38500.00, '2021-02-03', NULL, 'Xitrix Desk Frame E-180', NULL, NULL, NULL, 'XITRIX', NULL, 'Eco Power', NULL, NULL, NULL, NULL, 'LI09-HV-ICTE-2021-0051', NULL, 'Broken PC (Pulled Out)'),
(46, 'ICT-LC_2VMBAA-PC001', 'Desktop Computer', 'VCAA', 'Christine', 'VMB/2nd Floor/VCAA', 38500.00, '2021-02-03', NULL, 'Xitrix Desktop Frame E-180', 'HDD ST1000DM10-2EP102 1TB', '16GB DIMM', 'NVIDIA GeForce GT 710', 'XITRIX', 'XITRIX', 'Secure', 'Intel Core i5-9400F 2.90GHz', 'Windows 11 Pro', 'Yes', 'Microsoft Office (Not Licensed)', NULL, 'LI15-HV-ICTE-2021-0055', 'Working Unit'),
(47, 'ICTLI-PC-200', 'Desktop Computer', 'VCAA', 'Dr. Imelda M. Flores', 'VMB/2nd Floor/VCAA', 38500.00, '2021-02-03', NULL, 'Xitrix Desktop Frame E-180', 'HDD ST1000DM10-2EP102 1TB', '16GB DIMM', 'NVIDIA GeForce GT 710', 'XITRIX', 'XITRIX', 'Secure', 'Intel Core i5-9400F 2.90GHz', 'Windows 11 Pro', 'Yes', 'Microsoft Office (Not Licensed)', NULL, 'LI15-HV-ICTE-2021-0054', 'Broken Motherboard; replaced with ICTLI-PC-31001'),
(48, 'ICT-LC_2VMBAA-PC001-OLD', 'Desktop Computer', 'VCAA', 'Christine', 'VMB/2nd Floor/VCAA', NULL, NULL, NULL, 'Xitrix', 'Lexar SSD NS100 256GB', '2GB DDR3', NULL, 'XITRIX', 'XITRIX', 'Secure', 'Intel Core i5-3330 3.00GHz', 'Windows 11 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, NULL, 'Working Unit'),
(49, 'ICT-LC_2VMBNS-PC001', 'Desktop Computer', 'NSTP', 'Asst. Prof. Elvis B. Lumanglas', 'VMB/2nd Floor/NSTP', 69645.00, '2022-03-04', NULL, 'Trend Sonic', 'HDD ST1000DM010-2EP102 1TB', '8GB DIMM', 'Intel UHD Graphics 750', 'A4TECH', 'A4TECH', NULL, 'Intel Core i7-11700K 2.50GHz', 'Windows 11 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, '2022-06-03-0053(3)-LI27', 'Working Unit'),
(50, 'ICT-LC_VMBCE-PC001', 'Desktop Computer', 'CET', 'Wenyfer T. Suarez', 'VMB/3rd Floor/CET', 47880.00, '2020-06-24', NULL, 'HP 280 G4 MT Business PC', 'HDD Toshiba DT01ACA100 1TB', '8GB DIMM', 'Intel UHD Graphics 630', 'HP', 'HP', 'Eco Power', 'Intel Core i5-8400 2.80GHz', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, '0213-20', 'Working Unit'),
(51, 'ICT-LC_VMBCE-PC002', 'Desktop Computer', 'CET', 'Dr. Emerita T. Generoso', 'VMB/3rd Floor/CET', 32590.00, '2021-03-29', NULL, 'Gigabyte H410M S2H V2', 'HDD Toshiba HDWD110 1TB', '8GB DIMM', 'Intel UHD Graphics 630', 'RAPOO', 'RAPOO', 'Eco Power', 'Intel Core i5-10400 2.90GHz', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, 'LI22-HV-ICTE-2021-0058', 'Working Unit'),
(52, 'ICT-LC_VMBSP-PC001', 'Desktop Computer', 'SPORTS', 'Mr. Randy M. Manimtim', 'VMB/1st Floor/SPORTS', 39240.00, '2017-03-28', NULL, 'Cool Master', 'HDD ST1000DM010-2EP102 1TB', '12GB', 'Intel HD Graphics 630', 'A4TECH', 'A4TECH', 'Eco Power', 'Intel Core i5-7400 3.00GHz', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, '23117', 'Working Unit'),
(53, 'ICT-LC_2VMBCT-PC001', 'Desktop Computer', 'CTE', 'Dr. Eufronia M. Magundayao', 'VMB/3rd Floor/CTE', 32590.00, '2021-03-29', NULL, 'H410M2HV2', 'Lexar SSD NS100 256GB / Toshiba HDWD110', '8GB', 'Intel UHD Graphics', 'RAPOO', 'RAPOO', 'Secure', 'Intel Core i5-10400 2.90GHz', 'Windows 11', 'No', 'Microsoft Office (Licensed)', NULL, 'LI20-HV-ICTE-2021-0056', 'Working Unit'),
(54, 'ICT-LC_4VMBCA-PC001', 'Desktop Computer', 'CAS', 'Assoc. Prof. Maria Lucia A. Caringal', 'VMB/4th Floor/CAS', 38500.00, '2021-02-03', NULL, 'DeskFrame E-180 SFF', 'HDD ST1000DM010-2EP102 1TB', '16GB', 'NVIDIA GeForce GT 710', 'XITRIX', 'A4TECH', 'Secure', 'Intel Core i5-9400F 2.90GHz', 'Windows 11 Pro', 'Yes', 'Microsoft Office (Not Licensed)', NULL, 'LI18-HV-ICTE-2021-0050', 'Working Unit'),
(55, 'ICT-LC_5VMBRE-PC001', 'Desktop Computer', 'RESEARCH', 'Engr. Leoven A. Austria', 'VMB/5th Floor/RESEARCH', 32590.00, '2021-03-29', NULL, 'Neutron Fit H410M S2HV2', 'HDD Toshiba HDWD110 1TB', '8GB', 'Intel UHD Graphics 630', 'RAPOO', 'RAPOO', 'Secure', 'Intel Core i5-10400 2.90GHz', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, 'LI19-HVICTE-2021-0057', 'Working Unit'),
(56, 'ICT-LC_5VMBRE-PC002', 'Desktop Computer', 'RESEARCH', 'Engr. Leoven A. Austria', 'VMB/5th Floor/RESEARCH', 32590.00, '2021-03-29', NULL, 'Neutron Fit H410M S2HV2', 'HDD Toshiba HDWD110 1TB', '8GB', 'Intel UHD Graphics 630', 'A4TECH', 'A4TECH', 'Secure', 'Intel Core i5-10400 2.90GHz', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, 'LI19-HVICTE-2021-0059', 'Working Unit'),
(57, 'ICT-LC_3GZBOS-PC001', 'Desktop Computer', 'OSD', 'Engr. Leoven A. Austria', 'GZB/3rd Floor/OSD', 47880.00, '2020-06-24', NULL, 'HIP Z390 UD', 'HDD ST1000DM010-2EP102 1TB', '8GB', 'Intel UHD Graphics 630', 'A4TECH', 'A4TECH', 'Eco Power', 'Intel Core i7-9700 3.60GHz', 'Windows 11 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, 'LI19-HV-ICTE-2020-0035', 'Working Unit / Replaced HDD'),
(58, 'ICT-LC_1FCRG-PC001', 'Desktop Computer', 'RGO', 'Dr. Annalee D. Cabrera', '1st Floor/RGO', 47880.00, '2020-06-24', NULL, 'HIP Z390 UD', 'HDD ST1000DM010-2EP102 1TB', '8GB', 'Intel UHD Graphics 630', 'A4TECH', 'A4TECH', 'Eco Power', 'Intel Core i7-9700 3.60GHz', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, 'LI39-HV-ICTE-2020-0037', 'Working Unit'),
(59, 'ICT-LC_1FCRG-PC002', 'Desktop Computer', 'RGO', 'Dr. Vanessah V. Castillo', '1st Floor/RGO', 47880.00, '2020-06-24', NULL, 'HIP 5903 Full ATX Casing', 'HDD ST1000DM010-2EP102 1TB', '8GB', 'Intel UHD Graphics 630', 'A4TECH', 'A4TECH', 'Eco Power', 'Intel Core i7-9700 3.60GHz', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, 'LI35-HV-ICTE-2020-0017', 'Working Unit'),
(84, 'ICT-LI_2ABBHS-PC001', 'Desktop Computer', 'HEALTH', 'Engr. J.T. Rayos', 'ABB/2nd Floor/HS', NULL, '2013-09-11', NULL, 'N/A', 'ST500DM002-1BD142', '4 GB DDR3', 'NVIDIA GeForce GT 610', 'A4TECH', 'A4TECH', 'SECURE', 'Intel(R) Core(TM) i3-3210 CPU 3.20GHz', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, '11275-A', 'Working Unit'),
(85, 'ICT-LI_2ABBHS-PC002', 'Desktop Computer', 'HEALTH', 'CLINIC', 'ABB/2nd Floor/HS', 47880.00, '2020-06-24', NULL, 'HIP Z390 UD', 'ST1000DM010-2EP102', '8 GB', 'Intel(R) UHD Graphics 630', 'A4TECH', 'XITRIX', 'ECO POWER', 'Intel(R) Core(TM) i7-9700 CPU 3.60GHz', 'Windows 10 Enterprise', 'Yes', 'WPS', NULL, '0217-20', 'Working Unit/Replaced Mouse'),
(86, 'ICT-LI_3ABBCA-PC001', 'Desktop Computer', 'CABE', 'CABE', 'ABB/3rd Floor/CABE', 47880.00, '2020-06-24', NULL, 'HIP Z390 UD', 'ST1000DM010-2EP102', '8 GB', 'Intel(R) UHD Graphics 630', 'A4TECH', 'A4TECH', NULL, 'Intel(R) Core(TM) i7-9700 CPU 3.60GHz', 'Windows 10 Enterprise', 'No', 'Microsoft Office (Not Licensed)', NULL, '0218-20', 'Working Unit'),
(87, 'ICT-LI_4ABBOG-PC001', 'Desktop Computer', 'OGC', 'Engr. Jovito P. Permanente', 'ABB/4th Floor/OGC', 39240.00, '2017-03-28', NULL, 'Cool Master', 'ST1000DM010-2EP102', '4 GB', 'Standard VGA Graphics Adapter', 'A4TECH', 'A4TECH', 'SECURE', 'Intel(R) Core(TM) i5-7400 CPU 3.00GHz', 'Windows 7 Ultimate', 'No', 'Microsoft Office (Not Licensed)', NULL, '0234-17', 'Working Unit'),
(88, 'ICT-LI_4ABBOG-PC002', 'Desktop Computer', 'OGC', 'Ms. Caroline Ladaga', 'ABB/4th Floor/OGC', 25137.00, '2015-12-10', NULL, 'NEUTRON FIT', 'TOSHIBA DT01ACA050', '4 GB DDR3', 'NVIDIA GeForce GT 730', 'A4TECH', 'A4TECH', 'DELKIN', 'Intel(R) Core(TM) i5-4690 CPU 3.50GHz', 'Windows 10 Pro', 'No', 'Microsoft Office (Licensed)', NULL, '0317-16', 'Working Unit'),
(89, 'ICT-LI_5ABBLI-PC001', 'Desktop Computer', 'LIBRARY', 'Mr. Carlo Pastrana', 'ABB/5th Floor/LIBRARY', 22700.00, '2012-05-16', NULL, 'SAMSUNG', 'KINGSTON SKC600512G', '2 GB DDR3', 'NVIDIA GeForce GT 610', 'XITRIX', 'RAPOO', 'ECO POWER', 'Intel(R) Core(TM) i5-3450 CPU 3.10GHz', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, 'ICTLI-PC-30016', 'Working Unit'),
(90, 'ICT-LI_5ABBLI-PC002', 'Desktop Computer', 'LIBRARY', 'Engr. Jonnah R. Melo', 'ABB/5th Floor/LIBRARY', 58512.00, '2022-02-22', NULL, 'Xitrix DeskFrame E-300', '932 GB HDD WDC WD10EZEX-00BBHA0', '8 GB', 'NVIDIA GeForce GT 730', 'XITRIX', 'A4TECH', 'SECURE', 'Intel(R) Core(TM) i7-10700KF CPU 3.80GHz', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, '2022-06-03-0060(1)-LI33', 'Working Unit'),
(91, 'ICT-LI_5ABBLI-PC003', 'Desktop Computer', 'LIBRARY', 'Mrs. Donnalin Trinidad', 'ABB/5th Floor/LIBRARY', 47880.00, '2020-06-24', NULL, 'HIP Z390 UD', '1TB HDD ST1000DM010-2EP102', '8 GB', 'Intel(R) UHD Graphics 630', 'A4TECH', 'A4TECH', NULL, 'Intel(R) Core(TM) i7-9700 CPU 3.60GHz', 'Windows 10 Pro', 'Yes', 'Microsoft Office (Licensed)', NULL, '0219-20', 'Working Unit');

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `asset_tag` varchar(100) DEFAULT NULL,
  `property_equipment` varchar(100) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `assigned_person` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `unit_price` decimal(12,2) DEFAULT NULL,
  `date_acquired` date DEFAULT NULL,
  `useful_life` varchar(100) DEFAULT NULL,
  `hardware_specifications` text DEFAULT NULL,
  `software_specifications` text DEFAULT NULL,
  `high_value_ics_no` varchar(100) DEFAULT NULL,
  `inventory_item_no` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('active','maintenance','disposed','lost') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment_categories`
--

CREATE TABLE `equipment_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment_categories`
--

INSERT INTO `equipment_categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Laptop PC', 'Portable computers for mobile computing needs', '2025-07-27 00:07:33'),
(2, 'Desktop PC', 'Stationary computers for office work', '2025-07-27 00:07:33'),
(3, 'Printer', 'Printing devices for document output', '2025-07-27 00:07:33'),
(4, 'Router', 'Network routing devices', '2025-07-27 00:07:33'),
(5, 'Access Point', 'Wireless network access points', '2025-07-27 00:07:33');

-- --------------------------------------------------------

--
-- Table structure for table `history`
--

CREATE TABLE `history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `history`
--

INSERT INTO `history` (`id`, `user_id`, `equipment_id`, `table_name`, `action`, `timestamp`) VALUES
(1, 7, 35, 'desktop', 'qr_scan', '2025-09-27 13:02:44'),
(2, 7, 1, 'desktop', 'qr_scan', '2025-09-27 13:02:54'),
(3, 7, 1, 'desktop', 'qr_scan', '2025-09-27 13:05:46'),
(4, 7, 49, 'desktop', 'qr_scan', '2025-09-27 13:16:47'),
(5, 4, 57, 'printers', 'Add Maintenance', '2025-09-27 13:37:40'),
(6, 4, 59, 'printers', 'Add Maintenance', '2025-09-27 13:39:03'),
(7, 4, 59, 'printers', 'Add Maintenance', '2025-09-27 13:39:17'),
(8, 4, 1, 'accesspoint', 'Add Maintenance', '2025-09-27 13:39:40'),
(9, 9, 35, 'desktop', 'qr_scan', '2025-09-29 06:17:04');

-- --------------------------------------------------------

--
-- Table structure for table `laptops`
--

CREATE TABLE `laptops` (
  `id` int(11) NOT NULL,
  `asset_tag` varchar(100) DEFAULT NULL,
  `property_equipment` varchar(100) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `assigned_person` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `unit_price` decimal(12,2) DEFAULT NULL,
  `date_acquired` date DEFAULT NULL,
  `useful_life` varchar(100) DEFAULT NULL,
  `hardware_specifications` text DEFAULT NULL,
  `software_specifications` text DEFAULT NULL,
  `high_value_ics_no` varchar(100) DEFAULT NULL,
  `inventory_item_no` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laptops`
--

INSERT INTO `laptops` (`id`, `asset_tag`, `property_equipment`, `department`, `assigned_person`, `location`, `unit_price`, `date_acquired`, `useful_life`, `hardware_specifications`, `software_specifications`, `high_value_ics_no`, `inventory_item_no`, `remarks`) VALUES
(1, 'ICT-LC_4LTSPF-LP001', 'Laptop', 'PR', 'Roderick Cabael', 'LTSB/4th floor/Procurement', 63070.00, '2020-06-20', NULL, 'Xitrix TravelPro D-500 MX350 / 8g RAM LEXAR 256 gb SSD, Intel USD Graphics', 'Windows 11 Pro w/ Licensed', '2022-06-03-0007(2)-LI19', NULL, NULL),
(2, 'ICT-LC_1LTSRR-LP001', 'Laptop', 'REGISTRAR', NULL, NULL, 63070.00, '2022-06-20', NULL, 'Xitrix TravelPro D-500 MX350 / 8g RAM LEXAR 256 gb SSD, Intel USD Graphics', 'Windows 11', '2022-06-03-0039(02)-LI30', NULL, NULL),
(3, 'ICT-LC_1VMBGS-LP001', 'Laptop', 'GSO', 'Mr. Ronie Mendoza', 'VMB/1st Floor/GSO', 63070.00, '2022-06-20', NULL, 'Xitrix TravelProD-500 MX350; Intel Core i7-10750H', 'Windows 10 Pro', '2022-06-03-0012(2)-LI13', NULL, 'Working Unit'),
(4, 'ICT-LC_VMBSP-LP001', 'Laptop', 'SPORTS', 'Mr. Randy M. Manimtim', 'VMB/1st Floor/SPORTS', 63070.00, '2022-06-20', NULL, 'Xitrix TravelProD-500 MX350; Intel Core i7-10750H', 'Windows 10 Pro', '2022-06-03-0033', NULL, 'Working Unit'),
(5, 'ICT-LC_4VMBCA-LP001', 'Laptop', 'CAS', 'Assoc. Prof. Maria Lucia A. Caringal', 'VMB/4th Floor/CAS', 63070.00, '2022-06-20', NULL, 'Xitrix TravelProD-500 MX350; Intel Core i7-10750H', 'Windows 10', '2022-06-03-0009(2)-LI18', NULL, 'Working Unit'),
(6, 'ICT-LC_5VMBRE-LP001', 'Laptop', 'RESEARCH', 'Ms. Berna Grace M. Adame', 'VMB/5th Floor/RESEARCH', 63070.00, '2022-06-20', NULL, 'Xitrix TravelProD-500 MX350; Intel Core i7-10750H', 'Windows 10 Pro', '2022-06-03-0040(2)-LI18', NULL, 'Working Unit'),
(7, 'ICT-LI_2ABBHS-LP001', 'Laptop', 'HS', 'Medical and Dental Services', 'ABB/2nd Floor/HS', 63070.00, '2020-06-06', NULL, 'Xitrix TravelProD-500 MX350; Intel Core i7-10750H', 'Windows 11 Pro', '2022-06-03-0037(2)-LI25', NULL, 'Working Unit'),
(8, 'ICT-LI_3ABBCA-LP001', 'Laptop', 'CABE', 'Dr. Monette M. Soquiat', 'ABB/3rd Floor/CABE', 63070.00, '2022-06-20', NULL, 'Xitrix TravelProD-500 MX350; Intel Core i7-10750H', 'Windows 10 Pro', '2022-06-03-0030(2)-LI17', NULL, 'Working Unit'),
(9, 'ICT-LI_3ABBCA-LP002', 'Laptop', 'CABE', 'Dr. Madel L. Lumbera', 'ABB/3rd Floor/CABE', 63070.00, '2020-06-06', NULL, 'Xitrix TravelProD-500 MX350; Intel Core i7-10750H', 'Windows 11', '2022-06-03-0020(2)-LI17', NULL, 'Working Unit'),
(10, 'ICT-LI_4ABBOG-LP001', 'Laptop', 'OGC', 'Ms. Maria Lourdes G. Balita', 'ABB/4th Floor/OGC', 63070.00, '2022-06-20', NULL, 'Xitrix TravelProD-500 MX350; Intel Core i7-10750H', 'Windows 11 Pro', '2022-06-03-0006(2)-LI18', NULL, 'Working Unit');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user` varchar(100) NOT NULL,
  `action` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_records`
--

CREATE TABLE `maintenance_records` (
  `id` int(11) NOT NULL,
  `equipment_id` int(11) DEFAULT NULL,
  `equipment_type` varchar(50) NOT NULL,
  `technician_id` int(11) DEFAULT NULL,
  `maintenance_type` enum('preventive','corrective','upgrade') NOT NULL,
  `description` text DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `update_at` varchar(255) NOT NULL,
  `remarks` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `printers`
--

CREATE TABLE `printers` (
  `id` int(11) NOT NULL,
  `asset_tag` varchar(100) DEFAULT NULL,
  `property_equipment` varchar(100) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `assigned_person` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `unit_price` decimal(12,2) DEFAULT NULL,
  `date_acquired` date DEFAULT NULL,
  `useful_life` varchar(100) DEFAULT NULL,
  `hardware_specifications` text DEFAULT NULL,
  `software_specifications` text DEFAULT NULL,
  `high_value_ics_no` varchar(100) DEFAULT NULL,
  `inventory_item_no` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `printers`
--

INSERT INTO `printers` (`id`, `asset_tag`, `property_equipment`, `department`, `assigned_person`, `location`, `unit_price`, `date_acquired`, `useful_life`, `hardware_specifications`, `software_specifications`, `high_value_ics_no`, `inventory_item_no`, `remarks`) VALUES
(1, 'ICT-LC_4LTSPF-PR001', 'Printer', 'PFMO', 'Maricel Lacbay', 'LTSB/ 4th floor/PFMO', 10220.00, '2021-12-14', NULL, '3 in 1 EPSON L3250', NULL, 'LI08-HVICTE-2021-0035', NULL, 'Working Unit'),
(2, 'ICT-LC_4LTSEM-PR001', 'Printer', 'EMU', 'Engr. Gerald M. Foliente', 'LTSB/ 4th floor/EMU', 46912.00, '2024-11-19', NULL, 'Epson Echo Tank L1515150 A3 WIFI DUPLEX ALL IN ONE INK TANK PRINTER', NULL, 'LI12-HV-ICTE-2024-0031', NULL, 'Working Unit'),
(3, 'ICT-LC_4LTSPR-PR001', 'Printer', 'Procurement', 'Evangeline Garcia', 'LTSB/4th floor/Procurement', 10220.00, '2021-12-14', NULL, 'Epson echo tank L3250 wifi', NULL, 'LI16-HV-ICTE-2021-003', NULL, 'Working Unit'),
(4, 'ICT-LC_4LTSPR-PR002', 'Printer', 'Procurement', 'Evangeline Garcia', 'LTSB/4th floor/Procurement', 10220.00, '2021-12-14', NULL, 'Epson echo tank L3250 wifi', NULL, 'LI16-HV-ICTE-2021-0043', NULL, 'Working Unit'),
(5, 'ICT-LC_4LTSPR-PR003', 'Printer', 'Procurement', 'Evangeline Garcia', 'LTSB/4th floor/Procurement', 10220.00, '2021-12-14', NULL, 'Epson echo tank L3250 wifi', NULL, 'LI16-HV-ICTE-2021-0042', NULL, 'Working Unit'),
(6, 'ICT-LC_4LTSPS-PR001', 'Printer', 'PSO', 'Sheena Mendez', 'LTSB/4th floor/PSO', 10220.00, '2022-12-14', NULL, 'Epson echo tank L3250 Wifi', NULL, 'LI13-HV-ICTE-2021-0041', NULL, 'Working Unit'),
(7, 'ICT-LC_4LTSPS-PR002', 'Printer', 'PSO', NULL, 'LTSB/4th floor/PSO', 10225.00, '2022-11-23', NULL, 'Epson echo tank L3250', NULL, 'LI13-HV-ICTE-2022-0043', NULL, 'Working Unit'),
(8, 'ICT-LC_4LTSCI-PR001', 'Printer', 'CICS', 'Dr. Ryndel Amorado', 'LTSB/4th floor/CICS', 28280.00, '2025-01-06', NULL, 'Epson echo tank LI4150 A3+ WIFI DUPLEX', NULL, 'LI21-HV-ICTE-2025-0010', NULL, 'Working Unit'),
(9, 'ICT-LC_4LTSHR-PR001', 'Printer', 'HR', 'Ian', 'LTSB/3rd floor/HR', 10220.00, '2021-12-14', NULL, 'Epson Echo Tank L3250 Wifi All in one Ink Tank', NULL, 'LI10-HV-ICTE-2021-0032', NULL, 'Working Unit'),
(10, 'ICT-LC_4LTSHR-PR002', 'Printer', 'HR', 'Renzi', 'LTSB/3rd floor/HR', 10225.00, '2022-11-23', NULL, 'Epson BCO L3250 Wifi All in one Ink Tank', NULL, 'LI10-HV-ICTE-2022-0040', NULL, 'Working Unit'),
(11, 'ICT-LC_4LTSHR-PR003', 'Printer', 'HR', NULL, 'LTSB/3rd floor/HR', 10225.00, '2022-11-23', NULL, 'Epson echo tank L3250 Wifi', NULL, 'LI10-HV-ICTE-2022-0041', NULL, 'Working Unit'),
(12, 'ICT-LC_3LTSRD-PR001', 'Printer', 'RD', NULL, 'LTSB/3rd floor/RD', 10220.00, '2021-09-21', NULL, 'L3150', NULL, 'LI20-HV-ICTE-2021-0026', NULL, 'Working Unit'),
(13, 'ICT-LC_3LTSRD-PR002', 'Printer', 'RD', NULL, 'LTSB/3rd floor/RD', 50000.00, '2021-11-23', NULL, 'MP 2014AD', NULL, '2021-06-02-001(4)-LI40', NULL, 'Working Unit'),
(14, 'ICT-LC_3LTSRD-PR004', 'Printer', 'RD', NULL, 'LTSB/3rd floor/RD', 8790.00, '2022-05-24', NULL, 'EPSON L3210', NULL, 'LI410-HV-ICTE-2022-0013', NULL, 'Working Unit'),
(15, 'ICT-LC_3LTSRD-PR003', 'Printer', 'RD', NULL, 'LTSB/3rd floor/RD', 8790.00, '2022-05-24', NULL, 'EPSON L3210', NULL, 'LI41-HV-ICTE-2022-0013', NULL, 'Working Unit'),
(16, 'ICT-LC_4LTSRE-PR002', 'Printer', 'RE', NULL, 'LTSB/3rd floor/RE', 43320.00, '2021-07-27', NULL, 'EPSON DS 6500  SCanner', NULL, 'LI14-HV-ICTE-2021-0107', NULL, 'Working Unit'),
(17, 'ICT-LC_4LTSRE-PR001', 'Printer', 'RE', NULL, 'LTSB/3rd floor/RE', 9750.00, '2021-08-26', NULL, 'Canon G3010', NULL, 'LI14-HV-ICTE-2021-0020', NULL, 'Working Unit'),
(18, 'ICT-LC_4LTSRE-PR003', 'Printer', 'RE', 'MARILYN TORCULAS', 'LTSB/3rd floor/RE', 59000.00, '2021-11-23', NULL, 'DIGITAL COPIER', NULL, '2021-06-02-0015(4)-LI14', NULL, 'Working Unit'),
(19, 'ICT-LC_4LTSCO-PR001', 'Printer', 'COA', 'Leoven Austria', 'LTSB/2nd floor/OC', 10200.00, '2021-12-14', NULL, 'Epson ECOTANK L3250 Wifi', NULL, 'LI02-HVICTE-2021-0038', NULL, 'Working Unit'),
(20, 'ICT-LC_2LTSAF-PR001', 'Printer', 'VCAF', 'Michael Godoy', 'LTSB/2nd floor/AF', 9195.00, '2021-05-19', NULL, 'Canon 3in1 Printer G3010', NULL, 'LI03-HV-ICTE-2021-0012', NULL, 'Working Unit'),
(21, 'ICT-LC_2LTSAF-PR002', 'Photocopy', 'VCAF', NULL, NULL, 78000.00, '2021-11-23', NULL, 'MD 214', NULL, '2021-06-02-0012(4)-LI43', NULL, 'Working Unit'),
(22, 'ICT-LC_2LTSDE-PR001', 'Printer', 'VCDEA', 'Dioneces Alimoren', NULL, 10220.00, '2021-12-14', NULL, '3IN1 PRINTER EPSON L3250 WIFI', NULL, 'LI38-HVICTE-2021-0039', NULL, 'Working Unit'),
(23, 'ICT-LC_2LTSDE-PR002', 'Printer', 'VCDEA', 'Yolanda Pasia', NULL, 9195.00, '2021-05-19', NULL, '3IN1 CANON PRINTER G3010 AIO', NULL, 'LI13-HV-ICTE-2021-0016', NULL, 'Working Unit'),
(24, 'ICT-LC_2LTSOC-PR001', 'Printer', 'OC', 'Alvin De Silva', NULL, 10225.00, '2022-11-23', NULL, '3IN1 EPSON L3250', NULL, 'LI01-HV-ICTE-2022-0045', NULL, 'Working Unit'),
(25, 'ICT-LC_1LTSTA-PR001', 'Printer', 'TAO', 'Kristine Joy Tibayan', 'LTSB/1st floor/TAO', 12950.00, '2022-05-24', NULL, 'Brother DCP-TT720dw Ink TAnk Printer', NULL, 'LI34-HV-ICTE-2022-0005', NULL, 'Working Unit'),
(26, 'ICT-LC_1LTSTA-PR002', 'Printer', 'TAO', NULL, NULL, 11250.00, '2023-02-17', NULL, 'EPSON L3220', NULL, 'LI34-HV-ICTE-2023-0002', NULL, 'Working Unit'),
(27, 'ICT-LC_1LTSBU-PR001', 'Printer', 'BUDGET', NULL, NULL, 10220.00, '2022-03-16', NULL, 'Epson ECOTANK L3250', NULL, 'LI05-HV-ICTE-2021-0042', NULL, 'Working Unit'),
(28, 'ICT-LC_1LTSBU-PR002', 'Printer', 'BUDGET', NULL, NULL, 9195.00, '2021-05-16', NULL, 'CANON G3010', NULL, 'LI05-HV-ICTE-2021-0010', NULL, NULL),
(29, 'ICT-LC_1LTSBU-PR001_DUP', 'Printer', 'BUDGET', NULL, NULL, 59000.00, '2021-11-23', NULL, 'GESTENTER', NULL, '2021-06-02-0011(4)-LI05', NULL, NULL),
(30, 'ICT-LC_1LTSCA-PR001', 'Printer', 'CASHIER', NULL, NULL, 9980.00, '2017-03-28', NULL, 'EPSON L365, CONTINUES INK PRINTER', NULL, 'LI06-HV-0OTE-2017-0005', NULL, NULL),
(31, 'ICT-LC_1LTSAC-PR001', 'Printer', 'ACCOUNTING', NULL, NULL, 10220.00, '2021-10-14', NULL, 'EPSON L3250', NULL, 'LI04-HVICTE-2021-004', NULL, 'Working Unit'),
(32, 'ICT-LC_1LTSAC-PR002', 'Printer', 'ACCOUNTING', NULL, NULL, 10225.00, '2022-11-23', NULL, '3IN1 L3250', NULL, 'LI04-HV-ICTE-2022-0046', NULL, 'Working Unit'),
(33, 'ICT-LC_1LTSRR-PR001', 'Printer', 'REGISTRAR', NULL, NULL, 38076.00, '2021-06-03', NULL, 'MULTI FUNCTIONAL PRINTER', NULL, 'LI30-HVICTE-2021-0102', NULL, 'Working Unit'),
(34, 'ICT-LC_1LTSRR-PR002', 'Printer', 'REGISTRAR', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(35, 'ICT-LC_1LTSRR-PR003', 'Printer', 'REGISTRAR', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(36, 'ICT-LC_1LTSRR-PR004', 'Printer', 'REGISTRAR', NULL, NULL, 15500.00, '2022-07-04', NULL, 'HP OFFICEJET PRO 7720 WIDE', NULL, 'LI30-HC-ICTE-2022-0004', NULL, 'Working Unit'),
(37, 'ICT-LC_1LTSRR-PR005', 'Printer', 'REGISTRAR', NULL, NULL, 9750.00, '2021-08-26', NULL, 'EPSON L3250', NULL, 'LI30-=HC-ICTE-0021-0017', NULL, 'Working Unit'),
(38, 'ICT-LC_1VMBGS-PR001', 'Printer', 'GSO', 'Mr. Jerry P. Lumbera', 'VMB/1st Floor/GSO', 10220.00, '2021-12-14', NULL, '3 in 1 Printer Epson Eco Tank L3250 Wi-Fi All-in-One Ink Tank', NULL, 'CA-0090-21', NULL, 'Working Unit'),
(39, 'ICT-LC_2VMBAA-PR001', 'Printer', 'VCAA', 'Dr. Nerrie E. Malaluan', 'VMB/2nd Floor/VCAA', 10220.00, '2021-09-21', NULL, '3 in 1 Printer Epson Eco Tank L3150', NULL, 'LI15-HV-ICTE-2021-0021', NULL, 'Working Unit'),
(40, 'ICT-LC_2VMBAA-PR002', 'Printer', 'VCAA', 'Ms. Rhea M. Duran', 'VMB/2nd Floor/VCAA', 38076.00, '2021-06-03', NULL, 'HP Color Laser JET Pro MFP M479dw', NULL, 'LI30-HVICTE-2021-0101', NULL, 'Working Unit'),
(41, 'ICT-LC_2VMBAC-PR001', 'Printer', 'ACCREDITATION', 'Dr. Emerita T. Generoso', 'VMB/2nd Floor/AC', 10225.00, '2022-11-23', NULL, '3 in 1 Printer Epson L3250 Wireless All-in-One Ink Tank Printer', NULL, 'LI43-HV-ICTE-2022-0044', NULL, 'Working Unit'),
(42, 'ICT-LC_2VMBAC-PR002', 'Printer Photocopier', 'ACCREDITATION', 'Dr. Nerrie E. Malaluan', 'VMB/2nd Floor/AC', 95000.00, '2020-02-17', NULL, 'Photocopier Machine (Gestener)', NULL, '0146-20', NULL, 'Not Powering on'),
(43, 'ICT-LC_2VMBAC-PR003', 'Printer Photocopier', 'ACCREDITATION', 'Dr. Nerrie E. Malaluan', 'VMB/2nd Floor/AC', 33495.00, '2016-11-10', NULL, 'Photocopier Machine Muratec Multi Function', NULL, 'LI15-HV-OE-2016-0001', NULL, 'Defective'),
(44, 'ICT-LC_2VMBNS-PR001', 'Printer', 'NSTP', 'Asst. Prof. Elvis B. Lumanglas', 'VMB/2nd Floor/NSTP', 13000.00, '2022-03-08', NULL, 'All in One Printer/Copier Epson L3250 Printer Scanner Copier Xerox Wifi', NULL, 'LI27-HV-ICTE-2022-0001', NULL, 'Working Unit'),
(45, 'ICT-LC_VMBCE-PR001', 'Printer', 'CET', 'Dr. Emerita T. Generoso', 'VMB/3rd Floor/CET', 28280.00, '2025-01-06', NULL, 'All-in-One Printer Print, Scan, Copy, Fax with ADF; Prints up to A3+ (simplex); Connectivity: USB 2.0, LAN, & Wi-Fi; EPSON ECOTANK L14150 A3+ Wi-Fi DUPLEX WIDE-FORMAT', NULL, 'LI22-HV-ICTE-2025-0008', NULL, 'Working Unit'),
(46, 'ICT-LC_VMBCE-PR002', 'Printer', 'CET', 'Dr. Emerita T. Generoso', 'VMB/3rd Floor/CET', 10220.00, '2021-09-21', NULL, '3 in 1 Printer Wi-Fi All-in-One Ink Tank Printer Epson EcoTank L3150', NULL, 'LI22-HVICTE-2021-0021', NULL, 'Working Unit'),
(47, 'ICT-LC_VMBSP-PR001', 'Printer', 'SPORTS', 'Dr. Nerrie E. Malaluan', 'VMB/1st Floor/SPORTS', 10220.00, '2021-09-21', NULL, '3 in 1 Printer Epson Eco Tank L3150', NULL, 'LI15-HV-ICTE-2021-0022', NULL, 'Working Unit'),
(48, 'ICT-LC_VMBSP-PR002', 'Printer', 'SPORTS', 'Mr. Randy M. Manimtim', 'VMB/1st Floor/SPORTS', 12651.00, '2024-11-19', NULL, 'Brother DCP-T720DW INK TANK PRINTER', NULL, 'LI23-HV-ICTE-2024-0021', NULL, 'Working Unit'),
(49, 'ICT-LC_3VMBCT-PR001', 'Printer', 'CTE', 'Dr. Eufronia M. Magundayao', 'VMB/3rd Floor/CTE', 10220.00, '2021-09-21', NULL, '3 in 1 Printer Epson L3150', NULL, 'LI20-HVICTE-2021-0027', NULL, 'Working Unit'),
(50, 'ICT-LC_3VMBCT-PR002', 'Printer', 'CTE', 'Dr. Eufronia M. Magundayao', 'VMB/3rd Floor/CTE', 27018.00, '2024-11-19', NULL, 'All in One Epson Eco Tank L14150', NULL, 'LI20-HV-ICTE-2024-0030', NULL, 'Working Unit'),
(51, 'ICT-LC_4VMBCA-PR001', 'Printer', 'CAS', 'Assoc. Prof. Maria Lucia A. Caringal', 'VMB/4th Floor/CAS', 10220.00, '2021-09-21', NULL, 'Printer Epson L3150', NULL, 'LI18-HVICTE-2021-0025', NULL, 'Working Unit'),
(52, 'ICT-LC_4VMBCA-PR002', 'Printer', 'CAS', 'Assoc. Prof. Maria Lucia A. Caringal', 'VMB/4th Floor/CAS', 10220.00, '2021-09-21', NULL, 'Printer Epson L3150', NULL, 'LI18-HVICTE-2021-0024', NULL, 'Working Unit'),
(53, 'ICT-LC_5VMBRE-PR001', 'Printer', 'RESEARCH', 'Engr. Leoven A. Austria', 'VMB/5th Floor/RESEARCH', 10220.00, '2021-09-21', NULL, '3 in 1 Printer Epson L3150', NULL, 'LI19-HVICTE-2021--0030', NULL, 'Working Unit'),
(54, 'ICT-LC_1FCRG-PR001', 'Printer', 'RGO', 'Dr. Monnette M. Suquiat', '1st Floor/RGO', 10220.00, '2021-12-14', NULL, '3 in 1 Printer Epson Eco Tank L3250', NULL, 'LI39-HVICTE-2021-0040', NULL, 'Working Unit'),
(55, 'ICT-LI_2ABBHS-PR001', 'Printer', 'HS', 'Dr. Nerrie E. Malaluan', 'ABB/2nd Floor/HS', 10225.00, '2022-11-23', NULL, '3 in 1 Printer Epson L3250', NULL, 'LI25-HV-ICTE-2022-0039', NULL, 'Working Unit'),
(56, 'ICT-LI_3ABBCA-PR001', 'Printer', 'CABE', 'Dr. Monnette M. Suquiat', 'ABB/3rd Floor/CABE', 8790.00, '2022-05-24', NULL, '3 in 1 Printer Epson L3210', NULL, 'LI17-HVICTE-2022-0011', NULL, 'Working Unit'),
(57, 'ICT-LI_3ABBCA-PR001_DUP', 'Printer', 'CABE', 'Dr. Monnette M. Suquiat', 'ABB/3rd Floor/CABE', 10220.00, '2021-09-21', NULL, '3 in 1 Printer Epson Eco Tank', NULL, 'LI17-HVIVTE-2021-0028', NULL, 'Working Unit'),
(58, 'ICT-LI_4ABBOG-PR001', 'Printer', 'OGC', 'Ms. Maria Lourdes G. Balita', 'ABB/4th Floor/OGC', 11580.00, '2024-11-19', NULL, '3 in 1 Printer Epson Eco Tank L3556', NULL, 'LI24-HV-ICTE-2024-0024', NULL, 'Working Unit'),
(59, 'ICT-LI_4ABBOG-PR002', 'Printer', 'OGC', 'Ms. Maria Lourdes G. Balita', 'ABB/4th Floor/OGC', 9750.00, '2021-08-26', NULL, '3 in 1 Printer Canon G3010', NULL, 'LI17-HV-IOTE-2021-018', NULL, 'Working Unit'),
(60, 'ICT-LI_5ABBLI-PR001', 'Printer', 'LIBRARY', 'Engr. Jonnah R. Melo', 'ABB/5th Floor/LIBRARY', 11580.00, '2024-11-19', NULL, '3 in 1 Printer Epson Eco Tank L3556', NULL, 'LI26-HV-ICTE-2024-0022', NULL, 'Working Unit'),
(61, 'ICT-LI_5ABBLI-PR002', 'Printer', 'LIBRARY', 'Engr. Jonnah R. Melo', 'ABB/5th Floor/LIBRARY', NULL, NULL, NULL, 'Epson L15150 Inkjet Color Facsimile Multifunction Printing Machine', NULL, NULL, NULL, 'Working Unit / No Property Tag');

-- --------------------------------------------------------

--
-- Table structure for table `switch`
--

CREATE TABLE `switch` (
  `id` int(11) NOT NULL,
  `asset_tag` varchar(100) DEFAULT NULL,
  `property_equipment` varchar(100) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `assigned_person` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `unit_price` decimal(12,2) DEFAULT NULL,
  `date_acquired` date DEFAULT NULL,
  `useful_life` varchar(100) DEFAULT NULL,
  `hardware_specifications` text DEFAULT NULL,
  `software_specifications` text DEFAULT NULL,
  `high_value_ics_no` varchar(100) DEFAULT NULL,
  `inventory_item_no` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `switch`
--

INSERT INTO `switch` (`id`, `asset_tag`, `property_equipment`, `department`, `assigned_person`, `location`, `unit_price`, `date_acquired`, `useful_life`, `hardware_specifications`, `software_specifications`, `high_value_ics_no`, `inventory_item_no`, `remarks`) VALUES
(1, 'ICT-LC_4LTSPF-SW001', 'Switch', 'PFMO', '', 'LTSB/4th floor/PFMO', 1994.00, '2022-03-04', NULL, NULL, NULL, 'CA-2022-0072', 'CA-2022-0072', 'Working Unit'),
(2, 'ICT-LC_4LTSCI-SW001', 'Switch', 'CICS', '', 'LTSB/4th floor/CICS', 1994.00, '2022-03-04', NULL, NULL, NULL, 'CA-2022-0081', 'CA-2022-0081', 'Working Unit'),
(3, 'ICT-LC_4LTSHR-SW001', 'Switch', 'HR', NULL, 'LTSB/3rd floor/HRMO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'ICT-LC_3LTSRD-SW001', 'Switch', 'RD', '', 'LTSB/3rd floor/RD', 1994.00, '2022-03-04', NULL, NULL, NULL, 'CA-2022-0077', 'CA-2022-0077', ''),
(5, 'ICT-LC_3LTSRE-SW001', 'Switch', 'RE', NULL, 'LTSB/4th floor/RE', 1994.00, '2022-03-04', NULL, 'D-Link DGS-1008A 8-port Gigabit Switch', NULL, 'CA-2022-0072', NULL, NULL),
(6, 'ICT-LC_3LTSCO-SW001', 'Switch', 'COA', NULL, NULL, 1994.00, '2022-03-04', NULL, 'D-Link DGS-1008A 8-port Gigabit Switch', NULL, 'CA-2022-0075', NULL, NULL),
(7, 'ICT-LC_1LTSBU-SW001', 'Switch', 'BUDGET', NULL, NULL, 1871.00, '2021-12-15', NULL, 'D-Link DGS-1008A 8-port Gigabit Switch', NULL, 'CA-2021-01156', NULL, NULL),
(8, 'IT-008', 'Switch', NULL, NULL, NULL, 8000.00, '2022-06-08', NULL, 'D-Link DGS-1008A 8-port Gigabit Switch', NULL, 'CA-0087-22', NULL, NULL),
(9, 'ICT-LC_1VMBGS-SW001', 'Switch', 'GSO', 'GSO', 'VMB/1st Floor/GSO', NULL, NULL, NULL, '24 ports switch', NULL, NULL, NULL, 'Working Unit'),
(10, 'ICT-LC_2VMBAA-SW001', 'Switch', 'VCAA', 'VCAA', 'VMB/2nd Floor/VCAA', NULL, NULL, NULL, '8 port switch', NULL, NULL, NULL, 'Working Unit'),
(11, 'ICT-LC_2VMBAC-SW001', 'Switch', 'ACCREDITATION', 'ACCREDITATION', 'VMB/2nd Floor/AC', 1994.00, '2022-03-04', NULL, 'D-Link DGS-1008A 8-port Gigabit Switch', NULL, 'CA-2022-0078', NULL, 'Working Unit'),
(12, 'ICT-LC_3VMBCT-SW001', 'Switch', 'CTE', 'CTE', 'VMB/3rd Floor/CTE', 1871.00, '2021-12-15', NULL, 'D-Link DGS-1008A 8-port Gigabit Switch', NULL, 'CA-2021-01160', NULL, 'Working Unit'),
(13, 'ICT-LC_VMBSP-SW001', 'Switch', 'SPORTS', 'SPORTS', 'VMB/1st Floor/SPORTS', NULL, NULL, NULL, '8 Port Switch', NULL, NULL, NULL, 'Working Unit'),
(14, 'ICT-LC_VMBCE-SW001', 'Switch', 'CET', 'CET', 'VMB/2nd Floor/CET', 1871.00, '2021-12-15', NULL, 'D-Link DGS-1008A 8-port Gigabit Switch', NULL, 'CA-2021-01159', NULL, 'Working Unit'),
(15, 'ICT-LC_4VMBCA-SW001', 'Switch', 'CAS', 'CAS', 'VMB/4th Floor/CAS', NULL, NULL, NULL, '8 Port Switch', NULL, NULL, NULL, 'Working Unit'),
(16, 'ICT-LC_5VMBRE-SW002', 'Switch', 'RESEARCH', 'RESEARCH', 'VMB/5th Floor/RESEARCH', 1871.00, '2021-12-15', NULL, 'D-Link DGS-1008A 8-port Gigabit Switch', NULL, 'CA-2021-01155', NULL, 'Working Unit'),
(17, 'ICT-LI_2ABBHS-SW001', 'Switch', 'HS', 'HS', 'ABB/2nd Floor/HS', 1994.00, '2022-03-04', NULL, NULL, NULL, 'CA-2022-0076', '', 'Working Unit'),
(18, 'ICT-LI_3ABBCA-SW001', 'Switch', 'CABE', 'CABE', 'ABB/3rd Floor/CABE', NULL, NULL, NULL, '24 ports Switch', NULL, NULL, NULL, 'Working Unit'),
(19, 'ICT-LI_4ABBOG-SW001', 'Switch', 'OGC', 'OGC', 'ABB/4th Floor/OGC', NULL, NULL, NULL, '8 port Switch', NULL, NULL, NULL, 'Working Unit'),
(20, 'ICT-LI_5ABBLI-SW001', 'Switch', 'LIBRARY', 'LIBRARY', 'ABB/5th Floor/LIBRARY', NULL, NULL, NULL, '8 port Switch', NULL, NULL, NULL, 'Working Unit');

-- --------------------------------------------------------

--
-- Table structure for table `system_requests`
--

CREATE TABLE `system_requests` (
  `id` int(11) NOT NULL,
  `requesting_office` varchar(255) NOT NULL,
  `type_of_request` varchar(255) NOT NULL,
  `urgency` varchar(100) DEFAULT NULL,
  `system_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `remarks` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `description`, `assigned_to`, `assigned_by`, `priority`, `status`, `due_date`, `created_at`, `updated_at`, `remarks`) VALUES
(20, 'repair', 'repair the computer in cics lab', 17, 17, 'medium', 'pending', '2025-10-10', '2025-10-08 06:44:37', '2025-10-08 06:44:37', '');

-- --------------------------------------------------------

--
-- Table structure for table `telephone`
--

CREATE TABLE `telephone` (
  `id` int(11) NOT NULL,
  `asset_tag` varchar(100) DEFAULT NULL,
  `property_equipment` varchar(100) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `assigned_person` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `unit_price` decimal(12,2) DEFAULT NULL,
  `date_acquired` date DEFAULT NULL,
  `useful_life` varchar(100) DEFAULT NULL,
  `hardware_specifications` text DEFAULT NULL,
  `software_specifications` text DEFAULT NULL,
  `high_value_ics_no` varchar(100) DEFAULT NULL,
  `inventory_item_no` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `telephone`
--

INSERT INTO `telephone` (`id`, `asset_tag`, `property_equipment`, `department`, `assigned_person`, `location`, `unit_price`, `date_acquired`, `useful_life`, `hardware_specifications`, `software_specifications`, `high_value_ics_no`, `inventory_item_no`, `remarks`) VALUES
(1, 'ICT-LC_4LTSPF-TP001', 'Telephone', 'PFMO', NULL, 'LTSB/4th floor/PFMO', 4136.00, '2021-09-23', NULL, 'IP phones GRP260IP', NULL, 'CA-2021-0279', NULL, 'Working Unit'),
(2, 'ICT-LC_4LTSPR-TP001', 'Telephone', 'PROCUREMENT', NULL, 'LTSB/4th floor/PR', 4136.00, '2021-09-23', NULL, 'IP phones GRP260IP', NULL, 'CA-2021-0274', NULL, 'Working Unit'),
(3, 'ICT-LC_4LTSPS-TP001', 'Telephone', 'PSO', NULL, 'LTSB/4th floor/CICS', 4136.00, '2021-09-23', NULL, 'IP Phones GRP260IP', NULL, 'CA-2021-0285', NULL, 'Working Unit'),
(4, 'ICT-LC_4LTSCO-TP001', 'Telephone', 'COA', NULL, NULL, 2990.00, '2022-09-27', NULL, 'IP Phones GRP260IP', NULL, 'LI37-LV-ICTE-2022-0122', NULL, NULL),
(5, 'IT-005', 'Telephone', NULL, NULL, NULL, 4136.00, '2021-09-23', NULL, 'IP Phones GRP260IP', NULL, 'CA-2021-0280', NULL, NULL),
(6, 'ICT-LC_1VMBGS-TP001', 'Telephone', 'GSO', 'N/A', 'VMB/1st Floor/GSO', 4136.00, '2021-09-23', NULL, 'IP Phones GRP260IP', NULL, 'CA-2021-0288', NULL, 'Working Unit'),
(7, 'ICT-LC_2VMBAA-TP001', 'Telephone', 'VCAA', 'N/A', 'VMB/2nd Floor/VCAA', NULL, NULL, NULL, 'Grandstream', NULL, 'N/A', NULL, 'Working Unit'),
(8, 'ICT-LC_2VMBAC-TP001', 'Telephone', 'ACCREDITATION', 'N/A', 'VMB/2nd Floor/AC', NULL, NULL, NULL, 'Grandstream', NULL, 'N/A', NULL, 'Working Unit'),
(9, 'ICT-LC_2VMBNS-TP001', 'Telephone', 'NSTP', 'N/A', 'VMB/2nd Floor/NSTP', 4136.00, '2021-09-23', NULL, 'IP Phones GRP260IP', NULL, 'CA-2021-0278', NULL, 'Working Unit'),
(10, 'ICT-LC_VMBCE-TP001', 'Telephone', 'CET', 'N/A', 'VMB/3rd Floor/CTE', 4136.00, '2021-09-23', NULL, 'IP Phones GRP260IP', NULL, 'CA-2021-0287', NULL, 'Working Unit'),
(11, 'ICT-LC_VMBSP-TP001', 'Telephone', 'SPORTS', 'Dr. Ryndel V. Amorado', 'VMB/1st Floor/SPORTS', 2990.00, '2022-09-27', NULL, 'Grandstream GRP 2602 non POE', NULL, 'LI37-LV-ICTE-2022-0124', NULL, 'Working Unit'),
(12, 'ICT-LC_2VMBCT-TP001', 'Telephone', 'CTE', 'CTE', 'VMB/3rd Floor/CTE', 4136.00, '2021-09-23', NULL, 'IP Phones GRP260IP', NULL, 'CA-2021-0283', NULL, 'Working Unit'),
(13, 'ICT-LC_4VMBCA-TP001', 'Telephone', 'CAS', 'CAS', 'VMB/4th Floor/CAS', 4136.00, '2021-09-23', NULL, 'IP Phones GRP260IP', NULL, 'CA-2021-0286', NULL, 'Working Unit'),
(14, 'ICT-LC_5VMBRE-TP001', 'Telephone', 'RESEARCH', 'RESEARCH', 'VMB/5th Floor/RESEARCH', 4136.00, '2021-09-23', NULL, 'IP Phones GRP260IP', NULL, 'CA-2021-0284', NULL, 'Working Unit'),
(15, 'ICT-LC_3GZBOJ-TP001', 'Telephone', 'OJT', 'OJT', 'GZB/2nd Floor/OJT', 4136.00, '2021-09-23', NULL, 'IP Phones GRP260IP', NULL, 'CA-2021-0275', NULL, 'Working Unit'),
(16, 'ICT-LC_1FCRG-TP001', 'Telephone', 'RGO', 'RGO', '1st Floor/RGO', 4136.00, '2021-09-23', NULL, 'IP Phones GRP260IP', NULL, 'CA-2021-0281', NULL, 'Working Unit'),
(17, 'ICT-LC_1FCSE-TP001', 'Telephone', 'SECURITY', 'SECURITY', '1st Floor/SECURITY', 4136.00, '2021-09-23', NULL, 'IP Phones GRP260IP', NULL, 'CA-2021-0277', NULL, 'Working Unit'),
(18, 'ICT-LI_2ABBHS-TP001', 'Telephone', 'HS', 'HS', 'ABB/2nd Floor/HS', NULL, NULL, NULL, 'Grandstream', NULL, 'N/A', NULL, 'Working Unit'),
(19, 'ICT-LI_3ABBCA-TP001', 'Telephone', 'CABE', 'CABE', 'ABB/3rd Floor/CABE', 4136.00, '2021-09-23', NULL, 'IP Phones GRP260IP', NULL, 'CA-2021-0282', NULL, 'Working Unit'),
(20, 'ICT-LI_4ABBOG-TP001', 'Telephone', 'OGC', 'OGC', 'ABB/4th Floor/OGC', 4136.00, '2021-09-23', NULL, 'IP Phones GRP260IP', NULL, 'CA-2021-0276', NULL, 'Working Unit'),
(21, 'ICT-LI_5ABBLI-TP001', 'Telephone', 'LIBRARY', 'LIBRARY', 'ABB/5th Floor/LIBRARY', NULL, NULL, NULL, 'Grandstream', NULL, 'N/A', NULL, 'Working Unit');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('admin','technician','department_admin') NOT NULL DEFAULT 'technician',
  `phone_number` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `role`, `phone_number`, `password`, `created_at`, `updated_at`) VALUES
(8, 'Emman', 'senpai@gmail.com', 'admin', '12345678901', '$2y$10$7VhW09z6NC2.BMx7F5LgP.6MZG8JHb./9oWYqcwOMKmloxHa3318C', '2025-09-28 03:27:51', '2025-09-28 03:27:51'),
(9, 'sir', 'dimaculanganemmanuel9@gmail.com', 'technician', '09918298717', '$2y$10$mLDHDeVzmTXGHkef8i0GSudkb/6CCIOfmEZngWWJc0IxRGh1g0HCq', '2025-09-28 04:36:49', '2025-09-28 04:36:49'),
(10, 'barrin', 'kantoworks03@gmail.com', 'technician', '09123455678', '$2y$10$ds2S7qfo0JVVVjMBLXUKKO4ogV6Kwtl2iQfk/24BclutBEFxR2CzG', '2025-09-28 04:38:25', '2025-09-28 04:38:25'),
(14, 'barriah', 'barriah@gmail.com', 'department_admin', '09812345671', '$2y$10$SilMZp9fko9.2UDkgWftjeaIqe.2aaKWklyWYpwWxhOdZSri1SpaW', '2025-10-04 04:45:10', '2025-10-04 04:45:10'),
(15, 'Emmanuel M. Dimaculangan', '22-34069@g.batstate-u.edu.ph', 'admin', '09918298717', '$2y$10$wVm8EaIDJ8UjSANiI6dhFu5LA/nlSKUbqYS1CPj8Eq/oRQEYyxzGa', '2025-10-04 06:30:26', '2025-10-04 06:30:26'),
(16, 'barron', '22-37117@g.batstate-u.edu.ph', 'department_admin', '09918298685', '$2y$10$AWXSiW5OYiH9Vvds3EdkSO14XsnO/28uIRKQZ.ui1kHE5dAZ5u1f2', '2025-10-07 08:15:04', '2025-10-07 08:15:04'),
(17, 'carlos', '22-34593@g.batstate-u.edu.ph', 'technician', '12345678901', '$2y$10$bwmT9rKSi.qRi81oCLViUOjtNJ7xHBoen7UD5sDWAe5Xn/sjHEc3a', '2025-10-08 06:37:12', '2025-10-08 06:37:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `equipment_categories`
--
ALTER TABLE `equipment_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `maintenance_records`
--
ALTER TABLE `maintenance_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `technician_id` (`technician_id`);

--
-- Indexes for table `system_requests`
--
ALTER TABLE `system_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=244;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment_categories`
--
ALTER TABLE `equipment_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `history`
--
ALTER TABLE `history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_records`
--
ALTER TABLE `maintenance_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `system_requests`
--
ALTER TABLE `system_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `maintenance_records`
--
ALTER TABLE `maintenance_records`
  ADD CONSTRAINT `maintenance_records_ibfk_2` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


CREATE TABLE password_reset_otps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_email (email)
);
