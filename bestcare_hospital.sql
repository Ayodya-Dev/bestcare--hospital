-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: Aug 14, 2026 at 02:33 PM
-- Server version: 9.6.0
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bestcare_hospital`
--

CREATE DATABASE IF NOT EXISTS `bestcare_hospital`;
USE `bestcare_hospital`;

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
CREATE TABLE IF NOT EXISTS `appointments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `staff_id` int NOT NULL,
  `service_id` int NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('Pending','Confirmed','Completed','Cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `staff_id` (`staff_id`),
  KEY `service_id` (`service_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `staff_id`, `service_id`, `appointment_date`, `appointment_time`, `status`, `created_at`) VALUES
(1, 2, 2, 4, '2026-07-29', '11:00:00', 'Cancelled', '2026-07-25 07:11:47'),
(2, 2, 1, 4, '2026-07-25', '16:00:00', 'Completed', '2026-07-25 08:05:25'),
(3, 2, 2, 2, '2026-07-31', '15:30:00', 'Completed', '2026-07-25 08:13:09'),
(4, 2, 1, 5, '2026-07-31', '16:00:00', 'Completed', '2026-07-25 08:17:48'),
(5, 2, 2, 6, '2026-08-28', '10:30:00', 'Pending', '2026-07-25 14:17:27'),
(6, 2, 3, 5, '2026-07-31', '11:00:00', 'Pending', '2026-07-25 15:56:01'),
(7, 2, 3, 4, '2026-07-31', '16:00:00', 'Pending', '2026-07-26 06:09:40'),
(8, 2, 1, 1, '2026-07-31', '15:00:00', 'Confirmed', '2026-07-26 07:38:41');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE IF NOT EXISTS `departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `image_path`) VALUES
(1, 'General Medicine', 'General consultations and primary care', NULL),
(2, 'Cardiology', 'Heart and blood vessel care', NULL),
(3, 'Laboratory', 'Blood tests and lab investigations', NULL),
(4, 'Emergency', '24-hour emergency care', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

DROP TABLE IF EXISTS `medical_records`;
CREATE TABLE IF NOT EXISTS `medical_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `staff_id` int NOT NULL,
  `diagnosis` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `visit_date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `staff_id` (`staff_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `medical_records`
--

INSERT INTO `medical_records` (`id`, `patient_id`, `staff_id`, `diagnosis`, `notes`, `visit_date`) VALUES
(1, 2, 1, 'hiv', 'test', '2026-07-25');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

DROP TABLE IF EXISTS `patients`;
CREATE TABLE IF NOT EXISTS `patients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('Male','Female','Other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `user_id`, `full_name`, `dob`, `gender`, `contact`, `address`) VALUES
(1, 4, 'Saman Perera', '1995-05-12', 'Male', '0771234567', 'No 12, Main Street, Matara'),
(5, 12, 'Ayodya Sasanka', '2002-03-29', 'Male', '+94765550166', 'D38/6 Malwarushawa,Dehiowita');

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

DROP TABLE IF EXISTS `prescriptions`;
CREATE TABLE IF NOT EXISTS `prescriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `staff_id` int NOT NULL,
  `medication` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dosage` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `issued_date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `staff_id` (`staff_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`id`, `patient_id`, `staff_id`, `medication`, `dosage`, `issued_date`) VALUES
(1, 2, 1, '100mg ', '1', '2026-07-25'),
(2, 2, 1, '100mg ', '1', '2026-07-25');

-- --------------------------------------------------------

--
-- Table structure for table `queries`
--

DROP TABLE IF EXISTS `queries`;
CREATE TABLE IF NOT EXISTS `queries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `subject` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Open','Replied','Closed') COLLATE utf8mb4_unicode_ci DEFAULT 'Open',
  `reply` text COLLATE utf8mb4_unicode_ci,
  `replied_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `replied_by` (`replied_by`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `queries`
--

INSERT INTO `queries` (`id`, `patient_id`, `subject`, `message`, `status`, `reply`, `replied_by`, `created_at`) VALUES
(1, 2, 'tesint my', 'ntghnhn', 'Closed', NULL, NULL, '2026-07-26 05:13:09'),
(2, 2, 'hello', 'sfffdfdfdf', 'Replied', 'yes gooodhui', 1, '2026-07-26 06:23:31'),
(3, 2, 'scsdc', 'fdfvg', 'Open', NULL, NULL, '2026-07-26 06:36:05');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `department_id` int NOT NULL,
  `fee` decimal(10,2) NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `department_id`, `fee`, `image_path`) VALUES
(1, 'General Consultation', 'Basic doctor consultation', 1, 1500.00, NULL),
(2, 'Specialist Consultation', 'Specialist doctor appointment', 2, 3500.00, NULL),
(3, 'Blood Test', 'Full blood count and basic labs', 3, 2000.00, NULL),
(4, 'ECG', 'Electrocardiogram heart test', 2, 2500.00, NULL),
(5, 'Emergency Care', 'Urgent emergency treatment', 4, 5000.00, NULL),
(6, 'X RAY', 'ADWEFEDGVFGVFV', 4, 1500.00, '/bestcare-hospital/assets/uploads/services/service_1784987749_8524.png');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
CREATE TABLE IF NOT EXISTS `staff` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `specialization` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_id` int NOT NULL,
  `contact` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `staff_type` enum('Doctor','Hospital Staff','Other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Doctor',
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `department_id` (`department_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `user_id`, `full_name`, `specialization`, `department_id`, `contact`, `staff_type`, `image_path`) VALUES
(1, 2, 'Dr. Nimal Silva', 'General Physician', 1, '0712345678', 'Doctor', NULL),
(2, 3, 'Dr. Kamala Fernando', 'Cardiologist', 2, '0723456789', 'Doctor', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `test_results`
--

DROP TABLE IF EXISTS `test_results`;
CREATE TABLE IF NOT EXISTS `test_results` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `test_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `result_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `result_date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `test_results`
--

INSERT INTO `test_results` (`id`, `patient_id`, `test_name`, `result_text`, `result_date`) VALUES
(1, 2, 'blood count', 'low', '2026-07-25');

-- --------------------------------------------------------

--
-- Table structure for table `treatment_plans`
--

DROP TABLE IF EXISTS `treatment_plans`;
CREATE TABLE IF NOT EXISTS `treatment_plans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `staff_id` int NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plan_details` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('Active','Completed','On Hold') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `staff_id` (`staff_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `treatment_plans`
--

INSERT INTO `treatment_plans` (`id`, `patient_id`, `staff_id`, `title`, `plan_details`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(1, 2, 1, 'surgeyr', 'dewfef', '2026-07-26', '2026-07-24', 'Active', '2026-07-26 07:47:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','staff','patient') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `is_active`, `created_at`) VALUES
(1, 'admin', '$2y$12$/erZxYYeCvUglSI7qZ1eQuK7rxMG/BTAYTBqu7Kb2XnJkaDE.ok9q', 'admin', 1, '2026-07-22 18:35:58'),
(2, 'drsilva', '$2y$12$/erZxYYeCvUglSI7qZ1eQuK7rxMG/BTAYTBqu7Kb2XnJkaDE.ok9q', 'staff', 1, '2026-07-22 18:35:58'),
(12, 'ayodya@gmail.com', '$2y$10$GXa3T1gzn/DF.qCsh3tgeeieruX/KpO3PJv07Pf/yCajXIz5gDOaO', 'patient', 1, '2026-08-13 13:23:38');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
