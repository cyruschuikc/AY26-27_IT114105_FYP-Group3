-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- 主機: 127.0.0.1
-- 產生時間： 2026-11-10 08:41:37
-- 伺服器版本: 10.1.30-MariaDB
-- PHP 版本： 5.6.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `teambox`
--

-- --------------------------------------------------------

--
-- 資料表結構 `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `actor_id` int(11) NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detail` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `activities`
--

INSERT INTO `activities` (`id`, `project_id`, `group_id`, `actor_id`, `action`, `detail`, `created_at`) VALUES
(1, 6, 14, 227, 'contributed', 'logged contribution "Project plan"', DATE_SUB(NOW(), INTERVAL 0 DAY)),
(2, 6, 15, 231, 'created', 'created task "Fix prototype bugs"', DATE_SUB(NOW(), INTERVAL 0 DAY)),
(3, 1, 1, 187, 'contributed', 'logged contribution "Proposal writing"', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, 1, 1, 188, 'uploaded', 'uploaded file "weekly_progress.docx"', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(5, 1, 2, 191, 'created', 'created task "Design database schema"', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(6, 1, 3, 195, 'joined_team', 'SE-1A · Team 03', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(7, 5, 11, 283, 'uploaded', 'uploaded file "group_formation.docx"', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(8, 5, 12, 287, 'created', 'created task "Submit group formation"', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(9, 1, 1, 189, 'submitted', 'submitted task "Upload final demo slides"', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(10, 2, 5, 247, 'created', 'created task "Propose project topic"', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(11, 5, 11, 283, 'uploaded', 'uploaded file "group_formation.docx"', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(12, 5, 12, 287, 'created', 'created task "Submit group formation"', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(13, 7, 17, 303, 'updated', 'updated milestone "Final submission"', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(14, 1, 1, 187, 'contributed', 'logged contribution "Proposal writing"', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(15, 1, 1, 188, 'uploaded', 'uploaded file "weekly_progress.docx"', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(16, 1, 2, 191, 'created', 'created task "Design database schema"', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(17, 1, 3, 195, 'joined_team', 'SE-1A · Team 03', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(18, 4, 8, 207, 'contributed', 'logged contribution "Project plan"', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(19, 7, 17, 303, 'updated', 'updated milestone "Final submission"', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(20, 1, 1, 189, 'submitted', 'submitted task "Upload final demo slides"', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(21, 1, 1, 187, 'contributed', 'logged contribution "Proposal writing"', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(22, 1, 1, 188, 'uploaded', 'uploaded file "weekly_progress.docx"', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(23, 1, 2, 191, 'created', 'created task "Design database schema"', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(24, 1, 3, 195, 'joined_team', 'SE-1A · Team 03', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(25, 5, 11, 283, 'uploaded', 'uploaded file "group_formation.docx"', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(26, 5, 12, 287, 'created', 'created task "Submit group formation"', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(27, 1, 1, 189, 'submitted', 'submitted task "Upload final demo slides"', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(28, 6, 14, 227, 'contributed', 'logged contribution "Project plan"', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(29, 6, 15, 231, 'created', 'created task "Fix prototype bugs"', DATE_SUB(NOW(), INTERVAL 6 DAY));

-- --------------------------------------------------------

--
-- 資料表結構 `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_group_id` int(11) DEFAULT NULL,
  `target_project_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `channels` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inapp',
  `status` enum('Draft','Scheduled','Sent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Sent',
  `scheduled_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `email_sent_count` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `announcements`
--

INSERT INTO `announcements` (`id`, `teacher_id`, `title`, `message`, `target_group_id`, `target_project_id`, `created_at`, `channels`, `status`, `scheduled_at`, `sent_at`, `email_sent_count`) VALUES
(14, 1, 'DD', 'DDD', NULL, NULL, '2026-10-31 09:16:44', 'inapp', 'Sent', NULL, NULL, 0),
(15, 1, 'ABC', 'CCC', NULL, NULL, '2026-10-31 09:17:32', 'inapp', 'Sent', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- 資料表結構 `announcement_targets`
--

CREATE TABLE `announcement_targets` (
  `id` int(11) NOT NULL,
  `announcement_id` int(11) NOT NULL,
  `target_type` enum('course','group','student') COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `announcement_targets`
--

INSERT INTO `announcement_targets` (`id`, `announcement_id`, `target_type`, `target_id`, `created_at`) VALUES
(3, 14, 'group', 1, '2026-10-31 09:16:44'),
(4, 15, 'group', 5, '2026-10-31 09:17:32');

-- --------------------------------------------------------

--
-- 資料表結構 `calendar_events`
--

CREATE TABLE `calendar_events` (
  `id` int(11) NOT NULL,
  `creator_user_id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `event_type` enum('personal','group') COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `calendar_events`
--

INSERT INTO `calendar_events` (`id`, `creator_user_id`, `group_id`, `event_type`, `event_name`, `event_date`, `start_time`, `end_time`, `description`, `created_at`) VALUES
(1, 6, NULL, 'personal', 'ffff', '2026-11-01', '00:00:00', '00:00:00', '', '2026-11-09 15:44:51'),
(2, 6, NULL, 'personal', 'vbjkl', '2026-11-01', '09:00:00', '10:00:00', '', '2026-11-09 16:15:22');

-- --------------------------------------------------------

--
-- 資料表結構 `chat_polls`
--

CREATE TABLE `chat_polls` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `creator_id` int(11) NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `chat_poll_options`
--

CREATE TABLE `chat_poll_options` (
  `id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL,
  `option_text` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `chat_poll_votes`
--

CREATE TABLE `chat_poll_votes` (
  `id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `voter_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `class_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `programme` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year_level` tinyint(4) NOT NULL,
  `section` char(1) COLLATE utf8mb4_unicode_ci NOT NULL,
  `course_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `classes`
--

INSERT INTO `classes` (`id`, `class_code`, `programme`, `year_level`, `section`, `course_id`, `teacher_id`) VALUES
(1, 'SE-1A', 'Software Engineering', 1, 'A', 1, 1),
(2, 'SE-1B', 'Software Engineering', 1, 'B', 1, 2),
(3, 'SE-2A', 'Software Engineering', 2, 'A', 1, 3),
(4, 'SE-2B', 'Software Engineering', 2, 'B', 1, 1),
(5, 'CY-1A', 'Cybersecurity', 1, 'A', 1, 1),
(6, 'CY-1B', 'Cybersecurity', 1, 'B', 1, 2),
(7, 'CY-2A', 'Cybersecurity', 2, 'A', 1, 4),
(8, 'CY-2B', 'Cybersecurity', 2, 'B', 1, 5);

-- --------------------------------------------------------

--
-- 資料表結構 `contributions`
--

CREATE TABLE `contributions` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `contribution_date` date DEFAULT NULL,
  `confirmation_status` enum('Pending','Approved','Rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `points_awarded` tinyint(1) NOT NULL DEFAULT '0',
  `confirmed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `contributions`
--

INSERT INTO `contributions` (`id`, `group_id`, `student_id`, `title`, `type`, `notes`, `contribution_date`, `confirmation_status`, `points_awarded`, `confirmed_at`, `created_at`) VALUES
(1, 1, 182, 'Proposal writing', 'Documentation', 'Wrote the proposal draft', DATE_SUB(CURDATE(), INTERVAL 6 DAY), 'Approved', 10, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 6 DAY)),
(2, 1, 183, 'Prototype coding', 'Development', 'Login module', DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'Pending', 0, NULL, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(3, 1, 184, 'UI wireframe', 'Design', 'Figma mockups', DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Approved', 8, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(4, 2, 186, 'Database schema', 'Development', 'ERD + DDL', DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Pending', 0, NULL, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(5, 2, 187, 'API documentation', 'Documentation', 'Endpoint list', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Approved', 6, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(6, 2, 188, 'Legacy code cleanup', 'Development', 'Removed dead code', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Pending', 0, NULL, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(7, 14, 222, 'Project plan', 'Documentation', 'Milestone plan', DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Approved', 10, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(8, 14, 223, 'Broken build fix', 'Development', 'Rolled back', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Rejected', 0, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY));

-- --------------------------------------------------------

--
-- 資料表結構 `contribution_votes`
--

CREATE TABLE `contribution_votes` (
  `id` int(11) NOT NULL,
  `contribution_id` int(11) NOT NULL,
  `voter_student_id` int(11) NOT NULL,
  `vote` enum('agree','disagree') COLLATE utf8mb4_unicode_ci NOT NULL,
  `voted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `contribution_votes`
--

INSERT INTO `contribution_votes` (`id`, `contribution_id`, `voter_student_id`, `vote`, `voted_at`) VALUES
(1, 3, 2, 'agree', '2026-11-01 07:05:42'),
(4, 3, 3, 'agree', '2026-11-01 07:06:22'),
(5, 3, 4, 'agree', '2026-11-01 07:06:46'),
(6, 2, 4, 'disagree', '2026-11-01 07:07:41'),
(7, 2, 2, 'agree', '2026-11-01 07:07:17'),
(9, 2, 3, 'agree', '2026-11-01 07:08:00');

-- --------------------------------------------------------

--
-- 資料表結構 `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `semester` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `courses`
--

INSERT INTO `courses` (`id`, `code`, `name`, `teacher_id`, `semester`, `created_at`) VALUES
(1, 'ITE4116M', 'Final Year Project', 1, '2026/27 S1', '2026-10-07 11:11:58'),
(2, 'ITE3118', 'Web Application Dev', 1, '2026/27 S1', '2026-10-07 11:11:58'),
(3, 'ITE3120', 'Mobile Application Dev', 2, '2026/27 S1', '2026-10-07 11:11:58'),
(4, 'ITE3220', 'Database Systems', 3, '2026/27 S1', '2026-10-07 11:11:58'),
(5, 'ITE3330', 'Software Engineering', 4, '2026/27 S1', '2026-10-07 11:11:58'),
(6, 'ITE3440', 'AI & Machine Learning', 5, '2026/27 S1', '2026-10-07 11:11:58');

-- --------------------------------------------------------

--
-- 資料表結構 `course_enrollments`
--

CREATE TABLE `course_enrollments` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `course_enrollments`
--

INSERT INTO `course_enrollments` (`id`, `course_id`, `student_id`, `enrolled_at`) VALUES
(186, 1, 182, '2026-10-20 13:29:02'),
(187, 1, 183, '2026-10-20 13:29:02'),
(188, 1, 184, '2026-10-20 13:29:02'),
(189, 1, 185, '2026-10-20 13:29:02'),
(190, 1, 186, '2026-10-20 13:29:02'),
(191, 1, 187, '2026-10-20 13:29:02'),
(192, 1, 188, '2026-10-20 13:29:02'),
(193, 1, 189, '2026-10-20 13:29:02'),
(194, 1, 190, '2026-10-20 13:29:02'),
(195, 1, 191, '2026-10-20 13:29:02'),
(196, 1, 192, '2026-10-20 13:29:02'),
(197, 1, 193, '2026-10-20 13:29:02'),
(198, 1, 194, '2026-10-20 13:29:02'),
(199, 1, 195, '2026-10-20 13:29:02'),
(200, 1, 196, '2026-10-20 13:29:02'),
(201, 1, 197, '2026-10-20 13:29:02'),
(202, 1, 198, '2026-10-20 13:29:02'),
(203, 1, 199, '2026-10-20 13:29:02'),
(204, 1, 200, '2026-10-20 13:29:02'),
(205, 1, 201, '2026-10-20 13:29:02'),
(206, 1, 202, '2026-10-20 13:29:02'),
(207, 1, 203, '2026-10-20 13:29:02'),
(208, 1, 204, '2026-10-20 13:29:02'),
(209, 1, 205, '2026-10-20 13:29:02'),
(210, 1, 206, '2026-10-20 13:29:02'),
(211, 1, 207, '2026-10-20 13:29:02'),
(212, 1, 208, '2026-10-20 13:29:02'),
(213, 1, 209, '2026-10-20 13:29:02'),
(214, 1, 210, '2026-10-20 13:29:02'),
(215, 1, 211, '2026-10-20 13:29:02'),
(216, 1, 212, '2026-10-20 13:29:02'),
(217, 1, 213, '2026-10-20 13:29:02'),
(218, 1, 214, '2026-10-20 13:29:02'),
(219, 1, 215, '2026-10-20 13:29:02'),
(220, 1, 216, '2026-10-20 13:29:02'),
(221, 1, 217, '2026-10-20 13:29:02'),
(222, 1, 218, '2026-10-20 13:29:02'),
(223, 1, 219, '2026-10-20 13:29:02'),
(224, 1, 220, '2026-10-20 13:29:02'),
(225, 1, 221, '2026-10-20 13:29:02'),
(226, 1, 222, '2026-10-20 13:29:02'),
(227, 1, 223, '2026-10-20 13:29:02'),
(228, 1, 224, '2026-10-20 13:29:02'),
(229, 1, 225, '2026-10-20 13:29:02'),
(230, 1, 226, '2026-10-20 13:29:02'),
(231, 1, 227, '2026-10-20 13:29:02'),
(232, 1, 228, '2026-10-20 13:29:02'),
(233, 1, 229, '2026-10-20 13:29:02'),
(234, 1, 230, '2026-10-20 13:29:02'),
(235, 1, 231, '2026-10-20 13:29:02'),
(236, 1, 232, '2026-10-20 13:29:02'),
(237, 1, 233, '2026-10-20 13:29:02'),
(238, 1, 234, '2026-10-20 13:29:02'),
(239, 1, 235, '2026-10-20 13:29:02'),
(240, 1, 236, '2026-10-20 13:29:02'),
(241, 1, 237, '2026-10-20 13:29:02'),
(242, 1, 238, '2026-10-20 13:29:02'),
(243, 1, 239, '2026-10-20 13:29:02'),
(244, 1, 240, '2026-10-20 13:29:02'),
(245, 1, 241, '2026-10-20 13:29:02'),
(246, 1, 1, '2026-10-20 13:29:02'),
(247, 1, 2, '2026-10-20 13:29:02'),
(248, 1, 3, '2026-10-20 13:29:02'),
(249, 1, 4, '2026-10-20 13:29:02'),
(250, 1, 242, '2026-10-20 13:29:02'),
(251, 1, 243, '2026-10-20 13:29:02'),
(252, 1, 244, '2026-10-20 13:29:02'),
(253, 1, 245, '2026-10-20 13:29:02'),
(254, 1, 246, '2026-10-20 13:29:02'),
(255, 1, 247, '2026-10-20 13:29:02'),
(256, 1, 248, '2026-10-20 13:29:02'),
(257, 1, 249, '2026-10-20 13:29:02'),
(258, 1, 250, '2026-10-20 13:29:02'),
(259, 1, 251, '2026-10-20 13:29:02'),
(260, 1, 252, '2026-10-20 13:29:02'),
(261, 1, 253, '2026-10-20 13:29:02'),
(262, 1, 254, '2026-10-20 13:29:02'),
(263, 1, 255, '2026-10-20 13:29:02'),
(264, 1, 256, '2026-10-20 13:29:02'),
(265, 1, 257, '2026-10-20 13:29:02'),
(266, 1, 258, '2026-10-20 13:29:02'),
(267, 1, 259, '2026-10-20 13:29:02'),
(268, 1, 260, '2026-10-20 13:29:02'),
(269, 1, 261, '2026-10-20 13:29:02'),
(270, 1, 262, '2026-10-20 13:29:02'),
(271, 1, 263, '2026-10-20 13:29:02'),
(272, 1, 264, '2026-10-20 13:29:02'),
(273, 1, 265, '2026-10-20 13:29:02'),
(274, 1, 266, '2026-10-20 13:29:02'),
(275, 1, 267, '2026-10-20 13:29:02'),
(276, 1, 268, '2026-10-20 13:29:02'),
(277, 1, 269, '2026-10-20 13:29:02'),
(278, 1, 270, '2026-10-20 13:29:02'),
(279, 1, 271, '2026-10-20 13:29:02'),
(280, 1, 272, '2026-10-20 13:29:02'),
(281, 1, 273, '2026-10-20 13:29:02'),
(282, 1, 274, '2026-10-20 13:29:02'),
(283, 1, 275, '2026-10-20 13:29:02'),
(284, 1, 276, '2026-10-20 13:29:02'),
(285, 1, 277, '2026-10-20 13:29:02'),
(286, 1, 278, '2026-10-20 13:29:02'),
(287, 1, 279, '2026-10-20 13:29:02'),
(288, 1, 280, '2026-10-20 13:29:02'),
(289, 1, 281, '2026-10-20 13:29:02'),
(290, 1, 282, '2026-10-20 13:29:02'),
(291, 1, 283, '2026-10-20 13:29:02'),
(292, 1, 284, '2026-10-20 13:29:02'),
(293, 1, 285, '2026-10-20 13:29:02'),
(294, 1, 286, '2026-10-20 13:29:02'),
(295, 1, 287, '2026-10-20 13:29:02'),
(296, 1, 288, '2026-10-20 13:29:02'),
(297, 1, 289, '2026-10-20 13:29:02'),
(298, 1, 290, '2026-10-20 13:29:02'),
(299, 1, 291, '2026-10-20 13:29:02'),
(300, 1, 292, '2026-10-20 13:29:02'),
(301, 1, 293, '2026-10-20 13:29:02'),
(302, 1, 294, '2026-10-20 13:29:02'),
(303, 1, 295, '2026-10-20 13:29:02'),
(304, 1, 296, '2026-10-20 13:29:02'),
(305, 1, 297, '2026-10-20 13:29:02'),
(306, 1, 298, '2026-10-20 13:29:02'),
(307, 1, 299, '2026-10-20 13:29:02'),
(308, 1, 300, '2026-10-20 13:29:02'),
(309, 1, 301, '2026-10-20 13:29:02'),
(310, 1, 302, '2026-10-20 13:29:02'),
(311, 1, 303, '2026-10-20 13:29:02'),
(312, 1, 304, '2026-10-20 13:29:02'),
(313, 1, 305, '2026-10-20 13:29:02'),
(314, 1, 306, '2026-10-20 13:29:02'),
(315, 1, 307, '2026-10-20 13:29:02'),
(316, 1, 308, '2026-10-20 13:29:02'),
(317, 1, 309, '2026-10-20 13:29:02'),
(318, 1, 310, '2026-10-20 13:29:02'),
(319, 1, 311, '2026-10-20 13:29:02'),
(320, 1, 312, '2026-10-20 13:29:02'),
(321, 1, 313, '2026-10-20 13:29:03'),
(322, 1, 314, '2026-10-20 13:29:03'),
(323, 1, 315, '2026-10-20 13:29:03'),
(324, 1, 316, '2026-10-20 13:29:03'),
(325, 1, 317, '2026-10-20 13:29:03'),
(326, 1, 318, '2026-10-20 13:29:03'),
(327, 1, 319, '2026-10-20 13:29:03'),
(328, 1, 320, '2026-10-20 13:29:03'),
(329, 1, 321, '2026-10-20 13:29:03'),
(330, 1, 322, '2026-10-20 13:29:03'),
(331, 1, 323, '2026-10-20 13:29:03'),
(332, 1, 324, '2026-10-20 13:29:03'),
(333, 1, 325, '2026-10-20 13:29:03'),
(334, 1, 326, '2026-10-20 13:29:03'),
(335, 1, 327, '2026-10-20 13:29:03'),
(336, 1, 328, '2026-10-20 13:29:03'),
(337, 1, 329, '2026-10-20 13:29:03'),
(338, 1, 330, '2026-10-20 13:29:03'),
(339, 1, 331, '2026-10-20 13:29:03'),
(340, 1, 332, '2026-10-20 13:29:03'),
(341, 1, 333, '2026-10-20 13:29:03'),
(342, 1, 334, '2026-10-20 13:29:03'),
(343, 1, 335, '2026-10-20 13:29:03'),
(344, 1, 336, '2026-10-20 13:29:03'),
(345, 1, 337, '2026-10-20 13:29:03');

-- --------------------------------------------------------

--
-- 資料表結構 `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `task_id` int(11) DEFAULT NULL,
  `contribution_id` int(11) DEFAULT NULL,
  `uploader_id` int(11) NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `stored_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Unviewed','Accepted','Rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'Unviewed',
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_note` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `files`
--

INSERT INTO `files` (`id`, `project_id`, `group_id`, `task_id`, `contribution_id`, `uploader_id`, `file_name`, `display_name`, `notes`, `stored_name`, `file_size`, `file_type`, `status`, `uploaded_at`, `reviewed_by`, `reviewed_at`, `review_note`) VALUES
(1, 1, 1, 4, NULL, 185, '4524_CA_Test_Revision.pdf', '4524_CA_Test_Revision.pdf', NULL, '4524_CA_Test_Revision.pdf', '384.35 KB', 'PDF', 'Unviewed', DATE_SUB(NOW(), INTERVAL 2 HOUR), NULL, NULL, NULL),
(2, 1, 1, 3, NULL, 184, 'weekly_progress.docx', 'weekly_progress.docx', NULL, 'weekly_progress.docx', '384.35 KB', 'DOCX', 'Unviewed', DATE_SUB(NOW(), INTERVAL 5 HOUR), NULL, NULL, NULL),
(3, 1, 2, 6, NULL, 187, 'database_schema.pdf', 'database_schema.pdf', NULL, 'database_schema.pdf', '384.35 KB', 'PDF', 'Unviewed', DATE_SUB(NOW(), INTERVAL 3 HOUR), NULL, NULL, NULL),
(4, 1, 1, 2, NULL, 183, 'prototype_demo.zip', 'prototype_demo.zip', NULL, 'prototype_demo.zip', '384.35 KB', 'ZIP', 'Unviewed', DATE_SUB(NOW(), INTERVAL 8 HOUR), NULL, NULL, NULL),
(5, 6, 14, 29, NULL, 223, 'interim_report.pdf', 'interim_report.pdf', NULL, 'interim_report.pdf', '384.35 KB', 'PDF', 'Unviewed', DATE_SUB(NOW(), INTERVAL 4 HOUR), NULL, NULL, NULL),
(6, 6, 15, 31, NULL, 227, 'task_schedule.xlsx', 'task_schedule.xlsx', NULL, 'task_schedule.xlsx', '384.35 KB', 'XLSX', 'Unviewed', DATE_SUB(NOW(), INTERVAL 6 HOUR), NULL, NULL, NULL),
(7, 5, 11, 23, NULL, 279, 'group_formation.docx', 'group_formation.docx', NULL, 'group_formation.docx', '384.35 KB', 'DOCX', 'Unviewed', DATE_SUB(NOW(), INTERVAL 2 HOUR), NULL, NULL, NULL),
(8, 5, 12, 25, NULL, 283, 'grades_summary.xlsx', 'grades_summary.xlsx', NULL, 'grades_summary.xlsx', '384.35 KB', 'XLSX', 'Unviewed', DATE_SUB(NOW(), INTERVAL 3 HOUR), NULL, NULL, NULL),
(9, 1, 1, 1, NULL, 182, 'proposal_v1.pdf', 'proposal_v1.pdf', NULL, 'proposal_v1.pdf', '384.35 KB', 'PDF', 'Accepted', DATE_SUB(NOW(), INTERVAL 48 HOUR), 1, DATE_SUB(NOW(), INTERVAL 48 HOUR), 'Reviewed'),
(10, 6, 14, 27, NULL, 222, 'team_roster.xlsx', 'team_roster.xlsx', NULL, 'team_roster.xlsx', '384.35 KB', 'XLSX', 'Accepted', DATE_SUB(NOW(), INTERVAL 72 HOUR), 1, DATE_SUB(NOW(), INTERVAL 72 HOUR), 'Reviewed'),
(11, 1, 2, 5, NULL, 186, 'proposal_draft.docx', 'proposal_draft.docx', NULL, 'proposal_draft.docx', '384.35 KB', 'DOCX', 'Rejected', DATE_SUB(NOW(), INTERVAL 50 HOUR), 1, DATE_SUB(NOW(), INTERVAL 50 HOUR), 'Reviewed'),
(12, 5, 11, 22, NULL, 278, 'final_submission.zip', 'final_submission.zip', NULL, 'final_submission.zip', '384.35 KB', 'ZIP', 'Rejected', DATE_SUB(NOW(), INTERVAL 60 HOUR), 1, DATE_SUB(NOW(), INTERVAL 60 HOUR), 'Reviewed');

-- --------------------------------------------------------

--
-- 資料表結構 `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `leader_id` int(11) DEFAULT NULL,
  `creator_id` int(11) DEFAULT NULL,
  `max_members` int(11) DEFAULT '4',
  `status` enum('Recruiting','Waiting_Send','Voting','Executing','Completed') COLLATE utf8mb4_unicode_ci DEFAULT 'Recruiting',
  `formed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `groups`
--

INSERT INTO `groups` (`id`, `project_id`, `course_id`, `name`, `leader_id`, `creator_id`, `max_members`, `status`, `formed_at`, `created_at`) VALUES
(1, 1, 1, 'Team 01', 182, 182, 4, 'Executing', '2026-09-10 10:00:00', '2026-09-10 10:00:00'),
(2, 1, 1, 'Team 02', 186, 186, 4, 'Executing', '2026-09-10 10:00:00', '2026-09-10 10:00:00'),
(3, 1, 1, 'Team 03', 190, 190, 4, 'Voting', '2026-09-10 10:00:00', '2026-09-10 10:00:00'),
(4, 1, 1, 'Team 04', 194, 194, 4, 'Waiting_Send', '2026-09-10 10:00:00', '2026-09-10 10:00:00'),
(5, 2, 1, 'Team 05', 242, 242, 4, 'Voting', '2026-09-10 10:00:00', '2026-09-10 10:00:00'),
(6, 2, 1, 'Team 06', 246, 246, 4, 'Voting', '2026-09-10 10:00:00', '2026-09-10 10:00:00'),
(7, 2, 1, 'Team 07', 250, 250, 4, 'Waiting_Send', '2026-09-10 10:00:00', '2026-09-10 10:00:00'),
(8, 4, 1, 'Team 08', 202, 202, 4, 'Completed', '2026-09-10 10:00:00', '2026-09-10 10:00:00'),
(9, 4, 1, 'Team 09', 206, 206, 4, 'Completed', '2026-09-10 10:00:00', '2026-09-10 10:00:00'),
(10, 4, 1, 'Team 10', 210, 210, 4, 'Completed', '2026-09-10 10:00:00', '2026-09-10 10:00:00'),
(11, 5, 1, 'Team 11', 278, 278, 4, 'Waiting_Send', '2026-09-10 10:00:00', '2026-09-10 10:00:00'),
(12, 5, 1, 'Team 12', 282, 282, 4, 'Waiting_Send', '2026-09-10 10:00:00', '2026-09-10 10:00:00'),
(13, 5, 1, 'Team 13', 286, 286, 4, 'Waiting_Send', '2026-09-10 10:00:00', '2026-09-10 10:00:00'),
(14, 6, 1, 'Team 14', 222, 222, 4, 'Executing', '2026-09-10 10:00:00', '2026-09-10 10:00:00'),
(15, 6, 1, 'Team 15', 226, 226, 4, 'Executing', '2026-09-10 10:00:00', '2026-09-10 10:00:00'),
(16, 6, 1, 'Team 16', 230, 230, 4, 'Voting', '2026-09-10 10:00:00', '2026-09-10 10:00:00'),
(17, 7, 1, 'Team 17', 298, 298, 4, 'Completed', '2026-09-10 10:00:00', '2026-09-10 10:00:00'),
(18, 7, 1, 'Team 18', 302, 302, 4, 'Completed', '2026-09-10 10:00:00', '2026-09-10 10:00:00'),
(19, 7, 1, 'Team 19', 306, 306, 4, 'Completed', '2026-09-10 10:00:00', '2026-09-10 10:00:00');

-- --------------------------------------------------------

--
-- 資料表結構 `group_members`
--

CREATE TABLE `group_members` (
  `group_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `role` enum('leader','member') COLLATE utf8mb4_unicode_ci DEFAULT 'member',
  `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `group_members`
--

INSERT INTO `group_members` (`group_id`, `student_id`, `project_id`, `course_id`, `role`, `joined_at`) VALUES
(1, 182, 1, 1, 'leader', '2026-09-10 10:00:00'),
(1, 183, 1, 1, 'member', '2026-09-10 10:00:00'),
(1, 184, 1, 1, 'member', '2026-09-10 10:00:00'),
(1, 185, 1, 1, 'member', '2026-09-10 10:00:00'),
(2, 186, 1, 1, 'leader', '2026-09-10 10:00:00'),
(2, 187, 1, 1, 'member', '2026-09-10 10:00:00'),
(2, 188, 1, 1, 'member', '2026-09-10 10:00:00'),
(2, 189, 1, 1, 'member', '2026-09-10 10:00:00'),
(3, 190, 1, 1, 'leader', '2026-09-10 10:00:00'),
(3, 191, 1, 1, 'member', '2026-09-10 10:00:00'),
(3, 192, 1, 1, 'member', '2026-09-10 10:00:00'),
(3, 193, 1, 1, 'member', '2026-09-10 10:00:00'),
(4, 194, 1, 1, 'leader', '2026-09-10 10:00:00'),
(4, 195, 1, 1, 'member', '2026-09-10 10:00:00'),
(4, 196, 1, 1, 'member', '2026-09-10 10:00:00'),
(4, 197, 1, 1, 'member', '2026-09-10 10:00:00'),
(5, 242, 2, 1, 'leader', '2026-09-10 10:00:00'),
(5, 243, 2, 1, 'member', '2026-09-10 10:00:00'),
(5, 244, 2, 1, 'member', '2026-09-10 10:00:00'),
(5, 245, 2, 1, 'member', '2026-09-10 10:00:00'),
(6, 246, 2, 1, 'leader', '2026-09-10 10:00:00'),
(6, 247, 2, 1, 'member', '2026-09-10 10:00:00'),
(6, 248, 2, 1, 'member', '2026-09-10 10:00:00'),
(6, 249, 2, 1, 'member', '2026-09-10 10:00:00'),
(7, 250, 2, 1, 'leader', '2026-09-10 10:00:00'),
(7, 251, 2, 1, 'member', '2026-09-10 10:00:00'),
(7, 252, 2, 1, 'member', '2026-09-10 10:00:00'),
(7, 253, 2, 1, 'member', '2026-09-10 10:00:00'),
(8, 202, 4, 1, 'leader', '2026-09-10 10:00:00'),
(8, 203, 4, 1, 'member', '2026-09-10 10:00:00'),
(8, 204, 4, 1, 'member', '2026-09-10 10:00:00'),
(8, 205, 4, 1, 'member', '2026-09-10 10:00:00'),
(9, 206, 4, 1, 'leader', '2026-09-10 10:00:00'),
(9, 207, 4, 1, 'member', '2026-09-10 10:00:00'),
(9, 208, 4, 1, 'member', '2026-09-10 10:00:00'),
(9, 209, 4, 1, 'member', '2026-09-10 10:00:00'),
(10, 210, 4, 1, 'leader', '2026-09-10 10:00:00'),
(10, 211, 4, 1, 'member', '2026-09-10 10:00:00'),
(10, 212, 4, 1, 'member', '2026-09-10 10:00:00'),
(10, 213, 4, 1, 'member', '2026-09-10 10:00:00'),
(11, 278, 5, 1, 'leader', '2026-09-10 10:00:00'),
(11, 279, 5, 1, 'member', '2026-09-10 10:00:00'),
(11, 280, 5, 1, 'member', '2026-09-10 10:00:00'),
(11, 281, 5, 1, 'member', '2026-09-10 10:00:00'),
(12, 282, 5, 1, 'leader', '2026-09-10 10:00:00'),
(12, 283, 5, 1, 'member', '2026-09-10 10:00:00'),
(12, 284, 5, 1, 'member', '2026-09-10 10:00:00'),
(12, 285, 5, 1, 'member', '2026-09-10 10:00:00'),
(13, 286, 5, 1, 'leader', '2026-09-10 10:00:00'),
(13, 287, 5, 1, 'member', '2026-09-10 10:00:00'),
(13, 288, 5, 1, 'member', '2026-09-10 10:00:00'),
(13, 289, 5, 1, 'member', '2026-09-10 10:00:00'),
(14, 222, 6, 1, 'leader', '2026-09-10 10:00:00'),
(14, 223, 6, 1, 'member', '2026-09-10 10:00:00'),
(14, 224, 6, 1, 'member', '2026-09-10 10:00:00'),
(14, 225, 6, 1, 'member', '2026-09-10 10:00:00'),
(15, 226, 6, 1, 'leader', '2026-09-10 10:00:00'),
(15, 227, 6, 1, 'member', '2026-09-10 10:00:00'),
(15, 228, 6, 1, 'member', '2026-09-10 10:00:00'),
(15, 229, 6, 1, 'member', '2026-09-10 10:00:00'),
(16, 230, 6, 1, 'leader', '2026-09-10 10:00:00'),
(16, 231, 6, 1, 'member', '2026-09-10 10:00:00'),
(16, 232, 6, 1, 'member', '2026-09-10 10:00:00'),
(16, 233, 6, 1, 'member', '2026-09-10 10:00:00'),
(17, 298, 7, 1, 'leader', '2026-09-10 10:00:00'),
(17, 299, 7, 1, 'member', '2026-09-10 10:00:00'),
(17, 300, 7, 1, 'member', '2026-09-10 10:00:00'),
(17, 301, 7, 1, 'member', '2026-09-10 10:00:00'),
(18, 302, 7, 1, 'leader', '2026-09-10 10:00:00'),
(18, 303, 7, 1, 'member', '2026-09-10 10:00:00'),
(18, 304, 7, 1, 'member', '2026-09-10 10:00:00'),
(18, 305, 7, 1, 'member', '2026-09-10 10:00:00'),
(19, 306, 7, 1, 'leader', '2026-09-10 10:00:00'),
(19, 307, 7, 1, 'member', '2026-09-10 10:00:00'),
(19, 308, 7, 1, 'member', '2026-09-10 10:00:00'),
(19, 309, 7, 1, 'member', '2026-09-10 10:00:00');

--
-- 觸發器 `group_members`
--
DELIMITER $$
CREATE TRIGGER `trg_group_members_validate_insert` BEFORE INSERT ON `group_members` FOR EACH ROW BEGIN
            DECLARE v_project_id INT;
            DECLARE v_course_id INT;
            DECLARE v_project_class_id INT;
            DECLARE v_allow_cross_class TINYINT DEFAULT 0;
            DECLARE v_student_class_id INT;

            SELECT g.project_id, g.course_id, p.class_id, p.allow_cross_class
              INTO v_project_id, v_course_id, v_project_class_id, v_allow_cross_class
              FROM groups g
              INNER JOIN projects p ON p.id = g.project_id
             WHERE g.id = NEW.group_id;

            SELECT class_id INTO v_student_class_id
              FROM students WHERE id = NEW.student_id;

            IF v_project_id IS NULL OR v_student_class_id IS NULL THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid student or project group';
            END IF;

            IF v_allow_cross_class = 0 AND v_project_class_id <> v_student_class_id THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Student is not in the project class';
            END IF;

            SET NEW.project_id = v_project_id;
            SET NEW.course_id = v_course_id;
         END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 資料表結構 `group_requests`
--

CREATE TABLE `group_requests` (
  `id` int(11) NOT NULL,
  `group_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_id` int(11) NOT NULL,
  `submitted_by` int(11) NOT NULL,
  `status` enum('Pending','Accepted','Rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `review_note` text COLLATE utf8mb4_unicode_ci,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` datetime DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `group_requests`
--

INSERT INTO `group_requests` (`id`, `group_name`, `project_id`, `submitted_by`, `status`, `review_note`, `submitted_at`, `reviewed_at`, `reviewed_by`) VALUES
(1, 'Team Alpha', 3, 262, 'Pending', NULL, DATE_SUB(NOW(), INTERVAL 1 DAY), NULL, NULL),
(2, 'Team Beta', 3, 266, 'Pending', NULL, DATE_SUB(NOW(), INTERVAL 2 DAY), NULL, NULL),
(3, 'Team Gamma', 3, 270, 'Pending', NULL, DATE_SUB(NOW(), INTERVAL 1 DAY), NULL, NULL),
(4, 'Team Delta', 8, 318, 'Pending', NULL, DATE_SUB(NOW(), INTERVAL 3 DAY), NULL, NULL),
(5, 'Team Epsilon', 8, 322, 'Pending', NULL, DATE_SUB(NOW(), INTERVAL 4 DAY), NULL, NULL),
(6, 'Team Zeta', 9, 274, 'Pending', NULL, DATE_SUB(NOW(), INTERVAL 1 DAY), NULL, NULL),
(7, 'Team Eta', 9, 276, 'Pending', NULL, DATE_SUB(NOW(), INTERVAL 2 DAY), NULL, NULL),
(8, 'Team 03', 2, 246, 'Accepted', NULL, DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 8 DAY), 1),
(9, 'Team 99', 2, 250, 'Rejected', 'Already in another group', DATE_SUB(NOW(), INTERVAL 9 DAY), DATE_SUB(NOW(), INTERVAL 8 DAY), 1);

-- --------------------------------------------------------

--
-- 資料表結構 `invitations`
--

CREATE TABLE `invitations` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `inviter_id` int(11) NOT NULL,
  `invitee_id` int(11) NOT NULL,
  `status` enum('Pending','Accepted','Rejected','Cancelled','Leave') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `responded_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `leader_votes`
--

CREATE TABLE `leader_votes` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `voter_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `sender_user_id` int(11) DEFAULT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `attachment_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_pinned` tinyint(1) NOT NULL DEFAULT '0',
  `pinned_at` datetime DEFAULT NULL,
  `pinned_by_user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `messages`
--

INSERT INTO `messages` (`id`, `group_id`, `sender_id`, `sender_user_id`, `recipient_id`, `content`, `attachment_id`, `created_at`, `is_pinned`, `pinned_at`, `pinned_by_user_id`) VALUES
(43, 5, NULL, 1, NULL, 'hello', NULL, '2026-10-31 17:44:11', 0, NULL, NULL),
(44, 5, 1, NULL, NULL, 'hello', NULL, '2026-11-03 09:20:47', 1, '2026-11-03 17:20:50', 6);

-- --------------------------------------------------------

--
-- 資料表結構 `message_reads`
--

CREATE TABLE `message_reads` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `last_read_message_id` int(11) DEFAULT '0',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `message_reads`
--

INSERT INTO `message_reads` (`id`, `user_id`, `group_id`, `last_read_message_id`, `updated_at`) VALUES
(112, 6, 5, 44, '2026-11-03 09:20:47'),
(113, 1, 5, 43, '2026-10-31 17:44:11'),
(168, 7, 5, 44, '2026-11-09 16:10:06'),
(169, 8, 5, 43, '2026-11-01 07:13:17');

-- --------------------------------------------------------

--
-- 資料表結構 `milestones`
--

CREATE TABLE `milestones` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Pending','In Progress','Completed') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `progress` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `milestones`
--

INSERT INTO `milestones` (`id`, `project_id`, `name`, `start_date`, `end_date`, `status`, `progress`) VALUES
(1, 1, 'Proposal and planning', '2026-09-01', '2027-01-20', 'Completed', 100),
(2, 1, 'Prototype checkpoint', '2026-09-01', '2027-01-20', 'In Progress', 45),
(3, 1, 'Final submission', '2026-09-01', '2027-01-20', 'Pending', 0),
(4, 2, 'Proposal and planning', '2026-09-01', '2027-01-20', 'Completed', 100),
(5, 2, 'Prototype checkpoint', '2026-09-01', '2027-01-20', 'Pending', 0),
(6, 2, 'Final submission', '2026-09-01', '2027-01-20', 'Pending', 0),
(7, 3, 'Proposal and planning', '2026-09-01', '2027-01-20', 'Completed', 100),
(8, 3, 'Prototype checkpoint', '2026-09-01', '2027-01-20', 'Pending', 0),
(9, 3, 'Final submission', '2026-09-01', '2027-01-20', 'Pending', 0),
(10, 4, 'Proposal and planning', '2026-09-01', '2027-01-20', 'Completed', 100),
(11, 4, 'Prototype checkpoint', '2026-09-01', '2027-01-20', 'Completed', 100),
(12, 4, 'Final submission', '2026-09-01', '2027-01-20', 'Completed', 100),
(13, 5, 'Proposal and planning', '2026-09-01', '2027-01-20', 'Completed', 100),
(14, 5, 'Prototype checkpoint', '2026-09-01', '2027-01-20', 'Pending', 0),
(15, 5, 'Final submission', '2026-09-01', '2027-01-20', 'Pending', 0),
(16, 6, 'Proposal and planning', '2026-09-01', '2027-01-20', 'Completed', 100),
(17, 6, 'Prototype checkpoint', '2026-09-01', '2027-01-20', 'In Progress', 68),
(18, 6, 'Final submission', '2026-09-01', '2027-01-20', 'Pending', 0),
(19, 7, 'Proposal and planning', '2026-09-01', '2027-01-20', 'Completed', 100),
(20, 7, 'Prototype checkpoint', '2026-09-01', '2027-01-20', 'Completed', 100),
(21, 7, 'Final submission', '2026-09-01', '2027-01-20', 'Completed', 100),
(22, 8, 'Proposal and planning', '2026-09-01', '2027-01-20', 'Completed', 100),
(23, 8, 'Prototype checkpoint', '2026-09-01', '2027-01-20', 'Pending', 0),
(24, 8, 'Final submission', '2026-09-01', '2027-01-20', 'Pending', 0),
(25, 9, 'Proposal and planning', '2026-09-01', '2027-01-20', 'Completed', 100),
(26, 9, 'Prototype checkpoint', '2026-09-01', '2027-01-20', 'Pending', 0),
(27, 9, 'Final submission', '2026-09-01', '2027-01-20', 'Pending', 0),
(28, 10, 'Proposal and planning', '2026-09-01', '2027-01-20', 'Completed', 100),
(29, 10, 'Prototype checkpoint', '2026-09-01', '2027-01-20', 'In Progress', 0),
(30, 10, 'Final submission', '2026-09-01', '2027-01-20', 'Pending', 0);

-- --------------------------------------------------------

--
-- 資料表結構 `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('task','file','team','project','system') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
(120, 7, 'task', 'New Task Assigned', 'You have been assigned \"Abc\".', 'student_task.php?group_id=5', 1, '2026-10-31 10:58:50'),
(121, 7, 'task', 'New Task Assigned', 'You have been assigned \"ftgyh\".', 'student_task.php?group_id=5', 1, '2026-10-31 11:38:48'),
(122, 6, 'task', 'New Task Assigned', 'You have been assigned \"ABC\".', 'student_task.php?group_id=5', 1, '2026-10-31 17:50:04'),
(123, 6, 'task', 'New Task Assigned', 'You have been assigned \"nhjkl\".', 'student_task.php?group_id=5', 1, '2026-11-03 09:04:33'),
(124, 6, 'task', 'New Task Assigned', 'You have been assigned \"dfgh\".', 'student_task.php?group_id=5', 1, '2026-11-03 09:06:19'),
(125, 6, 'task', 'New Task Assigned', 'You have been assigned \"fgh\".', 'student_task.php?group_id=5', 1, '2026-11-09 16:10:29');

-- --------------------------------------------------------

--
-- 資料表結構 `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `topic_mode` enum('Fixed','Multi','Self') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Fixed',
  `assessment_scope` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assessment_weight` tinyint(3) UNSIGNED DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `supervisor_id` int(11) NOT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `status` enum('Recruiting','Waiting_Send','Voting','Executing','Completed','Archived') COLLATE utf8mb4_unicode_ci DEFAULT 'Recruiting',
  `progress` int(11) DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `requirements` text COLLATE utf8mb4_unicode_ci,
  `team_mode` enum('Free','Random','Teacher_Assigned') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Free',
  `team_deadline` date DEFAULT NULL,
  `allow_cross_class` tinyint(1) NOT NULL DEFAULT '0',
  `default_max_members` int(11) NOT NULL DEFAULT '4',
  `leader_permissions` text COLLATE utf8mb4_unicode_ci,
  `post_deadline_action` enum('Lock','Notify') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Lock',
  `visibility` enum('All','Course') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Course',
  `reminder_days` int(11) NOT NULL DEFAULT '3',
  `allow_extension` tinyint(1) NOT NULL DEFAULT '1',
  `is_draft` tinyint(1) NOT NULL DEFAULT '0',
  `last_reminder_sent` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `projects`
--

INSERT INTO `projects` (`id`, `name`, `category`, `topic_mode`, `assessment_scope`, `assessment_weight`, `course_id`, `class_id`, `supervisor_id`, `period_start`, `period_end`, `status`, `progress`, `description`, `requirements`, `team_mode`, `team_deadline`, `allow_cross_class`, `default_max_members`, `leader_permissions`, `post_deadline_action`, `visibility`, `reminder_days`, `allow_extension`, `is_draft`, `last_reminder_sent`, `created_at`) VALUES
(1, 'ITE4116M_SE-1A_Final Year Project', 'Final Year Project', 'Self', NULL, NULL, 1, 1, 1, '2026-09-01', '2027-01-31', 'Executing', 45, 'Final Year Project workspace for students to propose and develop their own topic.', 'Students work in teams of four and submit progress through TeamBox.', 'Free', '2026-10-18', 0, 4, '["invite","remove","rename","assign","submit"]', 'Lock', 'Course', 3, 1, 0, NULL, '2026-09-01 09:00:00'),
(2, 'ITE4116M_SE-2B_Final Year Project', 'Final Year Project', 'Self', NULL, NULL, 1, 4, 1, '2026-09-01', '2027-01-31', 'Voting', 18, 'Final Year Project workspace for students to propose and develop their own topic.', 'Students work in teams of four and submit progress through TeamBox.', 'Free', DATE_ADD(CURDATE(), INTERVAL 7 DAY), 0, 4, '["invite","remove","rename","assign","submit"]', 'Lock', 'Course', 3, 1, 0, NULL, '2026-09-01 09:00:00'),
(3, 'ITE4116M_CY-1A_Final Year Project', 'Final Year Project', 'Self', NULL, NULL, 1, 5, 1, '2026-09-01', '2027-01-31', 'Recruiting', 0, 'Final Year Project workspace for students to propose and develop their own topic.', 'Students work in teams of four and submit progress through TeamBox.', 'Free', DATE_ADD(CURDATE(), INTERVAL 7 DAY), 0, 4, '["invite","remove","rename","assign","submit"]', 'Lock', 'Course', 3, 1, 0, NULL, '2026-09-01 09:00:00'),
(4, 'ITE4116M_SE-1B_Final Year Project', 'Final Year Project', 'Self', NULL, NULL, 1, 2, 2, '2026-09-01', '2026-11-05', 'Completed', 100, 'Final Year Project workspace for students to propose and develop their own topic.', 'Students work in teams of four and submit progress through TeamBox.', 'Free', '2026-10-05', 0, 4, '["invite","remove","rename","assign","submit"]', 'Lock', 'Course', 3, 1, 0, NULL, '2026-09-01 09:00:00'),
(5, 'ITE4116M_CY-1B_Final Year Project', 'Final Year Project', 'Self', NULL, NULL, 1, 6, 2, '2026-09-01', '2027-01-31', 'Waiting_Send', 12, 'Final Year Project workspace for students to propose and develop their own topic.', 'Students work in teams of four and submit progress through TeamBox.', 'Free', DATE_ADD(CURDATE(), INTERVAL 2 DAY), 0, 4, '["invite","remove","rename","assign","submit"]', 'Lock', 'Course', 3, 1, 0, NULL, '2026-09-01 09:00:00'),
(6, 'ITE4116M_SE-2A_Final Year Project', 'Final Year Project', 'Self', NULL, NULL, 1, 3, 3, '2026-09-01', '2027-01-31', 'Executing', 68, 'Final Year Project workspace for students to propose and develop their own topic.', 'Students work in teams of four and submit progress through TeamBox.', 'Free', '2026-10-20', 0, 4, '["invite","remove","rename","assign","submit"]', 'Lock', 'Course', 3, 1, 0, NULL, '2026-09-01 09:00:00'),
(7, 'ITE4116M_CY-2A_Final Year Project', 'Final Year Project', 'Self', NULL, NULL, 1, 7, 4, '2026-09-01', '2026-10-31', 'Archived', 100, 'Final Year Project workspace for students to propose and develop their own topic.', 'Students work in teams of four and submit progress through TeamBox.', 'Free', '2026-10-01', 0, 4, '["invite","remove","rename","assign","submit"]', 'Lock', 'Course', 3, 1, 0, NULL, '2026-09-01 09:00:00'),
(8, 'ITE4116M_CY-2B_Final Year Project', 'Final Year Project', 'Self', NULL, NULL, 1, 8, 5, '2026-09-01', '2027-01-31', 'Recruiting', 0, 'Final Year Project workspace for students to propose and develop their own topic.', 'Students work in teams of four and submit progress through TeamBox.', 'Free', DATE_ADD(CURDATE(), INTERVAL 5 DAY), 0, 4, '["invite","remove","rename","assign","submit"]', 'Lock', 'Course', 3, 1, 0, NULL, '2026-09-01 09:00:00'),
(9, 'ITE4116M_CY-1A_Assignment', 'Assignment', 'Multi', 'EA', 3, 1, 5, 1, '2026-09-01', '2027-01-31', 'Recruiting', 0, 'Final Year Project workspace for students to propose and develop their own topic.', 'Students work in teams of four and submit progress through TeamBox.', 'Free', DATE_ADD(CURDATE(), INTERVAL 10 DAY), 0, 4, '["invite","remove","rename","assign","submit"]', 'Lock', 'Course', 3, 1, 0, NULL, '2026-09-01 09:00:00'),
(10, 'ITE4116M_SE-1A_Final Year Project', 'Final Year Project', 'Self', NULL, NULL, 1, 1, 1, '2026-09-01', '2027-01-31', 'Executing', 0, 'Final Year Project workspace for students to propose and develop their own topic.', 'Students work in teams of four and submit progress through TeamBox.', 'Free', DATE_ADD(CURDATE(), INTERVAL 7 DAY), 0, 4, '["invite","remove","rename","assign","submit"]', 'Lock', 'Course', 3, 1, 1, NULL, '2026-09-01 09:00:00');

-- --------------------------------------------------------

--
-- 資料表結構 `project_attachments`
--

CREATE TABLE `project_attachments` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stored_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `project_cross_classes`
--

CREATE TABLE `project_cross_classes` (
  `project_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `project_extension_requests`
--

CREATE TABLE `project_extension_requests` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `requested_end` date NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `status` enum('Pending','Approved','Rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `project_topics`
--

CREATE TABLE `project_topics` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `position` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `project_topics`
--

INSERT INTO `project_topics` (`id`, `project_id`, `title`, `description`, `position`, `is_active`, `created_at`) VALUES
(1, 9, 'gg', 'ggg', 0, 1, '2026-10-31 17:20:03');

-- --------------------------------------------------------

--
-- 資料表結構 `request_members`
--

CREATE TABLE `request_members` (
  `request_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `is_leader` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `request_members` (`request_id`, `student_id`, `is_leader`) VALUES
(1, 262, 1),
(1, 263, 0),
(1, 264, 0),
(1, 265, 0),
(2, 266, 1),
(2, 267, 0),
(2, 268, 0),
(2, 269, 0),
(3, 270, 1),
(3, 271, 0),
(3, 272, 0),
(3, 273, 0),
(4, 318, 1),
(4, 319, 0),
(4, 320, 0),
(4, 321, 0),
(5, 322, 1),
(5, 323, 0),
(5, 324, 0),
(5, 325, 0),
(6, 274, 1),
(6, 275, 0),
(6, 258, 0),
(6, 259, 0),
(7, 276, 1),
(7, 277, 0),
(7, 260, 0),
(7, 261, 0),
(8, 246, 1),
(8, 247, 0),
(8, 248, 0),
(8, 249, 0),
(9, 250, 1),
(9, 251, 0),
(9, 252, 0),
(9, 253, 0);

-- --------------------------------------------------------

--
-- 資料表結構 `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `student_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `major` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grade_level` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `students`
--

INSERT INTO `students` (`id`, `user_id`, `student_number`, `major`, `grade_level`, `class_id`) VALUES
(1, 6, 'S48291537', 'Software Engineering', 13, 4),
(2, 7, 'S15942736', 'Software Engineering', 13, 4),
(3, 8, 'S73658120', 'Software Engineering', 13, 4),
(4, 9, 'S20473819', 'Software Engineering', 13, 4),
(182, 187, 'S27000000', 'Software Engineering', 12, 1),
(183, 188, 'S27000001', 'Software Engineering', 12, 1),
(184, 189, 'S27000002', 'Software Engineering', 12, 1),
(185, 190, 'S27000003', 'Software Engineering', 12, 1),
(186, 191, 'S27000004', 'Software Engineering', 12, 1),
(187, 192, 'S27000005', 'Software Engineering', 12, 1),
(188, 193, 'S27000006', 'Software Engineering', 12, 1),
(189, 194, 'S27000007', 'Software Engineering', 12, 1),
(190, 195, 'S27000008', 'Software Engineering', 12, 1),
(191, 196, 'S27000009', 'Software Engineering', 12, 1),
(192, 197, 'S27000010', 'Software Engineering', 12, 1),
(193, 198, 'S27000011', 'Software Engineering', 12, 1),
(194, 199, 'S27000012', 'Software Engineering', 12, 1),
(195, 200, 'S27000013', 'Software Engineering', 12, 1),
(196, 201, 'S27000014', 'Software Engineering', 12, 1),
(197, 202, 'S27000015', 'Software Engineering', 12, 1),
(198, 203, 'S27000016', 'Software Engineering', 12, 1),
(199, 204, 'S27000017', 'Software Engineering', 12, 1),
(200, 205, 'S27000018', 'Software Engineering', 12, 1),
(201, 206, 'S27000019', 'Software Engineering', 12, 1),
(202, 207, 'S27000020', 'Software Engineering', 12, 2),
(203, 208, 'S27000021', 'Software Engineering', 12, 2),
(204, 209, 'S27000022', 'Software Engineering', 12, 2),
(205, 210, 'S27000023', 'Software Engineering', 12, 2),
(206, 211, 'S27000024', 'Software Engineering', 12, 2),
(207, 212, 'S27000025', 'Software Engineering', 12, 2),
(208, 213, 'S27000026', 'Software Engineering', 12, 2),
(209, 214, 'S27000027', 'Software Engineering', 12, 2),
(210, 215, 'S27000028', 'Software Engineering', 12, 2),
(211, 216, 'S27000029', 'Software Engineering', 12, 2),
(212, 217, 'S27000030', 'Software Engineering', 12, 2),
(213, 218, 'S27000031', 'Software Engineering', 12, 2),
(214, 219, 'S27000032', 'Software Engineering', 12, 2),
(215, 220, 'S27000033', 'Software Engineering', 12, 2),
(216, 221, 'S27000034', 'Software Engineering', 12, 2),
(217, 222, 'S27000035', 'Software Engineering', 12, 2),
(218, 223, 'S27000036', 'Software Engineering', 12, 2),
(219, 224, 'S27000037', 'Software Engineering', 12, 2),
(220, 225, 'S27000038', 'Software Engineering', 12, 2),
(221, 226, 'S27000039', 'Software Engineering', 12, 2),
(222, 227, 'S27000040', 'Software Engineering', 13, 3),
(223, 228, 'S27000041', 'Software Engineering', 13, 3),
(224, 229, 'S27000042', 'Software Engineering', 13, 3),
(225, 230, 'S27000043', 'Software Engineering', 13, 3),
(226, 231, 'S27000044', 'Software Engineering', 13, 3),
(227, 232, 'S27000045', 'Software Engineering', 13, 3),
(228, 233, 'S27000046', 'Software Engineering', 13, 3),
(229, 234, 'S27000047', 'Software Engineering', 13, 3),
(230, 235, 'S27000048', 'Software Engineering', 13, 3),
(231, 236, 'S27000049', 'Software Engineering', 13, 3),
(232, 237, 'S27000050', 'Software Engineering', 13, 3),
(233, 238, 'S27000051', 'Software Engineering', 13, 3),
(234, 239, 'S27000052', 'Software Engineering', 13, 3),
(235, 240, 'S27000053', 'Software Engineering', 13, 3),
(236, 241, 'S27000054', 'Software Engineering', 13, 3),
(237, 242, 'S27000055', 'Software Engineering', 13, 3),
(238, 243, 'S27000056', 'Software Engineering', 13, 3),
(239, 244, 'S27000057', 'Software Engineering', 13, 3),
(240, 245, 'S27000058', 'Software Engineering', 13, 3),
(241, 246, 'S27000059', 'Software Engineering', 13, 3),
(242, 247, 'S27000060', 'Software Engineering', 13, 4),
(243, 248, 'S27000061', 'Software Engineering', 13, 4),
(244, 249, 'S27000062', 'Software Engineering', 13, 4),
(245, 250, 'S27000063', 'Software Engineering', 13, 4),
(246, 251, 'S27000064', 'Software Engineering', 13, 4),
(247, 252, 'S27000065', 'Software Engineering', 13, 4),
(248, 253, 'S27000066', 'Software Engineering', 13, 4),
(249, 254, 'S27000067', 'Software Engineering', 13, 4),
(250, 255, 'S27000068', 'Software Engineering', 13, 4),
(251, 256, 'S27000069', 'Software Engineering', 13, 4),
(252, 257, 'S27000070', 'Software Engineering', 13, 4),
(253, 258, 'S27000071', 'Software Engineering', 13, 4),
(254, 259, 'S27000072', 'Software Engineering', 13, 4),
(255, 260, 'S27000073', 'Software Engineering', 13, 4),
(256, 261, 'S27000074', 'Software Engineering', 13, 4),
(257, 262, 'S27000075', 'Software Engineering', 13, 4),
(258, 263, 'S27000076', 'Cybersecurity', 12, 5),
(259, 264, 'S27000077', 'Cybersecurity', 12, 5),
(260, 265, 'S27000078', 'Cybersecurity', 12, 5),
(261, 266, 'S27000079', 'Cybersecurity', 12, 5),
(262, 267, 'S27000080', 'Cybersecurity', 12, 5),
(263, 268, 'S27000081', 'Cybersecurity', 12, 5),
(264, 269, 'S27000082', 'Cybersecurity', 12, 5),
(265, 270, 'S27000083', 'Cybersecurity', 12, 5),
(266, 271, 'S27000084', 'Cybersecurity', 12, 5),
(267, 272, 'S27000085', 'Cybersecurity', 12, 5),
(268, 273, 'S27000086', 'Cybersecurity', 12, 5),
(269, 274, 'S27000087', 'Cybersecurity', 12, 5),
(270, 275, 'S27000088', 'Cybersecurity', 12, 5),
(271, 276, 'S27000089', 'Cybersecurity', 12, 5),
(272, 277, 'S27000090', 'Cybersecurity', 12, 5),
(273, 278, 'S27000091', 'Cybersecurity', 12, 5),
(274, 279, 'S27000092', 'Cybersecurity', 12, 5),
(275, 280, 'S27000093', 'Cybersecurity', 12, 5),
(276, 281, 'S27000094', 'Cybersecurity', 12, 5),
(277, 282, 'S27000095', 'Cybersecurity', 12, 5),
(278, 283, 'S27000096', 'Cybersecurity', 12, 6),
(279, 284, 'S27000097', 'Cybersecurity', 12, 6),
(280, 285, 'S27000098', 'Cybersecurity', 12, 6),
(281, 286, 'S27000099', 'Cybersecurity', 12, 6),
(282, 287, 'S27000100', 'Cybersecurity', 12, 6),
(283, 288, 'S27000101', 'Cybersecurity', 12, 6),
(284, 289, 'S27000102', 'Cybersecurity', 12, 6),
(285, 290, 'S27000103', 'Cybersecurity', 12, 6),
(286, 291, 'S27000104', 'Cybersecurity', 12, 6),
(287, 292, 'S27000105', 'Cybersecurity', 12, 6),
(288, 293, 'S27000106', 'Cybersecurity', 12, 6),
(289, 294, 'S27000107', 'Cybersecurity', 12, 6),
(290, 295, 'S27000108', 'Cybersecurity', 12, 6),
(291, 296, 'S27000109', 'Cybersecurity', 12, 6),
(292, 297, 'S27000110', 'Cybersecurity', 12, 6),
(293, 298, 'S27000111', 'Cybersecurity', 12, 6),
(294, 299, 'S27000112', 'Cybersecurity', 12, 6),
(295, 300, 'S27000113', 'Cybersecurity', 12, 6),
(296, 301, 'S27000114', 'Cybersecurity', 12, 6),
(297, 302, 'S27000115', 'Cybersecurity', 12, 6),
(298, 303, 'S27000116', 'Cybersecurity', 13, 7),
(299, 304, 'S27000117', 'Cybersecurity', 13, 7),
(300, 305, 'S27000118', 'Cybersecurity', 13, 7),
(301, 306, 'S27000119', 'Cybersecurity', 13, 7),
(302, 307, 'S27000120', 'Cybersecurity', 13, 7),
(303, 308, 'S27000121', 'Cybersecurity', 13, 7),
(304, 309, 'S27000122', 'Cybersecurity', 13, 7),
(305, 310, 'S27000123', 'Cybersecurity', 13, 7),
(306, 311, 'S27000124', 'Cybersecurity', 13, 7),
(307, 312, 'S27000125', 'Cybersecurity', 13, 7),
(308, 313, 'S27000126', 'Cybersecurity', 13, 7),
(309, 314, 'S27000127', 'Cybersecurity', 13, 7),
(310, 315, 'S27000128', 'Cybersecurity', 13, 7),
(311, 316, 'S27000129', 'Cybersecurity', 13, 7),
(312, 317, 'S27000130', 'Cybersecurity', 13, 7),
(313, 318, 'S27000131', 'Cybersecurity', 13, 7),
(314, 319, 'S27000132', 'Cybersecurity', 13, 7),
(315, 320, 'S27000133', 'Cybersecurity', 13, 7),
(316, 321, 'S27000134', 'Cybersecurity', 13, 7),
(317, 322, 'S27000135', 'Cybersecurity', 13, 7),
(318, 323, 'S27000136', 'Cybersecurity', 13, 8),
(319, 324, 'S27000137', 'Cybersecurity', 13, 8),
(320, 325, 'S27000138', 'Cybersecurity', 13, 8),
(321, 326, 'S27000139', 'Cybersecurity', 13, 8),
(322, 327, 'S27000140', 'Cybersecurity', 13, 8),
(323, 328, 'S27000141', 'Cybersecurity', 13, 8),
(324, 329, 'S27000142', 'Cybersecurity', 13, 8),
(325, 330, 'S27000143', 'Cybersecurity', 13, 8),
(326, 331, 'S27000144', 'Cybersecurity', 13, 8),
(327, 332, 'S27000145', 'Cybersecurity', 13, 8),
(328, 333, 'S27000146', 'Cybersecurity', 13, 8),
(329, 334, 'S27000147', 'Cybersecurity', 13, 8),
(330, 335, 'S27000148', 'Cybersecurity', 13, 8),
(331, 336, 'S27000149', 'Cybersecurity', 13, 8),
(332, 337, 'S27000150', 'Cybersecurity', 13, 8),
(333, 338, 'S27000151', 'Cybersecurity', 13, 8),
(334, 339, 'S27000152', 'Cybersecurity', 13, 8),
(335, 340, 'S27000153', 'Cybersecurity', 13, 8),
(336, 341, 'S27000154', 'Cybersecurity', 13, 8),
(337, 342, 'S27000155', 'Cybersecurity', 13, 8);

-- --------------------------------------------------------

--
-- 資料表結構 `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `assigned_to` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `status` enum('Pending','In Progress','Waiting_Confirm','Completed') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `priority` enum('Low','Medium','High') COLLATE utf8mb4_unicode_ci DEFAULT 'Medium',
  `due_date` date DEFAULT NULL,
  `due_time` time DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `tasks`
--

INSERT INTO `tasks` (`id`, `project_id`, `group_id`, `title`, `description`, `assigned_to`, `created_by`, `status`, `priority`, `due_date`, `completed_at`, `created_at`) VALUES
(1, 1, 1, 'Prepare project proposal', 'Task description placeholder', 182, 187, 'Completed', 'Medium', DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(2, 1, 1, 'Complete prototype checkpoint', 'Task description placeholder', 183, 187, 'In Progress', 'High', CURDATE(), NULL, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(3, 1, 1, 'Submit weekly progress report', 'Task description placeholder', 184, 187, 'Pending', 'Medium', DATE_ADD(CURDATE(), INTERVAL 2 DAY), NULL, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(4, 1, 1, 'Upload final demo slides', 'Task description placeholder', 185, 187, 'Waiting_Confirm', 'Low', DATE_ADD(CURDATE(), INTERVAL 5 DAY), NULL, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(5, 1, 2, 'Prepare project proposal', 'Task description placeholder', 186, 191, 'Completed', 'Medium', DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(6, 1, 2, 'Design database schema', 'Task description placeholder', 187, 191, 'In Progress', 'High', DATE_SUB(CURDATE(), INTERVAL 3 DAY), NULL, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(7, 1, 2, 'Implement login flow', 'Task description placeholder', 188, 191, 'Pending', 'Medium', DATE_ADD(CURDATE(), INTERVAL 3 DAY), NULL, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(8, 1, 3, 'Prepare project proposal', 'Task description placeholder', 190, 195, 'Completed', 'Medium', DATE_SUB(CURDATE(), INTERVAL 28 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(9, 1, 3, 'Choose team leader', 'Task description placeholder', 191, 195, 'Pending', 'High', DATE_ADD(CURDATE(), INTERVAL 1 DAY), NULL, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(10, 1, 4, 'Submit project topic', 'Task description placeholder', 194, 199, 'Pending', 'High', CURDATE(), NULL, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(11, 2, 5, 'Propose project topic', 'Task description placeholder', 242, 247, 'Pending', 'Medium', DATE_ADD(CURDATE(), INTERVAL 2 DAY), NULL, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(12, 2, 5, 'Elect team leader', 'Task description placeholder', 243, 247, 'Pending', 'High', DATE_ADD(CURDATE(), INTERVAL 1 DAY), NULL, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(13, 2, 6, 'Propose project topic', 'Task description placeholder', 246, 251, 'Pending', 'Medium', DATE_ADD(CURDATE(), INTERVAL 2 DAY), NULL, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(14, 2, 7, 'Submit topic proposal', 'Task description placeholder', 250, 255, 'Pending', 'Medium', DATE_ADD(CURDATE(), INTERVAL 3 DAY), NULL, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(15, 4, 8, 'Prepare project proposal', 'Task description placeholder', 202, 207, 'Completed', 'Medium', DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(16, 4, 8, 'Build prototype', 'Task description placeholder', 203, 207, 'Completed', 'High', DATE_SUB(CURDATE(), INTERVAL 40 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(17, 4, 8, 'Final submission', 'Task description placeholder', 204, 207, 'Completed', 'High', DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(18, 4, 9, 'Prepare project proposal', 'Task description placeholder', 206, 211, 'Completed', 'Medium', DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(19, 4, 9, 'Final submission', 'Task description placeholder', 207, 211, 'Completed', 'High', DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(20, 4, 10, 'Prepare project proposal', 'Task description placeholder', 210, 215, 'Completed', 'Medium', DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(21, 4, 10, 'Final submission', 'Task description placeholder', 211, 215, 'Completed', 'High', DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(22, 5, 11, 'Prepare project proposal', 'Task description placeholder', 278, 283, 'Completed', 'Medium', DATE_SUB(CURDATE(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(23, 5, 11, 'Submit group formation', 'Task description placeholder', 279, 283, 'In Progress', 'High', CURDATE(), NULL, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(24, 5, 12, 'Prepare project proposal', 'Task description placeholder', 282, 287, 'Completed', 'Medium', DATE_SUB(CURDATE(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(25, 5, 12, 'Submit group formation', 'Task description placeholder', 283, 287, 'In Progress', 'High', DATE_ADD(CURDATE(), INTERVAL 1 DAY), NULL, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(26, 5, 13, 'Submit group formation', 'Task description placeholder', 286, 291, 'Pending', 'High', CURDATE(), NULL, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(27, 6, 14, 'Prepare project proposal', 'Task description placeholder', 222, 227, 'Completed', 'Medium', DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(28, 6, 14, 'Complete prototype checkpoint', 'Task description placeholder', 223, 227, 'In Progress', 'High', CURDATE(), NULL, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(29, 6, 14, 'Prepare interim presentation', 'Task description placeholder', 224, 227, 'Pending', 'Medium', DATE_ADD(CURDATE(), INTERVAL 4 DAY), NULL, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(30, 6, 15, 'Prepare project proposal', 'Task description placeholder', 226, 231, 'Completed', 'Medium', DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(31, 6, 15, 'Fix prototype bugs', 'Task description placeholder', 227, 231, 'In Progress', 'High', DATE_SUB(CURDATE(), INTERVAL 2 DAY), NULL, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(32, 6, 16, 'Choose team leader', 'Task description placeholder', 230, 235, 'Pending', 'High', DATE_ADD(CURDATE(), INTERVAL 1 DAY), NULL, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(33, 7, 17, 'Prepare project proposal', 'Task description placeholder', 298, 303, 'Completed', 'Medium', DATE_SUB(CURDATE(), INTERVAL 90 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(34, 7, 17, 'Final submission', 'Task description placeholder', 299, 303, 'Completed', 'High', DATE_SUB(CURDATE(), INTERVAL 50 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(35, 7, 18, 'Final submission', 'Task description placeholder', 302, 307, 'Completed', 'High', DATE_SUB(CURDATE(), INTERVAL 50 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(36, 7, 19, 'Final submission', 'Task description placeholder', 306, 311, 'Completed', 'High', DATE_SUB(CURDATE(), INTERVAL 50 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY));

-- --------------------------------------------------------

--
-- 資料表結構 `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `teacher_code` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hire_date` date DEFAULT NULL,
  `department` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `teachers`
--

INSERT INTO `teachers` (`id`, `user_id`, `teacher_code`, `hire_date`, `department`, `title`) VALUES
(1, 1, 'T48291', '2022-09-01', 'Information Technology', 'Senior Lecturer'),
(2, 2, 'T15942', '2020-09-01', 'Information Technology', 'Lecturer'),
(3, 3, 'T73658', '2017-09-01', 'Computer Science', 'Associate Professor'),
(4, 4, 'T20473', '2021-09-01', 'Information Technology', 'Lecturer'),
(5, 5, 'T91826', '2018-09-01', 'Software Engineering', 'Senior Lecturer');

-- --------------------------------------------------------

--
-- 資料表結構 `teacher_student`
--

CREATE TABLE `teacher_student` (
  `teacher_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `teacher_student`
--

INSERT INTO `teacher_student` (`teacher_id`, `student_id`, `assigned_at`) VALUES
(1, 1, '2026-10-20 13:29:02'),
(1, 2, '2026-10-20 13:29:02'),
(1, 3, '2026-10-20 13:29:02'),
(1, 4, '2026-10-20 13:29:02'),
(1, 182, '2026-10-20 13:29:02'),
(1, 183, '2026-10-20 13:29:02'),
(1, 184, '2026-10-20 13:29:02'),
(1, 185, '2026-10-20 13:29:02'),
(1, 186, '2026-10-20 13:29:02'),
(1, 187, '2026-10-20 13:29:02'),
(1, 188, '2026-10-20 13:29:02'),
(1, 189, '2026-10-20 13:29:02'),
(1, 190, '2026-10-20 13:29:02'),
(1, 191, '2026-10-20 13:29:02'),
(1, 192, '2026-10-20 13:29:02'),
(1, 193, '2026-10-20 13:29:02'),
(1, 194, '2026-10-20 13:29:02'),
(1, 195, '2026-10-20 13:29:02'),
(1, 196, '2026-10-20 13:29:02'),
(1, 197, '2026-10-20 13:29:02'),
(1, 198, '2026-10-20 13:29:02'),
(1, 199, '2026-10-20 13:29:02'),
(1, 200, '2026-10-20 13:29:02'),
(1, 201, '2026-10-20 13:29:02'),
(1, 242, '2026-10-20 13:29:02'),
(1, 243, '2026-10-20 13:29:02'),
(1, 244, '2026-10-20 13:29:02'),
(1, 245, '2026-10-20 13:29:02'),
(1, 246, '2026-10-20 13:29:02'),
(1, 247, '2026-10-20 13:29:02'),
(1, 248, '2026-10-20 13:29:02'),
(1, 249, '2026-10-20 13:29:02'),
(1, 250, '2026-10-20 13:29:02'),
(1, 251, '2026-10-20 13:29:02'),
(1, 252, '2026-10-20 13:29:02'),
(1, 253, '2026-10-20 13:29:02'),
(1, 254, '2026-10-20 13:29:02'),
(1, 255, '2026-10-20 13:29:02'),
(1, 256, '2026-10-20 13:29:02'),
(1, 257, '2026-10-20 13:29:02'),
(1, 258, '2026-10-20 13:29:02'),
(1, 259, '2026-10-20 13:29:02'),
(1, 260, '2026-10-20 13:29:02'),
(1, 261, '2026-10-20 13:29:02'),
(1, 262, '2026-10-20 13:29:02'),
(1, 263, '2026-10-20 13:29:02'),
(1, 264, '2026-10-20 13:29:02'),
(1, 265, '2026-10-20 13:29:02'),
(1, 266, '2026-10-20 13:29:02'),
(1, 267, '2026-10-20 13:29:02'),
(1, 268, '2026-10-20 13:29:02'),
(1, 269, '2026-10-20 13:29:02'),
(1, 270, '2026-10-20 13:29:02'),
(1, 271, '2026-10-20 13:29:02'),
(1, 272, '2026-10-20 13:29:02'),
(1, 273, '2026-10-20 13:29:02'),
(1, 274, '2026-10-20 13:29:02'),
(1, 275, '2026-10-20 13:29:02'),
(1, 276, '2026-10-20 13:29:02'),
(1, 277, '2026-10-20 13:29:02'),
(2, 202, '2026-10-20 13:29:02'),
(2, 203, '2026-10-20 13:29:02'),
(2, 204, '2026-10-20 13:29:02'),
(2, 205, '2026-10-20 13:29:02'),
(2, 206, '2026-10-20 13:29:02'),
(2, 207, '2026-10-20 13:29:02'),
(2, 208, '2026-10-20 13:29:02'),
(2, 209, '2026-10-20 13:29:02'),
(2, 210, '2026-10-20 13:29:02'),
(2, 211, '2026-10-20 13:29:02'),
(2, 212, '2026-10-20 13:29:02'),
(2, 213, '2026-10-20 13:29:02'),
(2, 214, '2026-10-20 13:29:02'),
(2, 215, '2026-10-20 13:29:02'),
(2, 216, '2026-10-20 13:29:02'),
(2, 217, '2026-10-20 13:29:02'),
(2, 218, '2026-10-20 13:29:02'),
(2, 219, '2026-10-20 13:29:02'),
(2, 220, '2026-10-20 13:29:02'),
(2, 221, '2026-10-20 13:29:02'),
(2, 278, '2026-10-20 13:29:02'),
(2, 279, '2026-10-20 13:29:02'),
(2, 280, '2026-10-20 13:29:02'),
(2, 281, '2026-10-20 13:29:02'),
(2, 282, '2026-10-20 13:29:02'),
(2, 283, '2026-10-20 13:29:02'),
(2, 284, '2026-10-20 13:29:02'),
(2, 285, '2026-10-20 13:29:02'),
(2, 286, '2026-10-20 13:29:02'),
(2, 287, '2026-10-20 13:29:02'),
(2, 288, '2026-10-20 13:29:02'),
(2, 289, '2026-10-20 13:29:02'),
(2, 290, '2026-10-20 13:29:02'),
(2, 291, '2026-10-20 13:29:02'),
(2, 292, '2026-10-20 13:29:02'),
(2, 293, '2026-10-20 13:29:02'),
(2, 294, '2026-10-20 13:29:02'),
(2, 295, '2026-10-20 13:29:02'),
(2, 296, '2026-10-20 13:29:02'),
(2, 297, '2026-10-20 13:29:02'),
(3, 222, '2026-10-20 13:29:02'),
(3, 223, '2026-10-20 13:29:02'),
(3, 224, '2026-10-20 13:29:02'),
(3, 225, '2026-10-20 13:29:02'),
(3, 226, '2026-10-20 13:29:02'),
(3, 227, '2026-10-20 13:29:02'),
(3, 228, '2026-10-20 13:29:02'),
(3, 229, '2026-10-20 13:29:02'),
(3, 230, '2026-10-20 13:29:02'),
(3, 231, '2026-10-20 13:29:02'),
(3, 232, '2026-10-20 13:29:02'),
(3, 233, '2026-10-20 13:29:02'),
(3, 234, '2026-10-20 13:29:02'),
(3, 235, '2026-10-20 13:29:02'),
(3, 236, '2026-10-20 13:29:02'),
(3, 237, '2026-10-20 13:29:02'),
(3, 238, '2026-10-20 13:29:02'),
(3, 239, '2026-10-20 13:29:02'),
(3, 240, '2026-10-20 13:29:02'),
(3, 241, '2026-10-20 13:29:02'),
(4, 298, '2026-10-20 13:29:02'),
(4, 299, '2026-10-20 13:29:02'),
(4, 300, '2026-10-20 13:29:02'),
(4, 301, '2026-10-20 13:29:02'),
(4, 302, '2026-10-20 13:29:02'),
(4, 303, '2026-10-20 13:29:02'),
(4, 304, '2026-10-20 13:29:02'),
(4, 305, '2026-10-20 13:29:02'),
(4, 306, '2026-10-20 13:29:02'),
(4, 307, '2026-10-20 13:29:02'),
(4, 308, '2026-10-20 13:29:02'),
(4, 309, '2026-10-20 13:29:02'),
(4, 310, '2026-10-20 13:29:02'),
(4, 311, '2026-10-20 13:29:02'),
(4, 312, '2026-10-20 13:29:02'),
(4, 313, '2026-10-20 13:29:02'),
(4, 314, '2026-10-20 13:29:03'),
(4, 315, '2026-10-20 13:29:03'),
(4, 316, '2026-10-20 13:29:03'),
(4, 317, '2026-10-20 13:29:03'),
(5, 318, '2026-10-20 13:29:03'),
(5, 319, '2026-10-20 13:29:03'),
(5, 320, '2026-10-20 13:29:03'),
(5, 321, '2026-10-20 13:29:03'),
(5, 322, '2026-10-20 13:29:03'),
(5, 323, '2026-10-20 13:29:03'),
(5, 324, '2026-10-20 13:29:03'),
(5, 325, '2026-10-20 13:29:03'),
(5, 326, '2026-10-20 13:29:03'),
(5, 327, '2026-10-20 13:29:03'),
(5, 328, '2026-10-20 13:29:03'),
(5, 329, '2026-10-20 13:29:03'),
(5, 330, '2026-10-20 13:29:03'),
(5, 331, '2026-10-20 13:29:03'),
(5, 332, '2026-10-20 13:29:03'),
(5, 333, '2026-10-20 13:29:03'),
(5, 334, '2026-10-20 13:29:03'),
(5, 335, '2026-10-20 13:29:03'),
(5, 336, '2026-10-20 13:29:03'),
(5, 337, '2026-10-20 13:29:03');

-- --------------------------------------------------------

--
-- 資料表結構 `team_topic_proposals`
--

CREATE TABLE `team_topic_proposals` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `topic_id` int(11) DEFAULT NULL,
  `version` int(11) NOT NULL DEFAULT '1',
  `topic_title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `topic_description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('Pending','Approved','Rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `submitted_by` int(11) NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_note` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `team_topic_proposals`
--

INSERT INTO `team_topic_proposals` (`id`, `group_id`, `project_id`, `topic_id`, `version`, `topic_title`, `topic_description`, `status`, `submitted_by`, `submitted_at`, `reviewed_by`, `reviewed_at`, `review_note`) VALUES
(1, 1, 1, NULL, 1, 'TeamBox', 'Approved Final Year Project topic selected for this team.', 'Approved', 182, '2026-09-12 14:00:00', 1, '2026-09-12 15:00:00', NULL),
(2, 2, 1, NULL, 1, 'Smart Campus Assistant', 'Approved Final Year Project topic selected for this team.', 'Approved', 186, '2026-09-12 14:00:00', 1, '2026-09-12 15:00:00', NULL),
(3, 3, 1, NULL, 1, 'Student Wellbeing Hub', 'Approved Final Year Project topic selected for this team.', 'Approved', 190, '2026-09-12 14:00:00', 1, '2026-09-12 15:00:00', NULL),
(4, 4, 1, NULL, 1, 'Campus Navigation', 'Approved Final Year Project topic selected for this team.', 'Approved', 194, '2026-09-12 14:00:00', 1, '2026-09-12 15:00:00', NULL),
(5, 5, 2, NULL, 1, 'Smart Attendance', 'Approved Final Year Project topic selected for this team.', 'Approved', 242, '2026-09-12 14:00:00', 1, '2026-09-12 15:00:00', NULL),
(6, 6, 2, NULL, 1, 'AI Study Planner', 'Approved Final Year Project topic selected for this team.', 'Approved', 246, '2026-09-12 14:00:00', 1, '2026-09-12 15:00:00', NULL),
(7, 7, 2, NULL, 1, 'Campus Event Finder', 'Approved Final Year Project topic selected for this team.', 'Approved', 250, '2026-09-12 14:00:00', 1, '2026-09-12 15:00:00', NULL),
(8, 8, 4, NULL, 1, 'Peer Tutoring', 'Approved Final Year Project topic selected for this team.', 'Approved', 202, '2026-09-12 14:00:00', 2, '2026-09-12 15:00:00', NULL),
(9, 9, 4, NULL, 1, 'Campus Map AR', 'Approved Final Year Project topic selected for this team.', 'Approved', 206, '2026-09-12 14:00:00', 2, '2026-09-12 15:00:00', NULL),
(10, 10, 4, NULL, 1, 'Lab Equipment Tracker', 'Approved Final Year Project topic selected for this team.', 'Approved', 210, '2026-09-12 14:00:00', 2, '2026-09-12 15:00:00', NULL),
(11, 11, 5, NULL, 1, 'Cybersecurity Awareness', 'Approved Final Year Project topic selected for this team.', 'Approved', 278, '2026-09-12 14:00:00', 2, '2026-09-12 15:00:00', NULL),
(12, 12, 5, NULL, 1, 'Smart Recycling', 'Approved Final Year Project topic selected for this team.', 'Approved', 282, '2026-09-12 14:00:00', 2, '2026-09-12 15:00:00', NULL),
(13, 13, 5, NULL, 1, 'Library Seat Finder', 'Approved Final Year Project topic selected for this team.', 'Approved', 286, '2026-09-12 14:00:00', 2, '2026-09-12 15:00:00', NULL),
(14, 14, 6, NULL, 1, 'Study Group Matcher', 'Approved Final Year Project topic selected for this team.', 'Approved', 222, '2026-09-12 14:00:00', 3, '2026-09-12 15:00:00', NULL),
(15, 15, 6, NULL, 1, 'Campus Lost and Found', 'Approved Final Year Project topic selected for this team.', 'Approved', 226, '2026-09-12 14:00:00', 3, '2026-09-12 15:00:00', NULL),
(16, 16, 6, NULL, 1, 'Lecture Capture Assistant', 'Approved Final Year Project topic selected for this team.', 'Approved', 230, '2026-09-12 14:00:00', 3, '2026-09-12 15:00:00', NULL),
(17, 17, 7, NULL, 1, 'Food Queue Manager', 'Approved Final Year Project topic selected for this team.', 'Approved', 298, '2026-09-12 14:00:00', 4, '2026-09-12 15:00:00', NULL),
(18, 18, 7, NULL, 1, 'Allergy-Safe Menu', 'Approved Final Year Project topic selected for this team.', 'Approved', 302, '2026-09-12 14:00:00', 4, '2026-09-12 15:00:00', NULL),
(19, 19, 7, NULL, 1, 'Sustainable Dining', 'Approved Final Year Project topic selected for this team.', 'Approved', 306, '2026-09-12 14:00:00', 4, '2026-09-12 15:00:00', NULL);

-- --------------------------------------------------------

--
-- 資料表結構 `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('teacher','student','admin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_active_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `last_active_at`, `created_at`) VALUES
(1, 'Esther Yuen', 'wyyuen@vtc.edu.hk', '12345', 'teacher', '2026-11-03 16:27:23', '2026-10-07 11:11:58'),
(2, 'Dr. Alan Ho', 'alanho@vtc.edu.hk', '12345', 'teacher', '2026-10-20 18:36:02', '2026-10-07 11:11:58'),
(3, 'Prof. Rebecca Lam', 'rebeccalam@vtc.edu.hk', '12345', 'teacher', NULL, '2026-10-07 11:11:58'),
(4, 'Dr. Vincent Ng', 'vincentng@vtc.edu.hk', '12345', 'teacher', NULL, '2026-10-07 11:11:58'),
(5, 'Ms. Karen Tsang', 'karentsang@vtc.edu.hk', '12345', 'teacher', '2026-10-20 15:08:34', '2026-10-07 11:11:58'),
(6, 'CHAN Ho Yin', '260222111@stu.vtc.edu.hk', '12345', 'student', '2026-11-10 00:10:38', '2026-10-07 11:11:58'),
(7, 'CHUI Ka Chun', '260222112@stu.vtc.edu.hk', '12345', 'student', '2026-11-10 00:10:06', '2026-10-07 11:11:58'),
(8, 'LAI Ka-Yu Savio', '260222113@stu.vtc.edu.hk', '12345', 'student', '2026-11-01 15:30:29', '2026-10-07 11:11:58'),
(9, 'MIAO Tung', '260222114@stu.vtc.edu.hk', '12345', 'student', '2026-11-01 15:07:35', '2026-10-07 11:11:58'),
(187, 'AU Ka Chun', 'student1@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(188, 'CHAN Wing Yan', 'student2@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(189, 'CHENG Ho Yin', 'student3@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(190, 'CHEUNG Mei Ling', 'student4@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(191, 'CHIU Chun Hei', 'student5@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(192, 'CHOW Ka Yan', 'student6@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(193, 'CHU Hiu Tung', 'student7@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(194, 'FUNG Sze Wing', 'student8@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(195, 'HE Man Kit', 'student9@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(196, 'HO Cheuk Yiu', 'student10@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(197, 'HUANG Yuen Ting', 'student11@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(198, 'HUI Tsz Hin', 'student12@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(199, 'IP Ho Kwan', 'student13@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(200, 'KWAN Wai Lam', 'student14@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(201, 'KWOK Ching Man', 'student15@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(202, 'LAI Ka Ho', 'student16@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(203, 'LAM Wing Chi', 'student17@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(204, 'LAU Chun Ming', 'student18@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(205, 'LEE Mei Yi', 'student19@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(206, 'LEUNG Tsz Ching', 'student20@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(207, 'LI Chi Hang', 'student21@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(208, 'LIN Man Yee', 'student22@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(209, 'LO Ka Wai', 'student23@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(210, 'LUO Ho Cheung', 'student24@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(211, 'MAK Wing Lam', 'student25@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(212, 'NG Ka Chun', 'student26@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(213, 'NGAI Wing Yan', 'student27@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(214, 'POON Ho Yin', 'student28@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(215, 'QIU Mei Ling', 'student29@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(216, 'SIU Chun Hei', 'student30@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(217, 'SO Ka Yan', 'student31@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(218, 'TAM Hiu Tung', 'student32@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(219, 'TANG Sze Wing', 'student33@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(220, 'TSE Man Kit', 'student34@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(221, 'WONG Cheuk Yiu', 'student35@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(222, 'WU Yuen Ting', 'student36@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(223, 'XU Tsz Hin', 'student37@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(224, 'YAU Ho Kwan', 'student38@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(225, 'YIP Wai Lam', 'student39@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(226, 'YU Ching Man', 'student40@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(227, 'AU Ka Ho', 'student41@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(228, 'CHAN Wing Chi', 'student42@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(229, 'CHENG Chun Ming', 'student43@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(230, 'CHEUNG Mei Yi', 'student44@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(231, 'CHIU Tsz Ching', 'student45@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(232, 'CHOW Chi Hang', 'student46@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(233, 'CHU Man Yee', 'student47@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(234, 'FUNG Ka Wai', 'student48@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(235, 'HE Ho Cheung', 'student49@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(236, 'HO Wing Lam', 'student50@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(237, 'HUANG Ka Chun', 'student51@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(238, 'HUI Wing Yan', 'student52@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(239, 'IP Ho Yin', 'student53@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(240, 'KWAN Mei Ling', 'student54@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(241, 'KWOK Chun Hei', 'student55@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(242, 'LAI Ka Yan', 'student56@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(243, 'LAM Hiu Tung', 'student57@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(244, 'LAU Sze Wing', 'student58@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(245, 'LEE Man Kit', 'student59@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(246, 'LEUNG Cheuk Yiu', 'student60@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(247, 'LI Yuen Ting', 'student61@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(248, 'LIN Tsz Hin', 'student62@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(249, 'LO Ho Kwan', 'student63@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(250, 'LUO Wai Lam', 'student64@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(251, 'MAK Ching Man', 'student65@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(252, 'NG Ka Ho', 'student66@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(253, 'NGAI Wing Chi', 'student67@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(254, 'POON Chun Ming', 'student68@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(255, 'QIU Mei Yi', 'student69@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(256, 'SIU Tsz Ching', 'student70@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(257, 'SO Chi Hang', 'student71@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(258, 'TAM Man Yee', 'student72@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(259, 'TANG Ka Wai', 'student73@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(260, 'TSE Ho Cheung', 'student74@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(261, 'WONG Wing Lam', 'student75@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(262, 'WU Ka Chun', 'student76@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(263, 'XU Wing Yan', 'student77@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(264, 'YAU Ho Yin', 'student78@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(265, 'YIP Mei Ling', 'student79@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(266, 'YU Chun Hei', 'student80@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(267, 'AU Ka Yan', 'student81@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(268, 'CHAN Hiu Tung', 'student82@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(269, 'CHENG Sze Wing', 'student83@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(270, 'CHEUNG Man Kit', 'student84@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(271, 'CHIU Cheuk Yiu', 'student85@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(272, 'CHOW Yuen Ting', 'student86@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(273, 'CHU Tsz Hin', 'student87@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(274, 'FUNG Ho Kwan', 'student88@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(275, 'HE Wai Lam', 'student89@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(276, 'HO Ching Man', 'student90@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(277, 'HUANG Ka Ho', 'student91@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(278, 'HUI Wing Chi', 'student92@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(279, 'IP Chun Ming', 'student93@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(280, 'KWAN Mei Yi', 'student94@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(281, 'KWOK Tsz Ching', 'student95@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(282, 'LAI Chi Hang', 'student96@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(283, 'LAM Man Yee', 'student97@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(284, 'LAU Ka Wai', 'student98@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(285, 'LEE Ho Cheung', 'student99@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(286, 'LEUNG Wing Lam', 'student100@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(287, 'LI Ka Chun', 'student101@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(288, 'LIN Wing Yan', 'student102@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(289, 'LO Ho Yin', 'student103@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(290, 'LUO Mei Ling', 'student104@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(291, 'MAK Chun Hei', 'student105@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(292, 'NG Ka Yan', 'student106@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(293, 'NGAI Hiu Tung', 'student107@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(294, 'POON Sze Wing', 'student108@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(295, 'QIU Man Kit', 'student109@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(296, 'SIU Cheuk Yiu', 'student110@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(297, 'SO Yuen Ting', 'student111@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(298, 'TAM Tsz Hin', 'student112@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(299, 'TANG Ho Kwan', 'student113@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(300, 'TSE Wai Lam', 'student114@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(301, 'WONG Ching Man', 'student115@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(302, 'WU Ka Ho', 'student116@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(303, 'XU Wing Chi', 'student117@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(304, 'YAU Chun Ming', 'student118@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(305, 'YIP Mei Yi', 'student119@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(306, 'YU Tsz Ching', 'student120@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(307, 'AU Chi Hang', 'student121@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(308, 'CHAN Man Yee', 'student122@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(309, 'CHENG Ka Wai', 'student123@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(310, 'CHEUNG Ho Cheung', 'student124@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(311, 'CHIU Wing Lam', 'student125@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(312, 'CHOW Ka Chun', 'student126@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(313, 'CHU Wing Yan', 'student127@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(314, 'FUNG Ho Yin', 'student128@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(315, 'HE Mei Ling', 'student129@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(316, 'HO Chun Hei', 'student130@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(317, 'HUANG Ka Yan', 'student131@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(318, 'HUI Hiu Tung', 'student132@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(319, 'IP Sze Wing', 'student133@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(320, 'KWAN Man Kit', 'student134@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(321, 'KWOK Cheuk Yiu', 'student135@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(322, 'LAI Yuen Ting', 'student136@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(323, 'LAM Tsz Hin', 'student137@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(324, 'LAU Ho Kwan', 'student138@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(325, 'LEE Wai Lam', 'student139@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(326, 'LEUNG Ching Man', 'student140@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(327, 'LI Ka Ho', 'student141@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(328, 'LIN Wing Chi', 'student142@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(329, 'LO Chun Ming', 'student143@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(330, 'LUO Mei Yi', 'student144@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(331, 'MAK Tsz Ching', 'student145@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(332, 'NG Chi Hang', 'student146@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(333, 'NGAI Man Yee', 'student147@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(334, 'POON Ka Wai', 'student148@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(335, 'QIU Ho Cheung', 'student149@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(336, 'SIU Wing Lam', 'student150@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(337, 'SO Ka Chun', 'student151@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(338, 'TAM Wing Yan', 'student152@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(339, 'TANG Ho Yin', 'student153@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(340, 'TSE Mei Ling', 'student154@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(341, 'WONG Chun Hei', 'student155@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02'),
(342, 'WU Ka Yan', 'student156@student.teambox.local', '12345', 'student', NULL, '2026-10-20 13:29:02');

--
-- 已匯出資料表的索引
--

--
-- 資料表索引 `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `actor_id` (`actor_id`);

--
-- 資料表索引 `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `target_group_id` (`target_group_id`),
  ADD KEY `target_project_id` (`target_project_id`);

--
-- 資料表索引 `announcement_targets`
--
ALTER TABLE `announcement_targets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ann` (`announcement_id`),
  ADD KEY `idx_target` (`target_type`,`target_id`);

--
-- 資料表索引 `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_calendar_creator_date` (`creator_user_id`,`event_date`),
  ADD KEY `idx_calendar_group_date` (`group_id`,`event_date`);

--
-- 資料表索引 `chat_polls`
--
ALTER TABLE `chat_polls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`);

--
-- 資料表索引 `chat_poll_options`
--
ALTER TABLE `chat_poll_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `poll_id` (`poll_id`);

--
-- 資料表索引 `chat_poll_votes`
--
ALTER TABLE `chat_poll_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_vote` (`poll_id`,`voter_id`),
  ADD KEY `option_id` (`option_id`);

--
-- 資料表索引 `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_classes_code` (`class_code`),
  ADD KEY `idx_classes_course` (`course_id`),
  ADD KEY `idx_classes_teacher` (`teacher_id`);

--
-- 資料表索引 `contributions`
--
ALTER TABLE `contributions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `student_id` (`student_id`);

--
-- 資料表索引 `contribution_votes`
--
ALTER TABLE `contribution_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_contribution_voter` (`contribution_id`,`voter_student_id`),
  ADD KEY `idx_vote_student` (`voter_student_id`);

--
-- 資料表索引 `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- 資料表索引 `course_enrollments`
--
ALTER TABLE `course_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enroll` (`course_id`,`student_id`),
  ADD UNIQUE KEY `uq_course_enrollment` (`course_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- 資料表索引 `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `uploader_id` (`uploader_id`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `idx_files_contribution_id` (`contribution_id`);

--
-- 資料表索引 `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `leader_id` (`leader_id`),
  ADD KEY `creator_id` (`creator_id`);

--
-- 資料表索引 `group_members`
--
ALTER TABLE `group_members`
  ADD PRIMARY KEY (`group_id`,`student_id`),
  ADD UNIQUE KEY `uq_group_member_course` (`student_id`,`course_id`),
  ADD KEY `student_id` (`student_id`);

--
-- 資料表索引 `group_requests`
--
ALTER TABLE `group_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `submitted_by` (`submitted_by`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- 資料表索引 `invitations`
--
ALTER TABLE `invitations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_invite` (`group_id`,`invitee_id`),
  ADD KEY `inviter_id` (`inviter_id`),
  ADD KEY `invitee_id` (`invitee_id`);

--
-- 資料表索引 `leader_votes`
--
ALTER TABLE `leader_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_vote` (`group_id`,`voter_id`),
  ADD KEY `voter_id` (`voter_id`),
  ADD KEY `candidate_id` (`candidate_id`);

--
-- 資料表索引 `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `attachment_id` (`attachment_id`),
  ADD KEY `recipient_id` (`recipient_id`),
  ADD KEY `idx_messages_sender_user` (`sender_user_id`),
  ADD KEY `idx_messages_pinned` (`group_id`,`is_pinned`),
  ADD KEY `fk_messages_pinned_by` (`pinned_by_user_id`);

--
-- 資料表索引 `message_reads`
--
ALTER TABLE `message_reads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_read` (`user_id`,`group_id`),
  ADD KEY `group_id` (`group_id`);

--
-- 資料表索引 `milestones`
--
ALTER TABLE `milestones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- 資料表索引 `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- 資料表索引 `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supervisor_id` (`supervisor_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `idx_projects_draft` (`is_draft`),
  ADD KEY `fk_projects_class` (`class_id`);

--
-- 資料表索引 `project_attachments`
--
ALTER TABLE `project_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- 資料表索引 `project_cross_classes`
--
ALTER TABLE `project_cross_classes`
  ADD PRIMARY KEY (`project_id`,`class_id`),
  ADD KEY `class_id` (`class_id`);

--
-- 資料表索引 `project_extension_requests`
--
ALTER TABLE `project_extension_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- 資料表索引 `project_topics`
--
ALTER TABLE `project_topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project_topics_project` (`project_id`,`is_active`,`position`);

--
-- 資料表索引 `request_members`
--
ALTER TABLE `request_members`
  ADD PRIMARY KEY (`request_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- 資料表索引 `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `student_number` (`student_number`),
  ADD KEY `fk_students_class` (`class_id`);

--
-- 資料表索引 `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `created_by` (`created_by`);

--
-- 資料表索引 `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `teacher_code` (`teacher_code`);

--
-- 資料表索引 `teacher_student`
--
ALTER TABLE `teacher_student`
  ADD PRIMARY KEY (`teacher_id`,`student_id`),
  ADD UNIQUE KEY `uq_teacher_student_student` (`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- 資料表索引 `team_topic_proposals`
--
ALTER TABLE `team_topic_proposals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_team_topic_version` (`group_id`,`version`),
  ADD KEY `idx_topic_proposals_project_status` (`project_id`,`status`),
  ADD KEY `idx_topic_proposals_topic` (`topic_id`),
  ADD KEY `idx_topic_proposals_submitted_by` (`submitted_by`),
  ADD KEY `idx_topic_proposals_reviewed_by` (`reviewed_by`);

--
-- 資料表索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- 在匯出的資料表使用 AUTO_INCREMENT
--

--
-- 使用資料表 AUTO_INCREMENT `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=307;

--
-- 使用資料表 AUTO_INCREMENT `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- 使用資料表 AUTO_INCREMENT `announcement_targets`
--
ALTER TABLE `announcement_targets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用資料表 AUTO_INCREMENT `calendar_events`
--
ALTER TABLE `calendar_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用資料表 AUTO_INCREMENT `chat_polls`
--
ALTER TABLE `chat_polls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表 AUTO_INCREMENT `chat_poll_options`
--
ALTER TABLE `chat_poll_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表 AUTO_INCREMENT `chat_poll_votes`
--
ALTER TABLE `chat_poll_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表 AUTO_INCREMENT `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 使用資料表 AUTO_INCREMENT `contributions`
--
ALTER TABLE `contributions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用資料表 AUTO_INCREMENT `contribution_votes`
--
ALTER TABLE `contribution_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- 使用資料表 AUTO_INCREMENT `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用資料表 AUTO_INCREMENT `course_enrollments`
--
ALTER TABLE `course_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=346;

--
-- 使用資料表 AUTO_INCREMENT `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用資料表 AUTO_INCREMENT `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- 使用資料表 AUTO_INCREMENT `group_requests`
--
ALTER TABLE `group_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 使用資料表 AUTO_INCREMENT `invitations`
--
ALTER TABLE `invitations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表 AUTO_INCREMENT `leader_votes`
--
ALTER TABLE `leader_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表 AUTO_INCREMENT `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- 使用資料表 AUTO_INCREMENT `message_reads`
--
ALTER TABLE `message_reads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=170;

--
-- 使用資料表 AUTO_INCREMENT `milestones`
--
ALTER TABLE `milestones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- 使用資料表 AUTO_INCREMENT `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- 使用資料表 AUTO_INCREMENT `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- 使用資料表 AUTO_INCREMENT `project_attachments`
--
ALTER TABLE `project_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表 AUTO_INCREMENT `project_extension_requests`
--
ALTER TABLE `project_extension_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表 AUTO_INCREMENT `project_topics`
--
ALTER TABLE `project_topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用資料表 AUTO_INCREMENT `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=338;

--
-- 使用資料表 AUTO_INCREMENT `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- 使用資料表 AUTO_INCREMENT `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用資料表 AUTO_INCREMENT `team_topic_proposals`
--
ALTER TABLE `team_topic_proposals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- 使用資料表 AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=343;

--
-- 已匯出資料表的限制(Constraint)
--

--
-- 資料表的 Constraints `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activities_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activities_ibfk_3` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- 資料表的 Constraints `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `announcements_ibfk_2` FOREIGN KEY (`target_group_id`) REFERENCES `groups` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `announcements_ibfk_3` FOREIGN KEY (`target_project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL;

--
-- 資料表的 Constraints `announcement_targets`
--
ALTER TABLE `announcement_targets`
  ADD CONSTRAINT `announcement_targets_ibfk_1` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE;

--
-- 資料表的 Constraints `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD CONSTRAINT `fk_calendar_event_creator` FOREIGN KEY (`creator_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_calendar_event_group` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE;

--
-- 資料表的 Constraints `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `fk_classes_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `fk_classes_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`);

--
-- 資料表的 Constraints `contributions`
--
ALTER TABLE `contributions`
  ADD CONSTRAINT `contributions_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contributions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- 資料表的 Constraints `contribution_votes`
--
ALTER TABLE `contribution_votes`
  ADD CONSTRAINT `fk_vote_contribution` FOREIGN KEY (`contribution_id`) REFERENCES `contributions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_vote_student` FOREIGN KEY (`voter_student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- 資料表的 Constraints `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- 資料表的 Constraints `course_enrollments`
--
ALTER TABLE `course_enrollments`
  ADD CONSTRAINT `course_enrollments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_enrollments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- 資料表的 Constraints `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `files_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `files_ibfk_3` FOREIGN KEY (`uploader_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `files_ibfk_4` FOREIGN KEY (`reviewed_by`) REFERENCES `teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `files_ibfk_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_files_contribution` FOREIGN KEY (`contribution_id`) REFERENCES `contributions` (`id`) ON DELETE CASCADE;

--
-- 資料表的 Constraints `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `groups_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `groups_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `groups_ibfk_3` FOREIGN KEY (`leader_id`) REFERENCES `students` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `groups_ibfk_creator` FOREIGN KEY (`creator_id`) REFERENCES `students` (`id`) ON DELETE SET NULL;

--
-- 資料表的 Constraints `group_members`
--
ALTER TABLE `group_members`
  ADD CONSTRAINT `group_members_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_members_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- 資料表的 Constraints `group_requests`
--
ALTER TABLE `group_requests`
  ADD CONSTRAINT `group_requests_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_requests_ibfk_2` FOREIGN KEY (`submitted_by`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_requests_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `teachers` (`id`) ON DELETE SET NULL;

--
-- 資料表的 Constraints `invitations`
--
ALTER TABLE `invitations`
  ADD CONSTRAINT `invitations_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invitations_ibfk_2` FOREIGN KEY (`inviter_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invitations_ibfk_3` FOREIGN KEY (`invitee_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- 資料表的 Constraints `leader_votes`
--
ALTER TABLE `leader_votes`
  ADD CONSTRAINT `leader_votes_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leader_votes_ibfk_2` FOREIGN KEY (`voter_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leader_votes_ibfk_3` FOREIGN KEY (`candidate_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- 資料表的 Constraints `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_pinned_by` FOREIGN KEY (`pinned_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_messages_sender_user` FOREIGN KEY (`sender_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`attachment_id`) REFERENCES `files` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `messages_ibfk_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- 資料表的 Constraints `message_reads`
--
ALTER TABLE `message_reads`
  ADD CONSTRAINT `message_reads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_reads_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE;

--
-- 資料表的 Constraints `milestones`
--
ALTER TABLE `milestones`
  ADD CONSTRAINT `milestones_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- 資料表的 Constraints `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- 資料表的 Constraints `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_projects_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`supervisor_id`) REFERENCES `teachers` (`id`),
  ADD CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL;

--
-- 資料表的 Constraints `project_attachments`
--
ALTER TABLE `project_attachments`
  ADD CONSTRAINT `project_attachments_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- 資料表的 Constraints `project_cross_classes`
--
ALTER TABLE `project_cross_classes`
  ADD CONSTRAINT `project_cross_classes_class_fk` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_cross_classes_project_fk` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- 資料表的 Constraints `project_extension_requests`
--
ALTER TABLE `project_extension_requests`
  ADD CONSTRAINT `project_ext_req_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_ext_req_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_ext_req_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `teachers` (`id`) ON DELETE SET NULL;

--
-- 資料表的 Constraints `project_topics`
--
ALTER TABLE `project_topics`
  ADD CONSTRAINT `project_topics_project_fk` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- 資料表的 Constraints `request_members`
--
ALTER TABLE `request_members`
  ADD CONSTRAINT `request_members_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `group_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `request_members_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- 資料表的 Constraints `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- 資料表的 Constraints `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `students` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- 資料表的 Constraints `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- 資料表的 Constraints `teacher_student`
--
ALTER TABLE `teacher_student`
  ADD CONSTRAINT `teacher_student_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_student_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- 資料表的 Constraints `team_topic_proposals`
--
ALTER TABLE `team_topic_proposals`
  ADD CONSTRAINT `topic_proposals_group_fk` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `topic_proposals_project_fk` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `topic_proposals_student_fk` FOREIGN KEY (`submitted_by`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `topic_proposals_teacher_fk` FOREIGN KEY (`reviewed_by`) REFERENCES `teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `topic_proposals_topic_fk` FOREIGN KEY (`topic_id`) REFERENCES `project_topics` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
