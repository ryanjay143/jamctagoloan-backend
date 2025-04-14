-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 14, 2025 at 03:04 PM
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
-- Database: `jamctagoloan_backend`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `member_id` bigint(20) UNSIGNED NOT NULL,
  `status` int(11) NOT NULL COMMENT '0 = Present 1 = Absent',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id`, `member_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 0, '2025-04-12 16:43:18', '2025-04-12 16:43:18'),
(3, 9, 0, '2025-04-12 16:44:23', '2025-04-12 16:44:23'),
(4, 11, 0, '2025-04-12 16:44:35', '2025-04-12 16:44:35'),
(5, 6, 0, '2025-04-12 16:45:13', '2025-04-12 16:45:13'),
(6, 7, 1, '2025-04-12 16:45:22', '2025-04-12 16:45:22'),
(7, 16, 0, '2025-04-12 16:45:54', '2025-04-12 16:45:54'),
(8, 20, 0, '2025-04-12 16:46:06', '2025-04-12 16:46:06'),
(9, 21, 1, '2025-04-12 16:46:18', '2025-04-12 16:46:18'),
(10, 25, 0, '2025-04-12 16:46:30', '2025-04-12 16:46:30'),
(11, 26, 0, '2025-04-12 16:50:18', '2025-04-12 16:50:18'),
(12, 12, 0, '2025-04-12 16:50:43', '2025-04-12 16:50:43'),
(13, 22, 0, '2025-04-12 16:51:08', '2025-04-12 16:51:08'),
(14, 27, 0, '2025-04-12 16:53:27', '2025-04-12 16:53:27'),
(15, 15, 0, '2025-04-12 16:56:21', '2025-04-12 16:56:21'),
(16, 18, 0, '2025-04-12 16:56:33', '2025-04-12 16:56:33'),
(17, 28, 0, '2025-04-12 16:57:16', '2025-04-12 16:57:16'),
(18, 10, 0, '2025-04-12 17:00:06', '2025-04-12 17:00:06'),
(19, 13, 0, '2025-04-12 17:00:16', '2025-04-12 17:00:16'),
(20, 29, 0, '2025-04-12 17:00:30', '2025-04-12 17:00:30'),
(21, 30, 0, '2025-04-12 17:00:47', '2025-04-12 17:00:47'),
(22, 8, 0, '2025-04-12 17:04:27', '2025-04-12 17:04:27'),
(23, 31, 0, '2025-04-12 17:05:45', '2025-04-12 17:05:45'),
(24, 32, 0, '2025-04-12 17:05:52', '2025-04-12 17:05:52'),
(25, 33, 0, '2025-04-12 17:06:03', '2025-04-12 17:06:03'),
(26, 34, 0, '2025-04-12 17:07:31', '2025-04-12 17:07:31'),
(27, 36, 0, '2025-04-12 17:09:52', '2025-04-12 17:09:52'),
(28, 37, 0, '2025-04-12 17:18:39', '2025-04-12 17:18:39'),
(29, 42, 0, '2025-04-12 17:18:53', '2025-04-12 17:18:53'),
(30, 17, 0, '2025-04-12 17:19:59', '2025-04-12 17:19:59'),
(31, 5, 1, '2025-04-12 17:29:40', '2025-04-12 17:29:40'),
(32, 14, 0, '2025-04-12 17:29:49', '2025-04-12 17:29:49'),
(33, 35, 0, '2025-04-12 17:30:02', '2025-04-12 17:30:02'),
(34, 38, 1, '2025-04-12 17:30:10', '2025-04-12 17:30:10'),
(35, 39, 1, '2025-04-12 17:30:22', '2025-04-12 17:30:22'),
(36, 43, 0, '2025-04-12 17:30:47', '2025-04-12 17:30:47'),
(37, 44, 0, '2025-04-12 17:30:57', '2025-04-12 17:30:57'),
(38, 45, 0, '2025-04-12 17:31:07', '2025-04-12 17:31:07'),
(39, 46, 0, '2025-04-12 17:31:22', '2025-04-12 17:31:22'),
(40, 47, 0, '2025-04-12 17:31:36', '2025-04-12 17:31:36'),
(41, 48, 0, '2025-04-12 17:31:46', '2025-04-12 17:31:46'),
(42, 49, 1, '2025-04-12 17:31:58', '2025-04-12 17:31:58'),
(43, 24, 1, '2025-04-12 17:37:57', '2025-04-12 17:37:57'),
(44, 19, 1, '2025-04-12 17:38:06', '2025-04-12 17:38:06'),
(45, 40, 1, '2025-04-12 17:38:12', '2025-04-12 17:38:12'),
(46, 41, 1, '2025-04-12 17:38:15', '2025-04-12 17:38:15'),
(47, 50, 0, '2025-04-12 17:38:22', '2025-04-12 17:38:22'),
(48, 3, 1, '2025-04-12 17:56:03', '2025-04-12 17:56:03'),
(49, 51, 0, '2025-04-12 19:05:53', '2025-04-12 19:05:53');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `list_of_members`
--

CREATE TABLE `list_of_members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `church_status` varchar(255) DEFAULT '0',
  `attendance_status` int(255) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `list_of_members`
--

INSERT INTO `list_of_members` (`id`, `name`, `role`, `photo`, `church_status`, `attendance_status`, `created_at`, `updated_at`) VALUES
(2, 'Miguel Carlo Acebedo', 'Church Pastor', 'photos/1brALrnjuG6INFX9NyIuGVfyxukiBk0O0xWKsW9H.jpg', '0', 1, '2025-04-10 08:37:22', '2025-04-12 16:43:18'),
(3, 'Rejan Vincent Onting', 'Church Pastor', 'photos/7rIyngvRmBmo0a6NUdbZyE4yWQ4t8LA4vM5WlYbJ.jpg', '0', 1, '2025-04-10 08:43:06', '2025-04-12 17:56:03'),
(5, 'Jim Lesle Bullecer', 'Ushering Service Team', 'photos/w0atuLeua5zJ2pcmwHyVlVXAQjBv6RI9vyeLuJ8n.jpg', '0', 1, '2025-04-11 05:30:57', '2025-04-12 17:29:40'),
(6, 'Webster Burias', 'Praise & Worship Team', 'photos/fuZmyWSsyOU9IXeXaqjT6YYHcQfcYQjMNev4CCEW.jpg', '0', 1, '2025-04-11 06:07:44', '2025-04-12 16:45:13'),
(7, 'Arneth Cabunoc', 'Finance Team', 'photos/jqQO9WtIS5rWpChVOYzbSfR4qZYXsbNXvzxUw3Jf.jpg', '0', 1, '2025-04-11 06:11:24', '2025-04-12 16:45:22'),
(8, 'Arabelle Beltran', 'Ushering Service Team', NULL, '0', 1, '2025-04-11 06:23:58', '2025-04-12 17:04:27'),
(9, 'Ruby Cabunoc', 'Praise & Worship Team', NULL, '0', 1, '2025-04-11 06:26:07', '2025-04-12 16:44:23'),
(10, 'Shandy Valmorida', 'Praise & Worship Team', 'photos/XYgHknlvS1Ly1btTH0P2QDJdzoRrOXpfewkWxwrP.jpg', '0', 1, '2025-04-11 06:26:45', '2025-04-12 17:00:06'),
(11, 'Ryan Reyes', 'Multimedia Service Team', 'photos/ehlMGCvAhAzIYWfMv4YHJ9yYPkdSReQ3xdGa1vOj.jpg', '0', 1, '2025-04-11 06:42:55', '2025-04-12 16:44:35'),
(12, 'Keziah Cabunoc', 'Praise & Worship Team', 'photos/X7IxWwdAjLPONOyFj1oYfIxPWQnKAmC8AI2iHTa1.jpg', '0', 1, '2025-04-11 07:40:25', '2025-04-12 16:50:43'),
(13, 'Riadel Malmorida', 'Praise & Worship Team', 'photos/RZdBDQSNr0hGOAkX5XcQC8ikfJ4ZoRjWsoKIe3VN.jpg', '0', 1, '2025-04-11 08:12:49', '2025-04-12 17:00:16'),
(14, 'Khit Abegail Urbiztondo', 'Praise & Worship Team', 'photos/altGYnBo3ioqxPI3Q06EjyBFWeA3KjiyukKInBlx.jpg', '0', 1, '2025-04-11 08:16:59', '2025-04-12 17:29:49'),
(15, 'Santos De Cabunoc', 'Praise & Worship Team', 'photos/MzmZuXJc2mfWWpUVCrUdQL5Ov9XBD8hMuHWYdBg6.jpg', '0', 1, '2025-04-11 22:59:30', '2025-04-12 16:56:21'),
(16, 'Cecille Cabunoc', 'Ushering Service Team', 'photos/OuIcS92Lfeguyf5NCdQXwUci8lxWKCxnENcVk1oM.jpg', '0', 1, '2025-04-11 23:23:42', '2025-04-12 16:45:54'),
(17, 'Dymaii Emata Compas', 'Praise & Worship Team', NULL, '0', 1, '2025-04-11 23:29:29', '2025-04-12 17:19:59'),
(18, 'Ashlee Nicole Mabayo', 'Multimedia Service Team', 'photos/I07BCytM4I4oeuvnsPljHwfJwROYWffHoU7QZGsf.jpg', '0', 1, '2025-04-11 23:53:12', '2025-04-12 16:56:33'),
(19, 'Corazon Iman', 'Regular', 'photos/9OLhmEPP2hDGDuLtd9eFly1V4yZTDBKFiiZKZ5SG.jpg', '0', 1, '2025-04-12 00:01:30', '2025-04-12 17:38:06'),
(20, 'Ayshen Quilicot', 'Regular', 'photos/DGz6CnkUgXbhonu3QB3idzMkvL5xKvOWvHbJ009E.jpg', '0', 1, '2025-04-12 00:16:12', '2025-04-12 16:46:06'),
(21, 'Anna Maria Reb', 'Visitor', 'photos/tZ6mTNflbEP5Zh6BSwKcmiJ4JFIwPFC5ejWveWdL.jpg', '0', 1, '2025-04-12 02:46:37', '2025-04-12 16:46:18'),
(22, 'Bags Gadot', 'Regular', 'photos/m6rx8Wle4m9ZFCHRgnaxbUhug7pfOF4zkAPiemT5.jpg', '0', 1, '2025-04-12 02:58:01', '2025-04-12 16:51:08'),
(24, 'Emmanuel Mabayo', 'Praise & Worship Team', 'photos/yrc2phUf6w3NJ9hW59OpXyJ7nOIfZQoDzJUYKIEi.jpg', '0', 1, '2025-04-12 07:25:53', '2025-04-12 17:37:57'),
(25, 'Joel Casona', 'Praise & Worship Team', 'photos/GVcslFT7kRjIrdWpx20tFxBhYn0DFIR0QBsTxEDG.jpg', '0', 1, '2025-04-12 16:42:21', '2025-04-12 16:46:30'),
(26, 'Charlene Montella', 'Ushering Service Team', NULL, '0', 1, '2025-04-12 16:49:56', '2025-04-12 16:50:18'),
(27, 'Lovern Gadot', 'Ushering Service Team', NULL, '0', 1, '2025-04-12 16:53:06', '2025-04-12 16:53:27'),
(28, 'Marlyn Zalvan', 'Prayer Service Team', NULL, '0', 1, '2025-04-12 16:55:55', '2025-04-12 16:57:16'),
(29, 'Marife Quilicot', 'Regular', NULL, '0', 1, '2025-04-12 16:58:53', '2025-04-12 17:00:30'),
(30, 'Maeva Cabunoc', 'Multimedia Service Team', NULL, '0', 1, '2025-04-12 16:59:47', '2025-04-12 17:00:47'),
(31, 'Mckenzie Cabunoc', 'Regular', NULL, '0', 1, '2025-04-12 17:02:03', '2025-04-12 17:05:45'),
(32, 'Atarah Quilicot', 'Regular', NULL, '0', 1, '2025-04-12 17:03:26', '2025-04-12 17:05:52'),
(33, 'Ivan Quilicot', 'Regular', NULL, '0', 1, '2025-04-12 17:04:14', '2025-04-12 17:06:03'),
(34, 'Virnil Quilicot', 'Praise & Worship Team', NULL, '0', 1, '2025-04-12 17:07:15', '2025-04-12 17:07:31'),
(35, 'Edeliza Cabunoc', 'Regular', NULL, '0', 1, '2025-04-12 17:08:43', '2025-04-12 17:30:02'),
(36, 'Aya Cabunoc', 'Regular', NULL, '0', 1, '2025-04-12 17:09:31', '2025-04-12 17:09:52'),
(37, 'Shiella Mae Redoblado', 'Regular', NULL, '0', 1, '2025-04-12 17:11:41', '2025-04-12 17:18:39'),
(38, 'Ian Redoblado', 'Ushering Service Team', NULL, '0', 1, '2025-04-12 17:13:07', '2025-04-12 17:30:10'),
(39, 'Jamela Redoblado', 'Regular', NULL, '0', 1, '2025-04-12 17:14:11', '2025-04-12 17:30:22'),
(40, 'Mayla Bagalanon', 'Ushering Service Team', NULL, '0', 1, '2025-04-12 17:16:01', '2025-04-12 17:38:12'),
(41, 'Jullian Bagalanon', 'Multimedia Service Team', NULL, '0', 1, '2025-04-12 17:17:03', '2025-04-12 17:38:15'),
(42, 'Maria Dolores Jimenez', 'Ushering Service Team', NULL, '0', 1, '2025-04-12 17:18:23', '2025-04-12 17:18:53'),
(43, 'Mark Nunez', 'Cleaning Minstry', NULL, '0', 1, '2025-04-12 17:24:01', '2025-04-12 17:30:47'),
(44, 'Micth Nunez', 'Regular', NULL, '0', 1, '2025-04-12 17:24:37', '2025-04-12 17:30:57'),
(45, 'Liam Nunez', 'Regular', NULL, '0', 1, '2025-04-12 17:25:13', '2025-04-12 17:31:07'),
(46, 'Elle Nunez', 'Regular', NULL, '0', 1, '2025-04-12 17:26:04', '2025-04-12 17:31:22'),
(47, 'Caleb Nunez', 'Regular', NULL, '0', 1, '2025-04-12 17:26:53', '2025-04-12 17:31:36'),
(48, 'Theon Urbiztondo', 'Regular', NULL, '0', 1, '2025-04-12 17:28:04', '2025-04-12 17:31:46'),
(49, 'Antonio Cabunoc Jr.', 'Family life', NULL, '0', 1, '2025-04-12 17:29:25', '2025-04-12 17:31:58'),
(50, 'Marjorie Senial', 'Family life', NULL, '0', 1, '2025-04-12 17:36:02', '2025-04-12 17:38:22'),
(51, 'REYMARK PAMON', 'Visitor', NULL, '0', 1, '2025-04-12 17:58:07', '2025-04-12 19:05:53');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_04_10_142130_create_personal_access_tokens_table', 2),
(6, '2025_04_10_142706_create_list_of_members_table', 3),
(11, '2025_04_12_043808_create_attendances_table', 4);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('3alvWcvHHZaupZBQ8u9hNWDwPDaglzOpW41xSTdu', NULL, '192.168.254.108', 'PostmanRuntime/7.43.3', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVENLbWNIdnFObTQ0VmFjUGI4c0tHamVURGQ3WkxieldxVjFBVmY0aiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTIuMTY4LjI1NC4xMDg6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1744459223),
('9z1J87xhD2Gb7KXcoF8IQB5sFACm4lSHMy3fwNWg', NULL, '192.168.254.108', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSGNaQTN3YWxMa09wQmJxQjVhREJ4S243SXRtSldEOUtiRWtiZnJhRyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTIuMTY4LjI1NC4xMDg6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1744376051),
('M2YDdDbrSEUkJFCmC6nBvW7GkuhwV1HrpxDdyhLm', NULL, '192.168.254.109', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMEUzbm9JQVJHNXBsTDJ5MjhLelNqVVkwSXBuQzRxZXlRSjZUeUhmMyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xOTIuMTY4LjI1NC4xMDk6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1744302365);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendances_member_id_foreign` (`member_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `list_of_members`
--
ALTER TABLE `list_of_members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

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
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `list_of_members`
--
ALTER TABLE `list_of_members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `list_of_members` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
