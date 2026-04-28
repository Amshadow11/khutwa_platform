-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 28 أبريل 2026 الساعة 01:42
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `khutwa_db`
--

-- --------------------------------------------------------

--
-- بنية الجدول `applications`
--

CREATE TABLE `applications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `job_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `cover_letter` text DEFAULT NULL,
  `cv_path` varchar(255) DEFAULT NULL,
  `about` text DEFAULT NULL,
  `applicant_name` varchar(150) DEFAULT NULL,
  `applicant_email` varchar(150) DEFAULT NULL,
  `applicant_phone` varchar(20) DEFAULT NULL,
  `status` enum('pending','viewed','shortlisted','interview','accepted','rejected') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `status_updated_at` timestamp NULL DEFAULT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `applications`
--

INSERT INTO `applications` (`id`, `job_id`, `user_id`, `cover_letter`, `cv_path`, `about`, `applicant_name`, `applicant_email`, `applicant_phone`, `status`, `notes`, `status_updated_at`, `applied_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 25, 24, NULL, 'uploads/login (1).png', 'بروفسور', 'عمار', 'ayman@gmail.com', '0771890884', 'pending', NULL, NULL, '2024-12-15 04:35:34', '2024-12-15 04:35:34', '2024-12-15 04:35:34', NULL),
(2, 27, 22, NULL, 'uploads/assignment, programming excersises.pdf', ';k;lklk;lk', 'ammar anis mohammed', 'ammar@gmail.com', '778188203', 'pending', NULL, NULL, '2025-11-04 18:40:31', '2025-11-04 18:40:31', '2025-11-04 18:40:31', NULL),
(3, 25, 22, NULL, '', '', '', '', '', 'pending', NULL, NULL, '2025-11-12 22:12:28', '2025-11-12 22:12:28', '2025-11-12 22:12:28', NULL),
(4, 31, 25, NULL, '', '', '', '', '', 'pending', NULL, NULL, '2025-11-12 23:13:21', '2025-11-12 23:13:21', '2025-11-12 23:13:21', NULL),
(5, 30, 25, NULL, '', '', '', '', '', 'accepted', NULL, NULL, '2025-11-12 23:21:27', '2025-11-12 23:21:27', '2025-11-12 23:21:27', NULL),
(6, 31, 22, NULL, '', '', '', '', '', 'viewed', NULL, NULL, '2025-12-08 22:45:10', '2025-12-08 22:45:10', '2026-04-26 01:09:40', NULL),
(7, 30, 22, NULL, '', '', '', '', '', 'pending', NULL, NULL, '2025-12-08 23:11:28', '2025-12-08 23:11:28', '2025-12-08 23:11:28', NULL),
(8, 32, 22, 'ارجوا قبولي في الوظيفة', 'cvs/2026/04/Wig1lfTzIvUQdpe76P7274hLPydy0db67rvFuF7O.pdf', NULL, 'عمار انيس', 'ammar@gmail.com', '0778188209', 'viewed', NULL, NULL, '2026-04-26 02:17:20', '2026-04-26 02:17:20', '2026-04-26 02:31:20', NULL);

-- --------------------------------------------------------

--
-- بنية الجدول `application_status_history`
--

CREATE TABLE `application_status_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','viewed','shortlisted','interview','accepted','rejected') NOT NULL,
  `note` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `application_status_history`
--

INSERT INTO `application_status_history` (`id`, `application_id`, `status`, `note`, `changed_at`, `created_at`, `updated_at`) VALUES
(1, 6, 'pending', NULL, '2026-04-26 01:09:35', '2026-04-26 01:09:35', '2026-04-26 01:09:35'),
(2, 6, 'viewed', NULL, '2026-04-26 01:09:40', '2026-04-26 01:09:40', '2026-04-26 01:09:40'),
(3, 8, 'viewed', NULL, '2026-04-26 02:31:20', '2026-04-26 02:31:20', '2026-04-26 02:31:20');

-- --------------------------------------------------------

--
-- بنية الجدول `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `companies`
--

CREATE TABLE `companies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `phone_code` varchar(10) DEFAULT 'YE',
  `logo` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `founded_year` year(4) DEFAULT NULL,
  `company_size` enum('startup','small','medium','large') NOT NULL DEFAULT 'small',
  `subscription_plan` varchar(50) NOT NULL DEFAULT 'free',
  `subscription` tinyint(1) NOT NULL DEFAULT 0,
  `subscription_started` date DEFAULT NULL,
  `subscription_end` date DEFAULT NULL,
  `status` enum('active','inactive','pending') NOT NULL DEFAULT 'pending',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `views` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `role` varchar(20) NOT NULL DEFAULT 'company',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `companies`
--

INSERT INTO `companies` (`id`, `company_name`, `email`, `password`, `phone`, `phone_code`, `logo`, `profile_picture`, `description`, `address`, `website`, `industry`, `founded_year`, `company_size`, `subscription_plan`, `subscription`, `subscription_started`, `subscription_end`, `status`, `is_verified`, `views`, `role`, `last_login`, `created_at`, `updated_at`, `deleted_at`) VALUES
(42, 'هائل سعيد أنعم', 'hael@gmail.com', '$2y$10$jBnRWRzeN9Ks.n6gj0vUjemG7/sJbLgbv99MAhfU0EPaptnQhG3j2', '771890884', 'YE', NULL, 'uploads/5be190c18ff4a-4a36eedfd045ba0f19b64a4977632e48.png', NULL, NULL, NULL, NULL, NULL, 'small', 'free', 0, NULL, NULL, 'pending', 0, 0, 'company', NULL, '2025-11-05 22:21:35', '2025-11-05 22:21:35', NULL),
(43, 'ايمن صلاح', 'ayman@gmail.com', '$2y$10$WGtqsIhU4ecKxzGnXhYnh.HEDsIOht/l7bgSlIFjWvntJPFrm8HaC', '7781564', 'YE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'small', 'free', 0, NULL, NULL, 'inactive', 1, 0, 'company', NULL, '2025-11-05 22:21:35', '2025-11-05 22:21:35', NULL),
(44, 'ايمن صلاح', 'aym@gmail.com', '$2y$12$2/1oy5L3uCrWgxbDjzpLoeF5jkh2GhikmVyYXgISedYRhyNAM4kIm', '77845654', 'YE', NULL, NULL, 'frgergre', NULL, NULL, NULL, NULL, 'small', 'free', 0, NULL, NULL, 'active', 0, 5, 'company', NULL, '2025-11-05 22:21:35', '2026-04-26 01:03:10', NULL),
(45, 'company', 'company@khutwa.com', '$2y$10$ij0zMN6G2.Av.FduOFlqH.a0L3ErUvyBBjmeWB1SZ9/nsCDsA1Dni', '45645464', 'YE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'small', 'free', 0, NULL, NULL, 'pending', 0, 0, 'company', NULL, '2025-11-05 22:21:35', '2025-11-05 22:21:35', NULL),
(47, 'Ammar', 'ammaranis@gmail.com', '$2y$10$FOc4Rk7Mtxt7sGN577i8U.4gMfLpey2IviUWhFNL/f4g7W4rUl3fO', '54646456', 'YE', NULL, NULL, 'swcwc', 'dscwc', '', NULL, NULL, 'small', '', 0, NULL, NULL, 'pending', 1, 3, 'company', NULL, '2025-11-11 20:10:54', '2025-12-08 22:49:00', NULL),
(48, 'Ammar Co', 'ammarco@gmail.com', '$2y$12$jmz6UCEnalwNfaX85C4hF.w/RI/F8fXCMibU2sfGy3ZAKRbqOqtdC', '0778188209', 'YE', NULL, NULL, NULL, NULL, 'https://github.com', 'government', NULL, 'medium', 'free', 0, NULL, NULL, 'pending', 1, 0, 'company', NULL, '2026-04-26 22:36:17', '2026-04-26 22:36:17', NULL);

-- --------------------------------------------------------

--
-- بنية الجدول `conversations`
--

CREATE TABLE `conversations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `job_id` bigint(20) UNSIGNED DEFAULT NULL,
  `last_message` text DEFAULT NULL,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `company_unread` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_unread` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `conversations`
--

INSERT INTO `conversations` (`id`, `company_id`, `user_id`, `job_id`, `last_message`, `last_message_at`, `company_unread`, `user_unread`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 44, 22, 31, 'sfewfewf', '2026-04-26 02:30:53', 0, 0, '2026-04-26 01:39:11', '2026-04-27 00:34:34', NULL),
(2, 44, 22, 32, NULL, NULL, 0, 0, '2026-04-26 23:25:25', '2026-04-26 23:25:25', NULL);

-- --------------------------------------------------------

--
-- بنية الجدول `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `requirements` text DEFAULT NULL,
  `benefits` text DEFAULT NULL,
  `category` enum('job','training') NOT NULL DEFAULT 'job',
  `job_type` varchar(100) DEFAULT NULL,
  `experience_level` varchar(50) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `remote_work` tinyint(1) NOT NULL DEFAULT 0,
  `salary` varchar(100) DEFAULT NULL,
  `salary_range` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive','expired','draft') NOT NULL DEFAULT 'active',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `urgent` tinyint(1) NOT NULL DEFAULT 0,
  `deadline` date DEFAULT NULL,
  `views` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `views_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `post_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `jobs`
--

INSERT INTO `jobs` (`id`, `company_id`, `title`, `description`, `requirements`, `benefits`, `category`, `job_type`, `experience_level`, `location`, `remote_work`, `salary`, `salary_range`, `status`, `is_active`, `featured`, `urgent`, `deadline`, `views`, `views_count`, `post_date`, `created_at`, `updated_at`, `deleted_at`) VALUES
(25, 42, 'وظيفة برمجة', 'مطلوب متخرج في مجال البرمجة وتكنولوجيا المعلومات', NULL, NULL, 'job', 'تكنولوجيا المعلومات', NULL, 'عدن', 0, NULL, NULL, 'active', 1, 0, 0, NULL, 0, 0, '2025-11-06 01:36:25', '2024-12-15 06:30:38', '2025-11-05 22:21:35', NULL),
(26, 42, 'وظيفة برمجة', 'pkk[', NULL, NULL, 'job', 'تصميم', NULL, 'عدن', 0, NULL, NULL, 'active', 1, 0, 0, NULL, 0, 0, '2025-11-06 01:36:25', '2024-12-15 06:46:16', '2025-11-05 22:21:35', NULL),
(27, 42, '1', '1', NULL, NULL, 'job', 'هندسة', NULL, 'ابين', 0, NULL, NULL, 'active', 1, 0, 0, NULL, 0, 0, '2025-11-06 01:36:25', '2024-12-17 20:44:26', '2025-11-05 22:21:35', NULL),
(28, 44, 'عمل', 'مبيعات', NULL, NULL, 'job', 'إدارة', NULL, 'عدن', 0, NULL, NULL, 'active', 1, 0, 0, NULL, 5, 0, '2025-11-06 01:36:25', '2025-11-01 00:37:59', '2025-12-13 00:21:43', NULL),
(30, 47, 'sdfdfs', 'csdafeafewewf', 'ewfwef', 'ewffwe', 'job', 'دوام جزئي', 'مبتدئ', 'dsfsddsdsf', 0, '52222', NULL, 'active', 1, 0, 0, NULL, 12, 0, '2025-11-11 23:00:06', '2025-11-11 23:00:06', '2026-04-26 01:55:14', NULL),
(31, 44, 'job', 'ewfewfwefew', 'ewfwefewfewfwefewf', NULL, 'job', 'دوام كامل', NULL, 'feweffweef', 0, '0000', NULL, 'active', 1, 0, 0, NULL, 9, 0, '2025-11-12 23:07:27', '2025-11-12 23:07:27', '2026-01-13 23:21:13', NULL),
(32, 44, 'مهندس', 'مهندس برمجيات متخصص في تطبيقات الويب', 'شهادة بكلاريوس وخبرة 3 سنوات', NULL, 'job', 'part_time', 'mid', 'عدن', 0, NULL, NULL, 'active', 1, 0, 0, '2026-05-05', 5, 0, '2026-04-26 01:22:01', '2026-04-26 01:22:01', '2026-04-26 23:29:18', NULL),
(33, 48, 'sdvdsv', 'sdcsdcwecfewcewcewvevewvewvewewvewevewve', 'ewvewvewvewvewvewvewvew', 'ewvvewvevewvewvewvewvevvwe', 'job', 'part_time', 'junior', 'عدن', 1, NULL, NULL, 'active', 1, 0, 1, '2026-04-30', 5, 0, '2026-04-26 22:49:10', '2026-04-26 22:49:10', '2026-04-27 00:20:28', NULL);

-- --------------------------------------------------------

--
-- بنية الجدول `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `conversation_id` bigint(20) UNSIGNED NOT NULL,
  `sender_type` varchar(255) NOT NULL,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `body` text NOT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `attachment_name` varchar(255) DEFAULT NULL,
  `attachment_type` varchar(50) DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `sender_type`, `sender_id`, `body`, `attachment_path`, `attachment_name`, `attachment_type`, `read_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'App\\Models\\Company', 44, 'good to know', 'messages/2026/04/hlbTXVlugzVdd7yLjolXwK6WNPAuS6xc9iEipRNy.pdf', 'الدكومنت-2.pdf', 'application/pdf', NULL, '2026-04-26 01:39:43', '2026-04-26 01:39:43', NULL),
(2, 1, 'App\\Models\\User', 22, '', 'messages/2026/04/g7VnSv7ixNrRtbehMYUiiLCjJhNvMI2ThXTshLJq.png', 'images.png', 'image/png', NULL, '2026-04-26 01:44:04', '2026-04-26 01:44:04', NULL),
(3, 1, 'App\\Models\\User', 22, 'sfewfewf', NULL, NULL, NULL, NULL, '2026-04-26 02:30:53', '2026-04-26 02:30:53', NULL);

-- --------------------------------------------------------

--
-- بنية الجدول `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2024_01_01_000001_create_companies_table', 1),
(2, '2024_01_01_000002_create_users_table', 1),
(3, '2024_01_01_000003_create_jobs_table', 1),
(4, '2024_01_01_000004_create_applications_table', 1),
(5, '2024_01_01_000005_create_application_status_history_table', 1),
(6, '2024_01_01_000006_create_sessions_table', 1),
(7, '2024_01_01_000007_create_cache_table', 1),
(8, '2024_01_01_000008_create_password_reset_tokens_table', 1),
(9, '2024_01_02_000001_create_messages_tables', 2),
(10, '2024_01_02_000002_create_notifications_table', 2);

-- --------------------------------------------------------

--
-- بنية الجدول `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `notifications`
--

INSERT INTO `notifications` (`id`, `notifiable_type`, `notifiable_id`, `type`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('0cab8708-0284-4812-a823-5d72af25f27b', 'App\\Models\\User', 22, 'App\\Notifications\\ApplicationStatusChanged', '{\"type\":\"status_changed\",\"message\":\"\\u0634\\u0627\\u0647\\u062f\\u062a \\u0627\\u064a\\u0645\\u0646 \\u0635\\u0644\\u0627\\u062d \\u0637\\u0644\\u0628\\u0643 \\u0639\\u0644\\u0649 \\u0648\\u0638\\u064a\\u0641\\u0629 \\\"\\u0645\\u0647\\u0646\\u062f\\u0633\\\"\",\"application_id\":8,\"new_status\":\"viewed\",\"old_status\":\"pending\",\"job_title\":\"\\u0645\\u0647\\u0646\\u062f\\u0633\",\"company_name\":\"\\u0627\\u064a\\u0645\\u0646 \\u0635\\u0644\\u0627\\u062d\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/user\\/applications\\/8\"}', NULL, '2026-04-26 02:31:22', '2026-04-26 02:31:22'),
('686b775a-8cab-47c9-b1c6-80c4940f7005', 'App\\Models\\User', 22, 'App\\Notifications\\ApplicationStatusChanged', '{\"type\":\"status_changed\",\"message\":\"\\u062a\\u0645 \\u062a\\u062d\\u062f\\u064a\\u062b \\u062d\\u0627\\u0644\\u0629 \\u0637\\u0644\\u0628\\u0643 \\u0639\\u0644\\u0649 \\u0648\\u0638\\u064a\\u0641\\u0629 \\\"job\\\"\",\"application_id\":6,\"new_status\":\"pending\",\"old_status\":\"accepted\",\"job_title\":\"job\",\"company_name\":\"\\u0627\\u064a\\u0645\\u0646 \\u0635\\u0644\\u0627\\u062d\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/user\\/applications\\/6\"}', '2026-04-26 01:27:15', '2026-04-26 01:09:39', '2026-04-26 01:27:15'),
('d26f7b4b-b616-4dee-a2c0-074017367915', 'App\\Models\\User', 22, 'App\\Notifications\\ApplicationStatusChanged', '{\"type\":\"status_changed\",\"message\":\"\\u0634\\u0627\\u0647\\u062f\\u062a \\u0627\\u064a\\u0645\\u0646 \\u0635\\u0644\\u0627\\u062d \\u0637\\u0644\\u0628\\u0643 \\u0639\\u0644\\u0649 \\u0648\\u0638\\u064a\\u0641\\u0629 \\\"job\\\"\",\"application_id\":6,\"new_status\":\"viewed\",\"old_status\":\"pending\",\"job_title\":\"job\",\"company_name\":\"\\u0627\\u064a\\u0645\\u0646 \\u0635\\u0644\\u0627\\u062d\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/user\\/applications\\/6\"}', '2026-04-26 01:26:23', '2026-04-26 01:09:40', '2026-04-26 01:26:23');

-- --------------------------------------------------------

--
-- بنية الجدول `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `sessions`
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
-- إرجاع أو استيراد بيانات الجدول `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('CZVglYfipsaJdb04WZb9ov4rvF8ziW3k17nMzoej', 22, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRXdGZ2tXU3FFV2haY2VHa3ZYVlpwT2tyVlBJVWUxdjhRV0x5YnNTZSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9qb2JzLzMzIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MjI7fQ==', 1777249231),
('Ot5DtynTSx0LJnutTD20utrf2PAgr7PWAClLeDEX', NULL, '192.168.1.102', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWk5MbHQ4NkJwNXFLbXFiWWZCTkhSZ1B0R1VaYU5BUXR5eFdhdU9QTyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHA6Ly8xOTIuMTY4LjEuMTA4OjgwMDAiO31zOjU0OiJsb2dpbl9jb21wYW55XzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NDQ7fQ==', 1777250089),
('vTdXZjBuGD5p0zjO3UWzbluy0Shc8Yl5qv4XtosK', NULL, '192.168.1.102', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.7727.55 Mobile Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoicUVGRUdBUENvNHFOQ1BxOGNjRTZlVXY0Y2Zic2hNTUFNUGhoUkJpNSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xOTIuMTY4LjEuMTA4OjgwMDAvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjU0OiJsb2dpbl9jb21wYW55XzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NDQ7fQ==', 1777245541);

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `phone_code` varchar(10) DEFAULT 'YE',
  `profile_picture` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `github_url` varchar(255) DEFAULT NULL,
  `portfolio_url` varchar(255) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `experience` text DEFAULT NULL,
  `education` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `phone_verified` tinyint(1) NOT NULL DEFAULT 0,
  `role` varchar(30) NOT NULL DEFAULT 'job_seeker',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `username`, `full_name`, `email`, `password`, `phone`, `phone_code`, `profile_picture`, `profile_image`, `bio`, `address`, `birth_date`, `gender`, `linkedin_url`, `github_url`, `portfolio_url`, `skills`, `experience`, `education`, `status`, `is_active`, `email_verified`, `phone_verified`, `role`, `last_login`, `created_at`, `updated_at`, `deleted_at`) VALUES
(22, 'amm2', 'عمار انيس', 'ammar@gmail.com', '$2y$12$DaU4a/jatBEc4zoIXcOPkOJGWW4NTqop4qqMQ1ai4C17Ooib3g5Nm', '0778188209', 'YE', 'avatars/aiwTeOoN1LUhQTQwwfB238xgxKQ5hSDLcgGKgjzE.jpg', NULL, 'صثبثصب', 'عدن المنصورة', NULL, NULL, NULL, NULL, NULL, 'مهندس برمجيات', 'web application', 'يكلاريوس هندسة قسم تكنولوجيا المعلومات', 'active', 1, 0, 0, 'job_seeker', '2026-04-23 22:48:11', '2025-11-06 00:30:44', '2026-04-26 02:13:07', NULL),
(23, 'amsh', 'Ammar', 'aym@gmail.com', '$2y$10$kh0FIpStmNuqiHznMG6pF.M14nPAHMzeCC91h48xBo0mzfh6n46ZS', '7854545', 'YE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', 1, 0, 0, 'job_seeker', NULL, '2025-11-11 20:00:00', '2025-11-11 20:00:00', NULL),
(24, 'aaa', '', 'ayman@gmail.com', '$2y$10$fArDHpS8wzRwuwkzlYEcv.McBxpaCS6vu7ZgHYyhhIu6UqNIBrNsq', '4565456', 'YE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', 1, 0, 0, 'job_seeker', NULL, '2025-11-11 20:01:42', '2025-11-11 20:01:42', NULL),
(25, 'am23', '', 'amm@gmail.com', '$2y$10$jhYuoLOYBF7wuABlCzoDDugkIk83WWVyC7vs9O.AEqHSN2xYZPJv6', '56454', 'YE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', 1, 0, 0, 'job_seeker', '2025-11-12 23:35:17', '2025-11-12 23:08:46', '2025-11-12 23:35:17', NULL),
(26, 'ammar', 'Ammar Anis', 'ammaraniis23@gmail.com', '$2y$12$1cDEKPWKvLIFmWgsB4UrDOWNqmz4N8fKAdW9CdVTz7riy1Vq/pkdu', '0778188209', 'YE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', 1, 0, 0, 'job_seeker', NULL, '2026-04-04 18:16:27', '2026-04-04 18:16:27', NULL),
(27, 'anis', 'anis', 'ammaa@gmail.com', '$2y$12$oH4oJiOVYrDRYrge352TWOrgPxCJ7PFPGIUqY57ulYcyqRC2WDfaO', '0778188209', 'YE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', 1, 0, 0, 'job_seeker', '2026-04-04 18:18:33', '2026-04-04 18:18:06', '2026-04-04 19:00:00', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `applications_job_id_user_id_unique` (`job_id`,`user_id`),
  ADD KEY `applications_job_id_index` (`job_id`),
  ADD KEY `applications_user_id_index` (`user_id`),
  ADD KEY `applications_status_index` (`status`),
  ADD KEY `applications_applied_at_index` (`applied_at`);

--
-- Indexes for table `application_status_history`
--
ALTER TABLE `application_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_status_history_application_id_index` (`application_id`);

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
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `companies_email_unique` (`email`),
  ADD KEY `companies_status_index` (`status`),
  ADD KEY `companies_is_verified_index` (`is_verified`),
  ADD KEY `companies_subscription_plan_index` (`subscription_plan`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `conversations_company_id_user_id_job_id_unique` (`company_id`,`user_id`,`job_id`),
  ADD KEY `conversations_job_id_foreign` (`job_id`),
  ADD KEY `conversations_company_id_index` (`company_id`),
  ADD KEY `conversations_user_id_index` (`user_id`),
  ADD KEY `conversations_last_message_at_index` (`last_message_at`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_company_id_index` (`company_id`),
  ADD KEY `jobs_status_index` (`status`),
  ADD KEY `jobs_is_active_index` (`is_active`),
  ADD KEY `jobs_category_index` (`category`),
  ADD KEY `jobs_location_index` (`location`),
  ADD KEY `jobs_job_type_index` (`job_type`),
  ADD KEY `jobs_created_at_index` (`created_at`),
  ADD KEY `jobs_is_active_status_index` (`is_active`,`status`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_sender_type_sender_id_index` (`sender_type`,`sender_id`),
  ADD KEY `messages_conversation_id_index` (`conversation_id`),
  ADD KEY `messages_read_at_index` (`read_at`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`),
  ADD KEY `notifications_read_at_index` (`read_at`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

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
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_status_index` (`status`),
  ADD KEY `users_is_active_index` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `application_status_history`
--
ALTER TABLE `application_status_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `application_status_history`
--
ALTER TABLE `application_status_history`
  ADD CONSTRAINT `application_status_history_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `conversations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
