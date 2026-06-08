

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Database: `gerims_db`

-- Table structure for table `admin_responses`

CREATE TABLE `admin_responses` (
  `response_id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `response_text` text NOT NULL,
  `is_visible_to_user` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Dumping data for table `admin_responses`

INSERT INTO `admin_responses` (`response_id`, `report_id`, `admin_id`, `response_text`, `is_visible_to_user`, `created_at`) VALUES
(1, 5, 1, 'response 123', 1, '2026-05-19 11:32:02'),
(2, 7, 1, '1234', 1, '2026-05-19 14:30:27');

-- Table structure for table `announcements`


CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `content` text NOT NULL,
  `audience` enum('all','users','admins') DEFAULT 'all',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Dumping data for table `announcements`

INSERT INTO `announcements` (`announcement_id`, `admin_id`, `title`, `content`, `audience`, `is_active`, `created_at`, `expires_at`) VALUES
(1, 1, 'Welcome to GERIMS', 'Welcome to the Gender Equality Reporting and Inclusion Monitoring System. This platform is a safe space for reporting gender-related concerns. All reports are handled with strict confidentiality.', 'all', 1, '2026-04-21 15:06:50', NULL),
(2, 1, '124rasf', '125qwrs', 'all', 0, '2026-05-19 14:58:35', '2026-05-29 00:00:00');

-- Table structure for table `audit_logs`

CREATE TABLE `audit_logs` (
  `audit_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `target_table` varchar(50) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `audit_logs`


INSERT INTO `audit_logs` (`audit_id`, `user_id`, `action`, `target_table`, `target_id`, `details`, `ip_address`, `created_at`) VALUES
(1, 2, 'LOGIN', NULL, NULL, NULL, '::1', '2026-04-21 15:08:06'),
(2, 2, 'SUBMIT_FEEDBACK', 'feedbacks', 1, NULL, '::1', '2026-04-21 15:08:37'),
(3, 1, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-07 09:52:59'),
(4, 3, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-12 15:39:45'),
(5, 4, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-14 14:15:37'),
(6, 4, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-14 14:32:10'),
(7, 4, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-15 05:25:04'),
(8, 4, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-15 05:43:54'),
(9, 4, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-15 17:39:04'),
(10, 4, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-16 05:21:34'),
(11, 4, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-16 05:28:43'),
(12, 4, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-16 05:32:01'),
(13, 4, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-16 05:36:16'),
(14, 4, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-16 05:39:43'),
(15, 1, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-16 05:39:52'),
(16, 1, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-16 05:48:57'),
(17, 4, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-16 05:49:09'),
(18, 4, 'SUBMIT_REPORT', 'reports', 1, 'Title: gi yw2 ko', '::1', '2026-05-16 05:51:10'),
(19, 4, 'SUBMIT_REPORT', 'reports', 2, 'Title: owaha', '::1', '2026-05-16 05:51:52'),
(20, 4, 'SUBMIT_REPORT', 'reports', 3, 'Title: kgfashds', '::1', '2026-05-16 05:52:24'),
(21, 4, 'SUBMIT_FEEDBACK', 'feedbacks', 2, NULL, '::1', '2026-05-16 05:52:50'),
(22, 4, 'SUBMIT_FEEDBACK', 'feedbacks', 3, NULL, '::1', '2026-05-16 05:52:59'),
(23, 4, 'SUBMIT_FEEDBACK', 'feedbacks', 4, NULL, '::1', '2026-05-16 05:53:10'),
(24, 4, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 11:20:58'),
(25, 4, 'SUBMIT_REPORT', 'reports', 4, 'Title: 12345', '::1', '2026-05-19 11:22:33'),
(26, 4, 'SUBMIT_REPORT', 'reports', 5, 'Title: gender bias', '::1', '2026-05-19 11:27:29'),
(27, 4, 'SUBMIT_FEEDBACK', 'feedbacks', 5, NULL, '::1', '2026-05-19 11:28:47'),
(28, 4, 'CHANGE_PASSWORD', 'users', 4, NULL, '::1', '2026-05-19 11:30:01'),
(29, 4, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 11:30:15'),
(30, 1, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 11:30:28'),
(31, 1, 'UPDATE_REPORT_STATUS', 'reports', 5, 'New status: dismissed', '::1', '2026-05-19 11:31:28'),
(32, 1, 'UPDATE_REPORT_STATUS', 'reports', 5, 'New status: under_review', '::1', '2026-05-19 11:31:52'),
(33, 1, 'ADD_RESPONSE', 'admin_responses', 8, NULL, '::1', '2026-05-19 11:32:02'),
(34, 1, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 11:36:44'),
(35, 4, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 11:36:50'),
(36, 4, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 11:37:34'),
(37, 1, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 11:37:42'),
(38, 1, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 11:55:43'),
(39, 4, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 11:55:51'),
(40, 4, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 12:01:47'),
(41, 1, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 12:01:58'),
(42, 1, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 12:08:37'),
(43, 4, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 12:08:55'),
(44, 4, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 14:04:33'),
(45, 5, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 14:09:36'),
(46, 5, 'SUBMIT_REPORT', 'reports', 6, 'Title: 123', '::1', '2026-05-19 14:27:13'),
(47, 5, 'SUBMIT_REPORT', 'reports', 7, 'Title: 12345', '::1', '2026-05-19 14:28:53'),
(48, 5, 'SUBMIT_FEEDBACK', 'feedbacks', 6, NULL, '::1', '2026-05-19 14:29:26'),
(49, 5, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 14:30:03'),
(50, 1, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 14:30:10'),
(51, 1, 'ADD_RESPONSE', 'admin_responses', 11, NULL, '::1', '2026-05-19 14:30:27'),
(52, 1, 'UPDATE_REPORT_STATUS', 'reports', 7, 'New status: resolved', '::1', '2026-05-19 14:30:36'),
(53, 1, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 14:33:20'),
(54, 4, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 14:33:30'),
(55, 4, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 14:33:53'),
(56, 1, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 14:34:05'),
(57, 1, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 14:38:24'),
(58, 4, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 14:38:30'),
(59, 4, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 14:39:04'),
(60, 1, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 14:39:15'),
(61, 1, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 15:00:26'),
(62, 4, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 15:00:32'),
(63, 4, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 15:01:07'),
(64, 1, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 15:01:18'),
(65, 1, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 15:01:28'),
(66, 4, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 15:01:33'),
(67, 4, 'SUBMIT_REPORT', 'reports', 8, 'Title: trashtalk', '::1', '2026-05-19 15:29:41'),
(68, 4, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 15:29:47'),
(69, 1, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 15:29:56'),
(70, 1, 'DELETE_REPORT', 'reports', 8, NULL, '::1', '2026-05-19 15:30:30'),
(71, 1, 'ADD_POLICY', 'policies', 3, NULL, '::1', '2026-05-19 15:31:08'),
(72, 1, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 15:31:19'),
(73, 4, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 15:31:25'),
(74, 4, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 15:31:30'),
(75, 5, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 15:31:36'),
(76, 5, 'SUBMIT_REPORT', 'reports', 9, 'Title: 1423qrwa', '::1', '2026-05-19 15:32:03'),
(77, 5, 'SUBMIT_REPORT', 'reports', 10, 'Title: workplace', '::1', '2026-05-19 15:33:50'),
(78, 5, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 15:34:04'),
(79, 1, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 15:34:11'),
(80, 1, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 15:34:20'),
(81, 5, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 15:34:33'),
(82, 5, 'SUBMIT_REPORT', 'reports', 11, 'Title: harassment', '::1', '2026-05-19 15:35:10'),
(83, 5, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 15:35:14'),
(84, 1, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 15:35:22'),
(85, 1, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 15:36:39'),
(86, 4, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 15:36:45'),
(87, 4, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 15:36:50'),
(88, 1, 'LOGIN', NULL, NULL, NULL, '::1', '2026-05-19 15:36:55'),
(89, 1, 'LOGOUT', NULL, NULL, NULL, '::1', '2026-05-19 15:38:39');

-- Table structure for table `categories`

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(80) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'fa-flag',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `categories`


INSERT INTO `categories` (`category_id`, `category_name`, `description`, `icon`, `created_at`) VALUES
(1, 'Discrimination', 'Unfair treatment based on gender identity or expression', 'fa-ban', '2026-04-21 15:06:50'),
(2, 'Sexual Harassment', 'Unwanted sexual advances or conduct', 'fa-exclamation-triangle', '2026-04-21 15:06:50'),
(3, 'Gender Bias', 'Systematic bias favoring or disfavoring certain genders', 'fa-balance-scale', '2026-04-21 15:06:50'),
(4, 'Workplace Inequality', 'Unequal treatment in professional environments', 'fa-briefcase', '2026-04-21 15:06:50'),
(5, 'Educational Inequality', 'Unequal access or treatment in academic settings', 'fa-graduation-cap', '2026-04-21 15:06:50'),
(6, 'Verbal Abuse', 'Harmful language targeting gender identity', 'fa-comment-slash', '2026-04-21 15:06:50'),
(7, 'Other', 'Other gender-related concerns not listed above', 'fa-ellipsis-h', '2026-04-21 15:06:50');


-- Table structure for table `feedbacks`

CREATE TABLE `feedbacks` (
  `feedback_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `feedback_type` enum('general','system','policy','report') DEFAULT 'general',
  `subject` varchar(150) DEFAULT NULL,
  `message` text NOT NULL,
  `rating` tinyint(1) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `is_anonymous` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedbacks`
--

INSERT INTO `feedbacks` (`feedback_id`, `user_id`, `feedback_type`, `subject`, `message`, `rating`, `is_anonymous`, `created_at`) VALUES
(1, 2, 'system', '123qweqw', 'wqewqewqe', 5, 1, '2026-04-21 15:08:37'),
(2, 4, 'report', 'too long to respond', 'owahwaohwaowha', 1, 0, '2026-05-16 05:52:50'),
(3, 4, 'system', 'too long to respond', 'owahwaohwaowha', 1, 1, '2026-05-16 05:52:59'),
(4, 4, 'policy', 'very nice', 'owahwaohwaowha', 5, 0, '2026-05-16 05:53:10'),
(5, 4, 'policy', '123qweqw', 'messageeeee', 5, 1, '2026-05-19 11:28:47'),
(6, 5, 'general', '123qweqw', '12342qwe', 4, 0, '2026-05-19 14:29:26');

-- Table structure for table `notifications`

CREATE TABLE `notifications` (
  `notif_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `report_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `notif_type` enum('status_update','response','system','feedback') DEFAULT 'system',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `notifications`

INSERT INTO `notifications` (`notif_id`, `user_id`, `report_id`, `message`, `is_read`, `notif_type`, `created_at`) VALUES
(1, 1, 1, 'New report submitted: gi yw2 ko', 1, 'system', '2026-05-16 05:51:10'),
(2, 1, 2, 'New report submitted: owaha', 1, 'system', '2026-05-16 05:51:52'),
(3, 1, 3, 'New report submitted: kgfashds', 1, 'system', '2026-05-16 05:52:24'),
(4, 1, 4, 'New report submitted: 12345', 1, 'system', '2026-05-19 11:22:33'),
(5, 1, 5, 'New report submitted: gender bias', 1, 'system', '2026-05-19 11:27:29'),
(6, 4, 5, 'Your report \"gender bias\" status changed to: Dismissed', 1, 'status_update', '2026-05-19 11:31:28'),
(7, 4, 5, 'Your report \"gender bias\" status changed to: Under review', 1, 'status_update', '2026-05-19 11:31:52'),
(8, 4, 5, 'Admin responded to your report: \"gender bias\"', 1, 'response', '2026-05-19 11:32:02'),
(9, 1, 6, 'New report submitted: 123', 1, 'system', '2026-05-19 14:27:13'),
(10, 1, 7, 'New report submitted: 12345', 1, 'system', '2026-05-19 14:28:53'),
(11, 5, 7, 'Admin responded to your report: \"12345\"', 1, 'response', '2026-05-19 14:30:27'),
(12, 5, 7, 'Your report \"12345\" status changed to: Resolved', 1, 'status_update', '2026-05-19 14:30:36'),
(13, 1, NULL, 'New report submitted: trashtalk', 1, 'system', '2026-05-19 15:29:41'),
(14, 1, 9, 'New report submitted: 1423qrwa', 1, 'system', '2026-05-19 15:32:03'),
(15, 1, 10, 'New report submitted: workplace', 1, 'system', '2026-05-19 15:33:50'),
(16, 1, 11, 'New report submitted: harassment', 1, 'system', '2026-05-19 15:35:10');

-- Table structure for table `policies`

CREATE TABLE `policies` (
  `policy_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(80) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Dumping data for table `policies`


INSERT INTO `policies` (`policy_id`, `title`, `content`, `category`, `created_by`, `is_published`, `created_at`, `updated_at`) VALUES
(1, 'Anti-Discrimination Policy', 'All individuals within this institution are entitled to a safe, inclusive, and respectful environment regardless of their gender identity or expression. Discrimination of any form will not be tolerated and will be acted upon immediately.', 'Discrimination', 1, 1, '2026-04-21 15:06:50', '2026-04-21 15:06:50'),
(2, 'Reporting Confidentiality Policy', 'All reports submitted through GERIMS are treated with the highest level of confidentiality. Reporter identities are protected and shall not be disclosed without explicit consent.', 'General', 1, 1, '2026-04-21 15:06:50', '2026-04-21 15:06:50'),
(3, 'policy', '124qwrasf', 'no harassments', 1, 1, '2026-05-19 15:31:08', '2026-05-19 15:31:08');

-- Table structure for table `reports`

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  `incident_date` date DEFAULT NULL,
  `is_anonymous` tinyint(1) DEFAULT 0,
  `status` enum('pending','under_review','resolved','dismissed') DEFAULT 'pending',
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `reports`

INSERT INTO `reports` (`report_id`, `user_id`, `category_id`, `title`, `description`, `location`, `incident_date`, `is_anonymous`, `status`, `priority`, `created_at`, `updated_at`) VALUES
(1, 4, 6, 'gi yw2 ko', 'ambot ato niya uy wa nako hilabti', 'sa park', '2026-05-12', 1, 'pending', 'low', '2026-05-16 05:51:10', '2026-05-16 05:51:10'),
(2, 4, 5, 'owaha', '1234qwedqwf', 'school', '2026-05-12', 0, 'pending', 'high', '2026-05-16 05:51:52', '2026-05-16 05:51:52'),
(3, 4, 3, 'kgfashds', '123asft', 'school', '2026-05-12', 1, 'pending', 'critical', '2026-05-16 05:52:24', '2026-05-16 05:52:24'),
(4, 4, 1, '12345', '12345', 'school', NULL, 1, 'pending', 'medium', '2026-05-19 11:22:33', '2026-05-19 11:22:33'),
(5, 4, 3, 'gender bias', 'gender bias', 'school', '2026-05-19', 0, 'under_review', 'low', '2026-05-19 11:27:29', '2026-05-19 11:31:52'),
(6, 5, 1, '123', '123', '123', '2026-05-19', 0, 'pending', 'medium', '2026-05-19 14:27:13', '2026-05-19 14:27:13'),
(7, 5, 5, '12345', '12345', 'school', '2026-05-19', 1, 'resolved', 'low', '2026-05-19 14:28:53', '2026-05-19 14:30:36'),
(9, 5, 7, '1423qrwa', 'others', '123', '2026-05-19', 0, 'pending', 'medium', '2026-05-19 15:32:03', '2026-05-19 15:32:03'),
(10, 5, 4, 'workplace', 'not equl', 'workplace', '2026-05-19', 0, 'pending', 'medium', '2026-05-19 15:33:50', '2026-05-19 15:33:50'),
(11, 5, 2, 'harassment', 'harasssment', 'market', '2026-05-19', 0, 'pending', 'medium', '2026-05-19 15:35:10', '2026-05-19 15:35:10');


--
-- Table structure for table `report_status_logs`
--

CREATE TABLE `report_status_logs` (
  `log_id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `old_status` varchar(30) DEFAULT NULL,
  `new_status` varchar(30) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report_status_logs`
--

INSERT INTO `report_status_logs` (`log_id`, `report_id`, `changed_by`, `old_status`, `new_status`, `remarks`, `changed_at`) VALUES
(1, 5, 1, 'pending', 'dismissed', 'idk', '2026-05-19 11:31:28'),
(2, 5, 1, 'dismissed', 'under_review', '', '2026-05-19 11:31:52'),
(3, 7, 1, 'pending', 'resolved', 'ok na', '2026-05-19 14:30:36');


--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `gender` varchar(30) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `year_level` varchar(20) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `profile_pic` varchar(255) DEFAULT 'default.png',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `gender`, `course`, `year_level`, `contact_number`, `role`, `profile_pic`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'System Administrator', 'admin@gerims.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Prefer not to say', NULL, NULL, NULL, 'admin', 'default.png', 1, '2026-04-21 15:06:50', '2026-04-21 15:06:50'),
(2, 'ansoni gian bonjor', 'ansonigian@gmail.com', '$2y$10$w83KXhFIBu.LzBpFkNIrE.o0ThgPYJEB6tr/eDPpAOt71SGdCnPg6', 'Male', 'BSIT', '1st Year', '09123456789', 'user', 'default.png', 1, '2026-04-21 15:07:52', '2026-04-21 15:07:52'),
(3, 'ansoni gian bonjo', 'anthony@gmail.com', '$2y$10$3rxLema7JKiq/TFQD.KtFO6Sqqj2Axpn9Dp7ZlUOsyu.r5K49dxpG', '', 'BSIT', '', '09123456780', 'user', 'default.png', 1, '2026-05-12 15:39:02', '2026-05-12 15:39:02'),
(4, 'ansoni gian bonjorno', 'anthony123@gmail.com', '$2y$10$rpjOBi.1R.2.hh4xD0xvw.WCm6NWdf0ZZdaj2tLqpGNpqbrHWOVUu', 'Male', 'BSIT', '2nd Year', '09123456789', 'user', 'default.png', 1, '2026-05-14 14:15:25', '2026-05-19 11:30:01'),
(5, 'ansoni bonior', 'anthonybonior@gmail.com', '$2y$10$N6olTsEH0CngD1TU8Fv4BuyMT9OrXgAWpolizQLSq4fmOHkjyjXiC', 'Male', 'BSIT', '2nd Year', '09123456789', 'user', 'default.png', 1, '2026-05-19 14:09:21', '2026-05-19 14:09:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_responses`
--
ALTER TABLE `admin_responses`
  ADD PRIMARY KEY (`response_id`),
  ADD KEY `report_id` (`report_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`audit_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notif_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `report_id` (`report_id`);

--
-- Indexes for table `policies`
--
ALTER TABLE `policies`
  ADD PRIMARY KEY (`policy_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `report_status_logs`
--
ALTER TABLE `report_status_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `report_id` (`report_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_responses`
--
ALTER TABLE `admin_responses`
  MODIFY `response_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `audit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `feedbacks`
--
ALTER TABLE `feedbacks`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `policies`
--
ALTER TABLE `policies`
  MODIFY `policy_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `report_status_logs`
--
ALTER TABLE `report_status_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_responses`
--
ALTER TABLE `admin_responses`
  ADD CONSTRAINT `admin_responses_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `reports` (`report_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_responses_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `feedbacks`
--
ALTER TABLE `feedbacks`
  ADD CONSTRAINT `feedbacks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`report_id`) REFERENCES `reports` (`report_id`) ON DELETE SET NULL;

--
-- Constraints for table `policies`
--
ALTER TABLE `policies`
  ADD CONSTRAINT `policies_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `report_status_logs`
--
ALTER TABLE `report_status_logs`
  ADD CONSTRAINT `report_status_logs_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `reports` (`report_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_status_logs_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
