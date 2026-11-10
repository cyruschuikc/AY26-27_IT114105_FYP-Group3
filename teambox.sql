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
(169, 1, 1, 187, 'joined_team', 'SE-1A · Team 01', '2026-10-20 13:29:03'),
(170, 1, 1, 188, 'joined_team', 'SE-1A · Team 01', '2026-10-20 13:29:03'),
(171, 1, 1, 189, 'joined_team', 'SE-1A · Team 01', '2026-10-20 13:29:03'),
(172, 1, 1, 190, 'joined_team', 'SE-1A · Team 01', '2026-10-20 13:29:03'),
(173, 1, 2, 191, 'joined_team', 'SE-1A · Team 02', '2026-10-20 13:29:03'),
(174, 1, 2, 192, 'joined_team', 'SE-1A · Team 02', '2026-10-20 13:29:03'),
(175, 1, 2, 193, 'joined_team', 'SE-1A · Team 02', '2026-10-20 13:29:03'),
(176, 1, 2, 194, 'joined_team', 'SE-1A · Team 02', '2026-10-20 13:29:03'),
(177, 1, 3, 195, 'joined_team', 'SE-1A · Team 03', '2026-10-20 13:29:03'),
(178, 1, 3, 196, 'joined_team', 'SE-1A · Team 03', '2026-10-20 13:29:03'),
(179, 1, 3, 197, 'joined_team', 'SE-1A · Team 03', '2026-10-20 13:29:03'),
(180, 1, 3, 198, 'joined_team', 'SE-1A · Team 03', '2026-10-20 13:29:03'),
(181, 1, 4, 199, 'joined_team', 'SE-1A · Team 04', '2026-10-20 13:29:03'),
(182, 1, 4, 200, 'joined_team', 'SE-1A · Team 04', '2026-10-20 13:29:03'),
(183, 1, 4, 201, 'joined_team', 'SE-1A · Team 04', '2026-10-20 13:29:03'),
(184, 1, 4, 202, 'joined_team', 'SE-1A · Team 04', '2026-10-20 13:29:03'),
(185, 2, 5, 6, 'joined_team', 'SE-2B · Team 05', '2026-10-20 13:29:03'),
(186, 2, 5, 7, 'joined_team', 'SE-2B · Team 05', '2026-10-20 13:29:03'),
(187, 2, 5, 8, 'joined_team', 'SE-2B · Team 05', '2026-10-20 13:29:03'),
(188, 2, 5, 9, 'joined_team', 'SE-2B · Team 05', '2026-10-20 13:29:03'),
(189, 2, 6, 247, 'joined_team', 'SE-2B · Team 06', '2026-10-20 13:29:03'),
(190, 2, 6, 248, 'joined_team', 'SE-2B · Team 06', '2026-10-20 13:29:03'),
(191, 2, 6, 249, 'joined_team', 'SE-2B · Team 06', '2026-10-20 13:29:03'),
(192, 2, 6, 250, 'joined_team', 'SE-2B · Team 06', '2026-10-20 13:29:03'),
(193, 2, 7, 251, 'joined_team', 'SE-2B · Team 07', '2026-10-20 13:29:03'),
(194, 2, 7, 252, 'joined_team', 'SE-2B · Team 07', '2026-10-20 13:29:03'),
(195, 2, 7, 253, 'joined_team', 'SE-2B · Team 07', '2026-10-20 13:29:03'),
(196, 2, 7, 254, 'joined_team', 'SE-2B · Team 07', '2026-10-20 13:29:03'),
(197, 2, 8, 255, 'joined_team', 'SE-2B · Team 08', '2026-10-20 13:29:03'),
(198, 2, 8, 256, 'joined_team', 'SE-2B · Team 08', '2026-10-20 13:29:03'),
(199, 2, 8, 257, 'joined_team', 'SE-2B · Team 08', '2026-10-20 13:29:03'),
(200, 2, 8, 258, 'joined_team', 'SE-2B · Team 08', '2026-10-20 13:29:03'),
(201, 3, 9, 263, 'joined_team', 'CY-1A · Team 09', '2026-10-20 13:29:03'),
(202, 3, 9, 264, 'joined_team', 'CY-1A · Team 09', '2026-10-20 13:29:03'),
(203, 3, 9, 265, 'joined_team', 'CY-1A · Team 09', '2026-10-20 13:29:03'),
(204, 3, 9, 266, 'joined_team', 'CY-1A · Team 09', '2026-10-20 13:29:03'),
(205, 3, 10, 267, 'joined_team', 'CY-1A · Team 10', '2026-10-20 13:29:03'),
(206, 3, 10, 268, 'joined_team', 'CY-1A · Team 10', '2026-10-20 13:29:03'),
(207, 3, 10, 269, 'joined_team', 'CY-1A · Team 10', '2026-10-20 13:29:03'),
(208, 3, 10, 270, 'joined_team', 'CY-1A · Team 10', '2026-10-20 13:29:03'),
(209, 3, 11, 271, 'joined_team', 'CY-1A · Team 11', '2026-10-20 13:29:03'),
(210, 3, 11, 272, 'joined_team', 'CY-1A · Team 11', '2026-10-20 13:29:03'),
(211, 3, 11, 273, 'joined_team', 'CY-1A · Team 11', '2026-10-20 13:29:03'),
(212, 3, 11, 274, 'joined_team', 'CY-1A · Team 11', '2026-10-20 13:29:03'),
(213, 3, 12, 275, 'joined_team', 'CY-1A · Team 12', '2026-10-20 13:29:03'),
(214, 3, 12, 276, 'joined_team', 'CY-1A · Team 12', '2026-10-20 13:29:03'),
(215, 3, 12, 277, 'joined_team', 'CY-1A · Team 12', '2026-10-20 13:29:03'),
(216, 3, 12, 278, 'joined_team', 'CY-1A · Team 12', '2026-10-20 13:29:03'),
(217, 4, 13, 207, 'joined_team', 'SE-1B · Team 13', '2026-10-20 13:29:03'),
(218, 4, 13, 208, 'joined_team', 'SE-1B · Team 13', '2026-10-20 13:29:03'),
(219, 4, 13, 209, 'joined_team', 'SE-1B · Team 13', '2026-10-20 13:29:03'),
(220, 4, 13, 210, 'joined_team', 'SE-1B · Team 13', '2026-10-20 13:29:03'),
(221, 4, 14, 211, 'joined_team', 'SE-1B · Team 14', '2026-10-20 13:29:03'),
(222, 4, 14, 212, 'joined_team', 'SE-1B · Team 14', '2026-10-20 13:29:03'),
(223, 4, 14, 213, 'joined_team', 'SE-1B · Team 14', '2026-10-20 13:29:03'),
(224, 4, 14, 214, 'joined_team', 'SE-1B · Team 14', '2026-10-20 13:29:03'),
(225, 4, 15, 215, 'joined_team', 'SE-1B · Team 15', '2026-10-20 13:29:03'),
(226, 4, 15, 216, 'joined_team', 'SE-1B · Team 15', '2026-10-20 13:29:03'),
(227, 4, 15, 217, 'joined_team', 'SE-1B · Team 15', '2026-10-20 13:29:03'),
(228, 4, 15, 218, 'joined_team', 'SE-1B · Team 15', '2026-10-20 13:29:03'),
(229, 4, 16, 219, 'joined_team', 'SE-1B · Team 16', '2026-10-20 13:29:03'),
(230, 4, 16, 220, 'joined_team', 'SE-1B · Team 16', '2026-10-20 13:29:03'),
(231, 4, 16, 221, 'joined_team', 'SE-1B · Team 16', '2026-10-20 13:29:03'),
(232, 4, 16, 222, 'joined_team', 'SE-1B · Team 16', '2026-10-20 13:29:03'),
(233, 5, 17, 283, 'joined_team', 'CY-1B · Team 17', '2026-10-20 13:29:03'),
(234, 5, 17, 284, 'joined_team', 'CY-1B · Team 17', '2026-10-20 13:29:03'),
(235, 5, 17, 285, 'joined_team', 'CY-1B · Team 17', '2026-10-20 13:29:03'),
(236, 5, 17, 286, 'joined_team', 'CY-1B · Team 17', '2026-10-20 13:29:03'),
(237, 5, 18, 287, 'joined_team', 'CY-1B · Team 18', '2026-10-20 13:29:03'),
(238, 5, 18, 288, 'joined_team', 'CY-1B · Team 18', '2026-10-20 13:29:03'),
(239, 5, 18, 289, 'joined_team', 'CY-1B · Team 18', '2026-10-20 13:29:03'),
(240, 5, 18, 290, 'joined_team', 'CY-1B · Team 18', '2026-10-20 13:29:03'),
(241, 5, 19, 291, 'joined_team', 'CY-1B · Team 19', '2026-10-20 13:29:03'),
(242, 5, 19, 292, 'joined_team', 'CY-1B · Team 19', '2026-10-20 13:29:03'),
(243, 5, 19, 293, 'joined_team', 'CY-1B · Team 19', '2026-10-20 13:29:03'),
(244, 5, 19, 294, 'joined_team', 'CY-1B · Team 19', '2026-10-20 13:29:03'),
(245, 5, 20, 295, 'joined_team', 'CY-1B · Team 20', '2026-10-20 13:29:03'),
(246, 5, 20, 296, 'joined_team', 'CY-1B · Team 20', '2026-10-20 13:29:03'),
(247, 5, 20, 297, 'joined_team', 'CY-1B · Team 20', '2026-10-20 13:29:03'),
(248, 5, 20, 298, 'joined_team', 'CY-1B · Team 20', '2026-10-20 13:29:03'),
(249, 6, 21, 227, 'joined_team', 'SE-2A · Team 21', '2026-10-20 13:29:03'),
(250, 6, 21, 228, 'joined_team', 'SE-2A · Team 21', '2026-10-20 13:29:03'),
(251, 6, 21, 229, 'joined_team', 'SE-2A · Team 21', '2026-10-20 13:29:03'),
(252, 6, 21, 230, 'joined_team', 'SE-2A · Team 21', '2026-10-20 13:29:03'),
(253, 6, 22, 231, 'joined_team', 'SE-2A · Team 22', '2026-10-20 13:29:03'),
(254, 6, 22, 232, 'joined_team', 'SE-2A · Team 22', '2026-10-20 13:29:03'),
(255, 6, 22, 233, 'joined_team', 'SE-2A · Team 22', '2026-10-20 13:29:03'),
(256, 6, 22, 234, 'joined_team', 'SE-2A · Team 22', '2026-10-20 13:29:03'),
(257, 6, 23, 235, 'joined_team', 'SE-2A · Team 23', '2026-10-20 13:29:03'),
(258, 6, 23, 236, 'joined_team', 'SE-2A · Team 23', '2026-10-20 13:29:03'),
(259, 6, 23, 237, 'joined_team', 'SE-2A · Team 23', '2026-10-20 13:29:03'),
(260, 6, 23, 238, 'joined_team', 'SE-2A · Team 23', '2026-10-20 13:29:03'),
(261, 6, 24, 239, 'joined_team', 'SE-2A · Team 24', '2026-10-20 13:29:03'),
(262, 6, 24, 240, 'joined_team', 'SE-2A · Team 24', '2026-10-20 13:29:03'),
(263, 6, 24, 241, 'joined_team', 'SE-2A · Team 24', '2026-10-20 13:29:03'),
(264, 6, 24, 242, 'joined_team', 'SE-2A · Team 24', '2026-10-20 13:29:03'),
(265, 7, 25, 303, 'joined_team', 'CY-2A · Team 25', '2026-10-20 13:29:03'),
(266, 7, 25, 304, 'joined_team', 'CY-2A · Team 25', '2026-10-20 13:29:03'),
(267, 7, 25, 305, 'joined_team', 'CY-2A · Team 25', '2026-10-20 13:29:03'),
(268, 7, 25, 306, 'joined_team', 'CY-2A · Team 25', '2026-10-20 13:29:03'),
(269, 7, 26, 307, 'joined_team', 'CY-2A · Team 26', '2026-10-20 13:29:03'),
(270, 7, 26, 308, 'joined_team', 'CY-2A · Team 26', '2026-10-20 13:29:03'),
(271, 7, 26, 309, 'joined_team', 'CY-2A · Team 26', '2026-10-20 13:29:03'),
(272, 7, 26, 310, 'joined_team', 'CY-2A · Team 26', '2026-10-20 13:29:03'),
(273, 7, 27, 311, 'joined_team', 'CY-2A · Team 27', '2026-10-20 13:29:03'),
(274, 7, 27, 312, 'joined_team', 'CY-2A · Team 27', '2026-10-20 13:29:03'),
(275, 7, 27, 313, 'joined_team', 'CY-2A · Team 27', '2026-10-20 13:29:03'),
(276, 7, 27, 314, 'joined_team', 'CY-2A · Team 27', '2026-10-20 13:29:03'),
(277, 7, 28, 315, 'joined_team', 'CY-2A · Team 28', '2026-10-20 13:29:03'),
(278, 7, 28, 316, 'joined_team', 'CY-2A · Team 28', '2026-10-20 13:29:03'),
(279, 7, 28, 317, 'joined_team', 'CY-2A · Team 28', '2026-10-20 13:29:03'),
(280, 7, 28, 318, 'joined_team', 'CY-2A · Team 28', '2026-10-20 13:29:03'),
(281, 8, 29, 323, 'joined_team', 'CY-2B · Team 29', '2026-10-20 13:29:03'),
(282, 8, 29, 324, 'joined_team', 'CY-2B · Team 29', '2026-10-20 13:29:03'),
(283, 8, 29, 325, 'joined_team', 'CY-2B · Team 29', '2026-10-20 13:29:03'),
(284, 8, 29, 326, 'joined_team', 'CY-2B · Team 29', '2026-10-20 13:29:03'),
(285, 8, 30, 327, 'joined_team', 'CY-2B · Team 30', '2026-10-20 13:29:03'),
(286, 8, 30, 328, 'joined_team', 'CY-2B · Team 30', '2026-10-20 13:29:03'),
(287, 8, 30, 329, 'joined_team', 'CY-2B · Team 30', '2026-10-20 13:29:03'),
(288, 8, 30, 330, 'joined_team', 'CY-2B · Team 30', '2026-10-20 13:29:03'),
(289, 8, 31, 331, 'joined_team', 'CY-2B · Team 31', '2026-10-20 13:29:03'),
(290, 8, 31, 332, 'joined_team', 'CY-2B · Team 31', '2026-10-20 13:29:03'),
(291, 8, 31, 333, 'joined_team', 'CY-2B · Team 31', '2026-10-20 13:29:03'),
(292, 8, 31, 334, 'joined_team', 'CY-2B · Team 31', '2026-10-20 13:29:03'),
(293, 8, 32, 335, 'joined_team', 'CY-2B · Team 32', '2026-10-20 13:29:03'),
(294, 8, 32, 336, 'joined_team', 'CY-2B · Team 32', '2026-10-20 13:29:03'),
(295, 8, 32, 337, 'joined_team', 'CY-2B · Team 32', '2026-10-20 13:29:03'),
(296, 8, 32, 338, 'joined_team', 'CY-2B · Team 32', '2026-10-20 13:29:03'),
(297, 2, 5, 6, 'created', 'created task \"Abc\"', '2026-10-31 10:58:50'),
(298, 2, 5, 6, 'created', 'created task \"ftgyh\"', '2026-10-31 11:38:48'),
(299, 2, 5, 7, 'created', 'created task \"ABC\"', '2026-10-31 17:50:04'),
(300, 2, 5, 6, 'contributed', 'logged contribution \"ABC\"', '2026-11-01 07:01:32'),
(301, 2, 5, 6, 'contributed', 'logged contribution \"DFG\"', '2026-11-01 07:01:55'),
(302, 2, 5, 8, 'contributed', 'logged contribution \"thdhuiop\"', '2026-11-01 07:08:28'),
(303, 2, 5, 8, 'contributed', 'logged contribution \"thdhuiop\"', '2026-11-01 07:12:18'),
(304, 2, 5, 7, 'created', 'created task \"nhjkl\"', '2026-11-03 09:04:33'),
(305, 2, 5, 7, 'created', 'created task \"dfgh\"', '2026-11-03 09:06:19'),
(306, 2, 5, 7, 'created', 'created task \"fgh\"', '2026-11-09 16:10:29');

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
(2, 5, 1, 'ABC', 'Documentation', 'GHCJK', '2026-11-01', 'Approved', 10, '2026-11-01 15:08:00', '2026-11-01 07:01:32'),
(3, 5, 1, 'DFG', 'Development', 'GHHHHH', '2026-11-01', 'Approved', 10, '2026-11-01 15:06:46', '2026-11-01 07:01:55'),
(4, 5, 3, 'thdhuiop', 'Development', '', '2026-11-01', 'Pending', 0, NULL, '2026-11-01 07:08:28'),
(5, 5, 3, 'thdhuiop', 'Development', '', '2026-11-01', 'Pending', 0, NULL, '2026-11-01 07:12:18');

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
(1, 2, 5, NULL, 2, 1, '4524_CA_Test_Revision Revised.pdf', '4524_CA_Test_Revision Revised.pdf', NULL, 'contribution_2_6ae6e3ccde7d2105732365.pdf', '384.35 KB', 'PDF', 'Unviewed', '2026-11-01 07:01:32', NULL, NULL, NULL),
(2, 2, 5, NULL, 4, 3, 'bell.png', 'bell.png', NULL, 'contribution_4_6ae6e56c2b884584670450.png', '418.22 KB', 'PNG', 'Unviewed', '2026-11-01 07:08:28', NULL, NULL, NULL),
(3, 2, 5, NULL, 5, 3, 'bell.png', 'bell.png', NULL, 'contribution_5_6ae6e65239384375734386.png', '418.22 KB', 'PNG', 'Unviewed', '2026-11-01 07:12:18', NULL, NULL, NULL);

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
(1, 1, 1, 'Team 01', 182, 182, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(2, 1, 1, 'Team 02', 186, 186, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(3, 1, 1, 'Team 03', 190, 190, 4, 'Voting', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(4, 1, 1, 'Team 04', 194, 194, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(5, 2, 1, 'Team 05', 1, 1, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(6, 2, 1, 'Team 06', 242, 242, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(7, 2, 1, 'Team 07', 246, 246, 4, 'Voting', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(8, 2, 1, 'Team 08', 250, 250, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(9, 3, 1, 'Team 09', 258, 258, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(10, 3, 1, 'Team 10', 262, 262, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(11, 3, 1, 'Team 11', 266, 266, 4, 'Waiting_Send', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(12, 3, 1, 'Team 12', 270, 270, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(13, 4, 1, 'Team 13', 202, 202, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(14, 4, 1, 'Team 14', 206, 206, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(15, 4, 1, 'Team 15', 210, 210, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(16, 4, 1, 'Team 16', 214, 214, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(17, 5, 1, 'Team 17', 278, 278, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(18, 5, 1, 'Team 18', 282, 282, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(19, 5, 1, 'Team 19', 286, 286, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(20, 5, 1, 'Team 20', 290, 290, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(21, 6, 1, 'Team 21', 222, 222, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(22, 6, 1, 'Team 22', 226, 226, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(23, 6, 1, 'Team 23', 230, 230, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(24, 6, 1, 'Team 24', 234, 234, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(25, 7, 1, 'Team 25', 298, 298, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(26, 7, 1, 'Team 26', 302, 302, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(27, 7, 1, 'Team 27', 306, 306, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(28, 7, 1, 'Team 28', 310, 310, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(29, 8, 1, 'Team 29', 318, 318, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(30, 8, 1, 'Team 30', 322, 322, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(31, 8, 1, 'Team 31', 326, 326, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03'),
(32, 8, 1, 'Team 32', 330, 330, 4, 'Executing', '2026-10-20 21:29:03', '2026-10-20 13:29:03');

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
(1, 182, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(1, 183, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(1, 184, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(1, 185, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(2, 186, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(2, 187, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(2, 188, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(2, 189, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(3, 190, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(3, 191, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(3, 192, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(3, 193, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(4, 194, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(4, 195, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(4, 196, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(4, 197, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(5, 1, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(5, 2, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(5, 3, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(5, 4, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(6, 242, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(6, 243, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(6, 244, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(6, 245, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(7, 246, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(7, 247, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(7, 248, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(7, 249, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(8, 250, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(8, 251, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(8, 252, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(8, 253, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(9, 258, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(9, 259, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(9, 260, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(9, 261, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(10, 262, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(10, 263, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(10, 264, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(10, 265, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(11, 266, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(11, 267, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(11, 268, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(11, 269, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(12, 270, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(12, 271, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(12, 272, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(12, 273, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(13, 202, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(13, 203, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(13, 204, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(13, 205, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(14, 206, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(14, 207, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(14, 208, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(14, 209, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(15, 210, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(15, 211, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(15, 212, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(15, 213, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(16, 214, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(16, 215, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(16, 216, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(16, 217, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(17, 278, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(17, 279, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(17, 280, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(17, 281, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(18, 282, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(18, 283, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(18, 284, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(18, 285, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(19, 286, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(19, 287, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(19, 288, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(19, 289, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(20, 290, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(20, 291, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(20, 292, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(20, 293, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(21, 222, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(21, 223, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(21, 224, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(21, 225, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(22, 226, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(22, 227, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(22, 228, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(22, 229, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(23, 230, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(23, 231, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(23, 232, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(23, 233, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(24, 234, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(24, 235, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(24, 236, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(24, 237, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(25, 298, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(25, 299, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(25, 300, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(25, 301, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(26, 302, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(26, 303, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(26, 304, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(26, 305, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(27, 306, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(27, 307, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(27, 308, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(27, 309, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(28, 310, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(28, 311, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(28, 312, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(28, 313, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(29, 318, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(29, 319, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(29, 320, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(29, 321, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(30, 322, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(30, 323, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(30, 324, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(30, 325, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(31, 326, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(31, 327, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(31, 328, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(31, 329, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(32, 330, NULL, NULL, 'leader', '2026-10-20 13:29:03'),
(32, 331, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(32, 332, NULL, NULL, 'member', '2026-10-20 13:29:03'),
(32, 333, NULL, NULL, 'member', '2026-10-20 13:29:03');

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
(6, 'Team 03', 1, 190, 'Accepted', NULL, '2026-10-20 13:29:03', '2026-11-01 01:18:56', 1),
(7, 'Team 07', 2, 246, 'Accepted', NULL, '2026-10-20 13:29:03', '2026-11-01 01:18:58', 1),
(8, 'Team 11', 3, 266, 'Pending', NULL, '2026-10-20 13:29:03', NULL, NULL);

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
(73, 1, 'Proposal and planning', '2026-09-01', '2026-10-15', 'Pending', 0),
(74, 1, 'Prototype checkpoint', '2026-10-16', '2026-11-20', 'Pending', 0),
(75, 1, 'Final submission', '2026-11-21', '2027-01-20', 'Pending', 0),
(76, 2, 'Proposal and planning', '2026-09-01', '2026-10-15', 'Pending', 0),
(77, 2, 'Prototype checkpoint', '2026-10-16', '2026-11-20', 'Pending', 0),
(78, 2, 'Final submission', '2026-11-21', '2027-01-20', 'Pending', 0),
(79, 3, 'Proposal and planning', '2026-09-01', '2026-10-15', 'Pending', 0),
(80, 3, 'Prototype checkpoint', '2026-10-16', '2026-11-20', 'Pending', 0),
(81, 3, 'Final submission', '2026-11-21', '2027-01-20', 'Pending', 0),
(82, 4, 'Proposal and planning', '2026-09-01', '2026-10-15', 'Pending', 0),
(83, 4, 'Prototype checkpoint', '2026-10-16', '2026-11-20', 'Pending', 0),
(84, 4, 'Final submission', '2026-11-21', '2027-01-20', 'Pending', 0),
(85, 5, 'Proposal and planning', '2026-09-01', '2026-10-15', 'Pending', 0),
(86, 5, 'Prototype checkpoint', '2026-10-16', '2026-11-20', 'Pending', 0),
(87, 5, 'Final submission', '2026-11-21', '2027-01-20', 'Pending', 0),
(88, 6, 'Proposal and planning', '2026-09-01', '2026-10-15', 'Pending', 0),
(89, 6, 'Prototype checkpoint', '2026-10-16', '2026-11-20', 'Pending', 0),
(90, 6, 'Final submission', '2026-11-21', '2027-01-20', 'Pending', 0),
(91, 7, 'Proposal and planning', '2026-09-01', '2026-10-15', 'Pending', 0),
(92, 7, 'Prototype checkpoint', '2026-10-16', '2026-11-20', 'Pending', 0),
(93, 7, 'Final submission', '2026-11-21', '2027-01-20', 'Pending', 0),
(94, 8, 'Proposal and planning', '2026-09-01', '2026-10-15', 'Pending', 0),
(95, 8, 'Prototype checkpoint', '2026-10-16', '2026-11-20', 'Pending', 0),
(96, 8, 'Final submission', '2026-11-21', '2027-01-20', 'Pending', 0),
(97, 9, 'ffgf', '2026-11-12', '2026-12-01', 'Pending', 0);

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
(1, 'ITE4116M_SE-1A_Final Year Project', 'Final Year Project', 'Self', NULL, NULL, 1, 1, 1, '2026-09-01', '2027-01-31', 'Executing', 20, 'Final Year Project workspace for SE-1A. Each team proposes and develops its own topic.', 'Students work in teams of four and submit progress through TeamBox.', 'Free', '2026-10-18', 0, 4, '[\"invite\",\"remove\",\"rename\",\"assign\",\"submit\"]', 'Lock', 'Course', 3, 1, 0, NULL, '2026-10-20 13:29:03'),
(2, 'ITE4116M_SE-2B_Final Year Project', 'Final Year Project', 'Self', NULL, NULL, 1, 4, 1, '2026-09-01', '2027-01-31', 'Executing', 20, 'Final Year Project workspace for SE-2B. Each team proposes and develops its own topic.', 'Students work in teams of four and submit progress through TeamBox.', 'Free', '2026-11-03', 0, 4, '[\"invite\",\"remove\",\"rename\",\"assign\",\"submit\"]', 'Lock', 'Course', 3, 1, 0, NULL, '2026-10-20 13:29:03'),
(3, 'ITE4116M_CY-1A_Final Year Project', 'Final Year Project', 'Self', NULL, NULL, 1, 5, 1, '2026-09-01', '2027-01-31', 'Executing', 20, 'Final Year Project workspace for CY-1A. Each team proposes and develops its own topic.', 'Students work in teams of four and submit progress through TeamBox.', 'Free', '2026-11-10', 0, 4, '[\"invite\",\"remove\",\"rename\",\"assign\",\"submit\"]', 'Lock', 'Course', 3, 1, 0, NULL, '2026-10-20 13:29:03'),
(4, 'ITE4116M_SE-1B_Final Year Project', 'Final Year Project', 'Self', NULL, NULL, 1, 2, 2, '2026-09-01', '2027-01-31', 'Executing', 20, 'Final Year Project workspace for SE-1B. Each team proposes and develops its own topic.', 'Students work in teams of four and submit progress through TeamBox.', 'Free', '2026-11-05', 0, 4, '[\"invite\",\"remove\",\"rename\",\"assign\",\"submit\"]', 'Lock', 'Course', 3, 1, 0, NULL, '2026-10-20 13:29:03'),
(5, 'ITE4116M_CY-1B_Final Year Project', 'Final Year Project', 'Self', NULL, NULL, 1, 6, 2, '2026-09-01', '2027-01-31', 'Executing', 20, 'Final Year Project workspace for CY-1B. Each team proposes and develops its own topic.', 'Students work in teams of four and submit progress through TeamBox.', 'Free', '2026-11-07', 0, 4, '[\"invite\",\"remove\",\"rename\",\"assign\",\"submit\"]', 'Lock', 'Course', 3, 1, 0, NULL, '2026-10-20 13:29:03'),
(6, 'ITE4116M_SE-2A_Final Year Project', 'Final Year Project', 'Self', NULL, NULL, 1, 3, 3, '2026-09-01', '2027-01-31', 'Executing', 20, 'Final Year Project workspace for SE-2A. Each team proposes and develops its own topic.', 'Students work in teams of four and submit progress through TeamBox.', 'Free', '2026-11-07', 0, 4, '[\"invite\",\"remove\",\"rename\",\"assign\",\"submit\"]', 'Lock', 'Course', 3, 1, 0, NULL, '2026-10-20 13:29:03'),
(7, 'ITE4116M_CY-2A_Final Year Project', 'Final Year Project', 'Self', NULL, NULL, 1, 7, 4, '2026-09-01', '2027-01-31', 'Executing', 20, 'Final Year Project workspace for CY-2A. Each team proposes and develops its own topic.', 'Students work in teams of four and submit progress through TeamBox.', 'Free', '2026-11-09', 0, 4, '[\"invite\",\"remove\",\"rename\",\"assign\",\"submit\"]', 'Lock', 'Course', 3, 1, 0, NULL, '2026-10-20 13:29:03'),
(8, 'ITE4116M_CY-2B_Final Year Project', 'Final Year Project', 'Self', NULL, NULL, 1, 8, 5, '2026-09-01', '2027-01-31', 'Executing', 20, 'Final Year Project workspace for CY-2B. Each team proposes and develops its own topic.', 'Students work in teams of four and submit progress through TeamBox.', 'Free', '2026-11-11', 0, 4, '[\"invite\",\"remove\",\"rename\",\"assign\",\"submit\"]', 'Lock', 'Course', 3, 1, 0, NULL, '2026-10-20 13:29:03'),
(9, 'ITE4116M_CY-1A_Assignment', 'Assignment', 'Multi', 'EA', 3, 1, 5, 1, '2026-10-31', '2027-02-20', 'Recruiting', 0, NULL, NULL, 'Free', '2026-11-14', 0, 4, '[\"invite\",\"rename\",\"assign\",\"submit\"]', 'Lock', 'Course', 3, 1, 0, NULL, '2026-10-31 17:20:03');

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

INSERT INTO `tasks` (`id`, `project_id`, `group_id`, `title`, `description`, `assigned_to`, `created_by`, `status`, `priority`, `due_date`, `due_time`, `completed_at`, `created_at`) VALUES
(80, 1, 1, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 182, 187, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(81, 1, 1, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 183, 187, 'In Progress', 'Medium', '2026-10-19', NULL, NULL, '2026-10-20 13:29:03'),
(82, 1, 2, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 186, 191, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(83, 1, 2, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 187, 191, 'In Progress', 'Medium', '2026-10-27', NULL, NULL, '2026-10-20 13:29:03'),
(84, 1, 4, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 194, 199, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(85, 1, 4, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 195, 199, 'In Progress', 'Medium', '2026-10-27', NULL, NULL, '2026-10-20 13:29:03'),
(86, 2, 5, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 1, 6, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(87, 2, 5, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 2, 6, 'In Progress', 'Medium', '2026-10-19', NULL, NULL, '2026-10-20 13:29:03'),
(88, 2, 6, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 242, 247, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(89, 2, 6, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 243, 247, 'In Progress', 'Medium', '2026-10-27', NULL, NULL, '2026-10-20 13:29:03'),
(90, 2, 8, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 250, 255, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(91, 2, 8, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 251, 255, 'In Progress', 'Medium', '2026-10-27', NULL, NULL, '2026-10-20 13:29:03'),
(92, 3, 9, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 258, 263, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(93, 3, 9, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 259, 263, 'In Progress', 'Medium', '2026-10-19', NULL, NULL, '2026-10-20 13:29:03'),
(94, 3, 10, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 262, 267, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(95, 3, 10, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 263, 267, 'In Progress', 'Medium', '2026-10-27', NULL, NULL, '2026-10-20 13:29:03'),
(96, 3, 12, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 270, 275, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(97, 3, 12, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 271, 275, 'In Progress', 'Medium', '2026-10-27', NULL, NULL, '2026-10-20 13:29:03'),
(98, 4, 13, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 202, 207, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(99, 4, 13, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 203, 207, 'In Progress', 'Medium', '2026-10-19', NULL, NULL, '2026-10-20 13:29:03'),
(100, 4, 14, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 206, 211, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(101, 4, 14, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 207, 211, 'In Progress', 'Medium', '2026-10-27', NULL, NULL, '2026-10-20 13:29:03'),
(102, 4, 15, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 210, 215, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(103, 4, 15, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 211, 215, 'In Progress', 'Medium', '2026-10-27', NULL, NULL, '2026-10-20 13:29:03'),
(104, 4, 16, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 214, 219, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(105, 4, 16, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 215, 219, 'In Progress', 'Medium', '2026-10-27', NULL, NULL, '2026-10-20 13:29:03'),
(106, 5, 17, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 278, 283, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(107, 5, 17, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 279, 283, 'In Progress', 'Medium', '2026-10-19', NULL, NULL, '2026-10-20 13:29:03'),
(108, 5, 18, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 282, 287, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(109, 5, 18, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 283, 287, 'In Progress', 'Medium', '2026-10-27', NULL, NULL, '2026-10-20 13:29:03'),
(110, 5, 19, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 286, 291, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(111, 5, 19, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 287, 291, 'In Progress', 'Medium', '2026-10-27', NULL, NULL, '2026-10-20 13:29:03'),
(112, 5, 20, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 290, 295, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(113, 5, 20, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 291, 295, 'In Progress', 'Medium', '2026-10-27', NULL, NULL, '2026-10-20 13:29:03'),
(114, 6, 21, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 222, 227, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(115, 6, 21, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 223, 227, 'In Progress', 'Medium', '2026-10-19', NULL, NULL, '2026-10-20 13:29:03'),
(116, 6, 22, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 226, 231, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(117, 6, 22, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 227, 231, 'In Progress', 'Medium', '2026-10-27', NULL, NULL, '2026-10-20 13:29:03'),
(118, 6, 23, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 230, 235, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(119, 6, 23, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 231, 235, 'In Progress', 'Medium', '2026-10-27', NULL, NULL, '2026-10-20 13:29:03'),
(120, 6, 24, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 234, 239, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(121, 6, 24, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 235, 239, 'In Progress', 'Medium', '2026-10-27', NULL, NULL, '2026-10-20 13:29:03'),
(122, 7, 25, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 298, 303, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(123, 7, 25, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 299, 303, 'In Progress', 'Medium', '2026-10-19', NULL, NULL, '2026-10-20 13:29:03'),
(124, 7, 26, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 302, 307, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(125, 7, 26, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 303, 307, 'In Progress', 'Medium', '2026-10-27', NULL, NULL, '2026-10-20 13:29:03'),
(126, 7, 27, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 306, 311, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(127, 7, 27, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 307, 311, 'In Progress', 'Medium', '2026-10-27', NULL, NULL, '2026-10-20 13:29:03'),
(128, 7, 28, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 310, 315, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(129, 7, 28, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 311, 315, 'In Progress', 'Medium', '2026-10-27', NULL, NULL, '2026-10-20 13:29:03'),
(130, 8, 29, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 318, 323, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(131, 8, 29, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 319, 323, 'In Progress', 'Medium', '2026-10-19', NULL, NULL, '2026-10-20 13:29:03'),
(132, 8, 30, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 322, 327, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(133, 8, 30, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 323, 327, 'In Progress', 'Medium', '2026-10-27', NULL, NULL, '2026-10-20 13:29:03'),
(134, 8, 31, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 326, 331, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(135, 8, 31, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 327, 331, 'In Progress', 'Medium', '2026-10-27', NULL, NULL, '2026-10-20 13:29:03'),
(136, 8, 32, 'Prepare project proposal', 'Create the initial proposal and agree on team responsibilities.', 330, 335, 'Completed', 'Medium', '2026-10-06', NULL, '2026-10-10 15:29:03', '2026-10-20 13:29:03'),
(137, 8, 32, 'Complete prototype checkpoint', 'Prepare the current prototype and document the completed work.', 331, 335, 'In Progress', 'Medium', '2026-10-27', NULL, NULL, '2026-10-20 13:29:03'),
(138, 2, 5, 'Abc', 'dfghyujk', 2, 6, 'Pending', 'Medium', '2026-11-11', NULL, NULL, '2026-10-31 10:58:50'),
(139, 2, 5, 'ftgyh', 'fgbn', 2, 6, 'Pending', 'Medium', '2026-11-18', NULL, NULL, '2026-10-31 11:38:48'),
(140, 2, 5, 'ABC', 'DTGHJK', 1, 7, 'Pending', 'Medium', '2026-11-18', NULL, NULL, '2026-10-31 17:50:04'),
(141, 2, 5, 'nhjkl', 'cvbnmk', 1, 7, 'Pending', 'Medium', '2026-11-11', NULL, NULL, '2026-11-03 09:04:33'),
(142, 2, 5, 'dfgh', 'fffff', 1, 7, 'Pending', 'Medium', '2026-11-09', NULL, NULL, '2026-11-03 09:06:19'),
(143, 2, 5, 'fgh', 'zsder', 1, 7, 'Pending', 'Medium', '2026-12-04', '22:15:00', NULL, '2026-11-09 16:10:29');

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
(1, 1, 1, NULL, 1, 'TeamBox', 'Approved Final Year Project topic selected for this team.', 'Approved', 182, '2026-10-20 14:00:00', 1, '2026-10-20 15:00:00', NULL),
(2, 2, 1, NULL, 1, 'Smart Campus Assistant', 'Approved Final Year Project topic selected for this team.', 'Approved', 186, '2026-10-20 14:00:00', 1, '2026-10-20 15:00:00', NULL),
(3, 3, 1, NULL, 1, 'Student Wellbeing Hub', 'Approved Final Year Project topic selected for this team.', 'Approved', 190, '2026-10-20 14:00:00', 1, '2026-10-20 15:00:00', NULL),
(4, 4, 1, NULL, 1, 'Campus Navigation', 'Approved Final Year Project topic selected for this team.', 'Approved', 194, '2026-10-20 14:00:00', 1, '2026-10-20 15:00:00', NULL),
(5, 5, 2, NULL, 1, 'Smart Attendance', 'Approved Final Year Project topic selected for this team.', 'Approved', 1, '2026-10-20 14:00:00', 1, '2026-10-20 15:00:00', NULL),
(6, 6, 2, NULL, 1, 'AI Study Planner', 'Approved Final Year Project topic selected for this team.', 'Approved', 242, '2026-10-20 14:00:00', 1, '2026-10-20 15:00:00', NULL),
(7, 7, 2, NULL, 1, 'Campus Event Finder', 'Approved Final Year Project topic selected for this team.', 'Approved', 246, '2026-10-20 14:00:00', 1, '2026-10-20 15:00:00', NULL),
(8, 8, 2, NULL, 1, 'Peer Tutoring', 'Approved Final Year Project topic selected for this team.', 'Approved', 250, '2026-10-20 14:00:00', 1, '2026-10-20 15:00:00', NULL),
(9, 9, 3, NULL, 1, 'Campus Map AR', 'Approved Final Year Project topic selected for this team.', 'Approved', 258, '2026-10-20 14:00:00', 1, '2026-10-20 15:00:00', NULL),
(10, 10, 3, NULL, 1, 'Lab Equipment Tracker', 'Approved Final Year Project topic selected for this team.', 'Approved', 262, '2026-10-20 14:00:00', 1, '2026-10-20 15:00:00', NULL),
(11, 11, 3, NULL, 1, 'Cybersecurity Awareness', 'Approved Final Year Project topic selected for this team.', 'Approved', 266, '2026-10-20 14:00:00', 1, '2026-10-20 15:00:00', NULL),
(12, 12, 3, NULL, 1, 'Smart Recycling', 'Approved Final Year Project topic selected for this team.', 'Approved', 270, '2026-10-20 14:00:00', 1, '2026-10-20 15:00:00', NULL),
(13, 13, 4, NULL, 1, 'Library Seat Finder', 'Approved Final Year Project topic selected for this team.', 'Approved', 202, '2026-10-20 14:00:00', 2, '2026-10-20 15:00:00', NULL),
(14, 14, 4, NULL, 1, 'Study Group Matcher', 'Approved Final Year Project topic selected for this team.', 'Approved', 206, '2026-10-20 14:00:00', 2, '2026-10-20 15:00:00', NULL),
(15, 15, 4, NULL, 1, 'Campus Lost and Found', 'Approved Final Year Project topic selected for this team.', 'Approved', 210, '2026-10-20 14:00:00', 2, '2026-10-20 15:00:00', NULL),
(16, 16, 4, NULL, 1, 'Lecture Capture Assistant', 'Approved Final Year Project topic selected for this team.', 'Approved', 214, '2026-10-20 14:00:00', 2, '2026-10-20 15:00:00', NULL),
(17, 17, 5, NULL, 1, 'Food Queue Manager', 'Approved Final Year Project topic selected for this team.', 'Approved', 278, '2026-10-20 14:00:00', 2, '2026-10-20 15:00:00', NULL),
(18, 18, 5, NULL, 1, 'Allergy-Safe Menu', 'Approved Final Year Project topic selected for this team.', 'Approved', 282, '2026-10-20 14:00:00', 2, '2026-10-20 15:00:00', NULL),
(19, 19, 5, NULL, 1, 'Sustainable Dining', 'Approved Final Year Project topic selected for this team.', 'Approved', 286, '2026-10-20 14:00:00', 2, '2026-10-20 15:00:00', NULL),
(20, 20, 5, NULL, 1, 'Campus Delivery Hub', 'Approved Final Year Project topic selected for this team.', 'Approved', 290, '2026-10-20 14:00:00', 2, '2026-10-20 15:00:00', NULL),
(21, 21, 6, NULL, 1, 'AI Study Buddy', 'Approved Final Year Project topic selected for this team.', 'Approved', 222, '2026-10-20 14:00:00', 3, '2026-10-20 15:00:00', NULL),
(22, 22, 6, NULL, 1, 'Revision Planner', 'Approved Final Year Project topic selected for this team.', 'Approved', 226, '2026-10-20 14:00:00', 3, '2026-10-20 15:00:00', NULL),
(23, 23, 6, NULL, 1, 'Flashcard Generator', 'Approved Final Year Project topic selected for this team.', 'Approved', 230, '2026-10-20 14:00:00', 3, '2026-10-20 15:00:00', NULL),
(24, 24, 6, NULL, 1, 'Academic Chatbot', 'Approved Final Year Project topic selected for this team.', 'Approved', 234, '2026-10-20 14:00:00', 3, '2026-10-20 15:00:00', NULL),
(25, 25, 7, NULL, 1, 'Green Campus Dashboard', 'Approved Final Year Project topic selected for this team.', 'Approved', 298, '2026-10-20 14:00:00', 4, '2026-10-20 15:00:00', NULL),
(26, 26, 7, NULL, 1, 'Energy Usage Tracker', 'Approved Final Year Project topic selected for this team.', 'Approved', 302, '2026-10-20 14:00:00', 4, '2026-10-20 15:00:00', NULL),
(27, 27, 7, NULL, 1, 'Waste Sorting Guide', 'Approved Final Year Project topic selected for this team.', 'Approved', 306, '2026-10-20 14:00:00', 4, '2026-10-20 15:00:00', NULL),
(28, 28, 7, NULL, 1, 'Sustainable Transport', 'Approved Final Year Project topic selected for this team.', 'Approved', 310, '2026-10-20 14:00:00', 4, '2026-10-20 15:00:00', NULL),
(29, 29, 8, NULL, 1, 'Career Path Finder', 'Approved Final Year Project topic selected for this team.', 'Approved', 318, '2026-10-20 14:00:00', 5, '2026-10-20 15:00:00', NULL),
(30, 30, 8, NULL, 1, 'Internship Matcher', 'Approved Final Year Project topic selected for this team.', 'Approved', 322, '2026-10-20 14:00:00', 5, '2026-10-20 15:00:00', NULL),
(31, 31, 8, NULL, 1, 'Skills Portfolio', 'Approved Final Year Project topic selected for this team.', 'Approved', 326, '2026-10-20 14:00:00', 5, '2026-10-20 15:00:00', NULL),
(32, 32, 8, NULL, 1, 'Graduate Readiness Hub', 'Approved Final Year Project topic selected for this team.', 'Approved', 330, '2026-10-20 14:00:00', 5, '2026-10-20 15:00:00', NULL);

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
