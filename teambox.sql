-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- 主機: 127.0.0.1
-- 產生時間： 2026-10-19 07:12:44
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
(1, 1, 1, 6, 'uploaded', 'uploaded Proposal_v2.docx', '2026-10-07 11:11:59'),
(2, 1, 1, 8, 'completed', 'marked Requirement Analysis complete', '2026-10-07 11:11:59'),
(3, 1, 1, 9, 'commented', 'commented on UI_Wireframe', '2026-10-07 11:11:59'),
(4, 1, 1, 6, 'elected', 'was elected as team leader', '2026-10-07 11:11:59'),
(5, 1, 1, 1, 'locked', 'locked the team roster', '2026-10-07 11:11:59'),
(6, 4, 4, 13, 'uploaded', 'uploaded SRS_v1.docx', '2026-10-07 11:11:59'),
(7, 4, 4, 14, 'uploaded', 'uploaded SeatMap_Mockup.png', '2026-10-07 11:11:59'),
(8, 4, 4, 15, 'uploaded', 'uploaded API_Design.pdf', '2026-10-07 11:11:59'),
(9, 4, 4, 2, 'reviewed', 'accepted SRS_v1.docx', '2026-10-07 11:11:59'),
(10, 5, 5, 14, 'uploaded', 'uploaded Menu_Schema.sql', '2026-10-07 11:11:59'),
(11, 5, 5, 17, 'uploaded', 'uploaded Payment_Flow.pdf', '2026-10-07 11:11:59'),
(12, 6, 6, 18, 'uploaded', 'uploaded Dataset_Description.pdf', '2026-10-07 11:11:59'),
(13, 6, 6, 20, 'uploaded', 'uploaded Baseline_Model.ipynb', '2026-10-07 11:11:59'),
(14, 6, 6, 21, 'uploaded', 'uploaded Prompt_Templates.txt', '2026-10-07 11:11:59'),
(15, 8, 9, 25, 'uploaded', 'uploaded Company_List.xlsx', '2026-10-07 11:11:59'),
(16, 8, 9, 26, 'uploaded', 'uploaded Matching_Algo.pdf', '2026-10-07 11:11:59'),
(17, 8, 9, 27, 'uploaded', 'uploaded Dashboard_Wireframe.fig', '2026-10-07 11:11:59'),
(18, 1, 1, 6, 'created', 'created Task \"Create System Interface\"', '2026-10-07 11:11:59'),
(19, 5, 5, 2, 'reviewed', 'rejected API_Design.pdf', '2026-10-07 11:11:59'),
(22, 1, 1, 7, 'uploaded', '上傳了共享檔案 bell.png', '2026-10-17 15:51:19'),
(23, 1, 1, 7, 'uploaded', 'uploaded shared file \"bell\"', '2026-10-17 16:01:13'),
(24, 1, 1, 7, 'uploaded', 'uploaded shared file \"bell\"', '2026-10-17 16:02:51'),
(25, 1, 1, 7, 'uploaded', 'uploaded shared file \"TeamBox_logo_black\"', '2026-10-17 16:12:02'),
(26, 1, 1, 6, 'created', 'created task \"Bell Interaction States\"', '2026-10-18 10:57:54'),
(27, 1, 1, 6, 'created', 'created task \"Notification Bell Icon\"', '2026-10-18 10:58:17'),
(28, 1, 1, 6, 'created', 'created task \"Notification Bell Icon\"', '2026-10-18 11:07:46'),
(29, 1, 1, 6, 'uploaded', 'uploaded bell.png to task \"Complete Project Proposal\"', '2026-10-18 11:10:27'),
(30, 1, 1, 6, 'uploaded', 'uploaded bell.png to task \"Complete Project Proposal\"', '2026-10-18 11:34:37'),
(31, 1, 1, 6, 'deleted', 'deleted task file \"bell.png\"', '2026-10-18 11:43:36'),
(32, 1, 1, 6, 'deleted', 'deleted task file \"bell.png\"', '2026-10-18 11:43:41'),
(33, 1, 1, 6, 'uploaded', 'uploaded bell (2).png to task \"Complete Project Proposal\"', '2026-10-18 12:00:54'),
(34, 1, 1, 6, 'deleted', 'deleted task file \"bell (2).png\"', '2026-10-18 12:01:00'),
(35, 1, 1, 6, 'uploaded', 'uploaded bell (2).png to task \"Complete Project Proposal\"', '2026-10-19 04:25:27'),
(36, 1, 1, 6, 'created', 'created task \"Bell Interaction States\"', '2026-10-19 04:35:08'),
(37, 1, 1, 7, 'uploaded', 'uploaded bell (2).png to task \"Bell Interaction States\"', '2026-10-19 04:35:49'),
(38, 1, 1, 7, 'uploaded', 'uploaded bell.png to task \"Bell Interaction States\"', '2026-10-19 04:37:43'),
(39, 1, 1, 7, 'uploaded', 'uploaded bell.png to task \"Notification Bell Icon\"', '2026-10-19 04:45:27');

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
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `announcements`
--

INSERT INTO `announcements` (`id`, `teacher_id`, `title`, `message`, `target_group_id`, `target_project_id`, `created_at`) VALUES
(1, 1, 'Progress Report Deadline', 'Please submit your progress report by 15 Oct.', NULL, 1, '2026-10-07 11:11:59'),
(2, 1, 'Welcome to FYP', 'Welcome everyone to the Final Year Project.', NULL, NULL, '2026-10-07 11:11:59'),
(3, 2, 'Mid-term Demo Schedule', 'Mid-term demo will be held on 20 Nov 2026.', NULL, 4, '2026-10-07 11:11:59'),
(4, 2, 'Campus Food Order Update', 'Please finalise the payment flow by end Oct.', NULL, 5, '2026-10-07 11:11:59'),
(5, 3, 'AI Study Buddy Reminder', 'Please submit the baseline model results.', NULL, 6, '2026-10-07 11:11:59'),
(6, 3, 'Green Campus Kickoff', 'Kickoff meeting on 15 Oct 2026.', NULL, 7, '2026-10-07 11:11:59'),
(7, 4, 'Career Path Finder', 'Dashboard design review next Monday.', NULL, 8, '2026-10-07 11:11:59'),
(8, 4, 'Virtual Lab Update', 'VR prototype draft due end of month.', NULL, 9, '2026-10-07 11:11:59'),
(9, 5, 'Campus Events Update', 'UI design must be done by mid Nov.', NULL, 10, '2026-10-07 11:11:59'),
(10, 5, 'Study Group Match', 'Voting for leader opens next week.', NULL, 11, '2026-10-07 11:11:59');

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
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `contributions`
--

INSERT INTO `contributions` (`id`, `group_id`, `student_id`, `title`, `type`, `notes`, `contribution_date`, `created_at`) VALUES
(2, 1, 2, 'Completed task: 21', 'Development', 'Auto-recorded when task \"Bell Interaction States\" was completed.', '2026-10-19', '2026-10-19 04:37:51'),
(3, 1, 2, 'Completed task: 18', 'Development', 'Auto-recorded when task \"Bell Interaction States\" was completed.', '2026-10-19', '2026-10-19 04:41:08'),
(4, 1, 2, 'Completed task: 20', 'Development', 'Auto-recorded when task \"Notification Bell Icon\" was confirmed by the assigner.', '2026-10-19', '2026-10-19 04:51:54');

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
(1, 1, 1, '2026-10-07 11:11:58'),
(2, 1, 2, '2026-10-07 11:11:58'),
(3, 1, 3, '2026-10-07 11:11:58'),
(4, 1, 4, '2026-10-07 11:11:58'),
(5, 1, 5, '2026-10-07 11:11:58'),
(6, 1, 6, '2026-10-07 11:11:58'),
(7, 1, 7, '2026-10-07 11:11:58'),
(8, 1, 8, '2026-10-07 11:11:58'),
(9, 1, 9, '2026-10-07 11:11:58'),
(10, 1, 10, '2026-10-07 11:11:58'),
(11, 1, 11, '2026-10-07 11:11:58'),
(12, 1, 12, '2026-10-07 11:11:58'),
(13, 1, 13, '2026-10-07 11:11:58'),
(14, 1, 14, '2026-10-07 11:11:58'),
(15, 1, 15, '2026-10-07 11:11:58'),
(16, 1, 16, '2026-10-07 11:11:58'),
(17, 1, 17, '2026-10-07 11:11:58'),
(18, 1, 18, '2026-10-07 11:11:58'),
(19, 1, 19, '2026-10-07 11:11:58'),
(20, 1, 20, '2026-10-07 11:11:58'),
(21, 1, 21, '2026-10-07 11:11:58'),
(22, 1, 22, '2026-10-07 11:11:58'),
(23, 1, 23, '2026-10-07 11:11:58'),
(24, 1, 24, '2026-10-07 11:11:58'),
(25, 1, 25, '2026-10-07 11:11:58');

-- --------------------------------------------------------

--
-- 資料表結構 `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `task_id` int(11) DEFAULT NULL,
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

INSERT INTO `files` (`id`, `project_id`, `group_id`, `task_id`, `uploader_id`, `file_name`, `display_name`, `notes`, `stored_name`, `file_size`, `file_type`, `status`, `uploaded_at`, `reviewed_by`, `reviewed_at`, `review_note`) VALUES
(1, 1, 1, NULL, 1, 'Proposal_v2.docx', NULL, NULL, NULL, '2.4 MB', 'DOCX', 'Unviewed', '2026-10-04 02:42:00', NULL, NULL, NULL),
(2, 1, 1, NULL, 3, 'Requirement_Analysis.pdf', NULL, NULL, NULL, '1.8 MB', 'PDF', 'Accepted', '2026-10-03 07:20:00', 1, '2026-10-04 09:00:00', 'Looks good.'),
(3, 1, 1, NULL, 4, 'UI_Wireframe.fig', NULL, NULL, NULL, '5.1 MB', 'FIG', 'Unviewed', '2026-10-02 01:15:00', NULL, NULL, NULL),
(4, 1, 1, NULL, 1, 'Sprint_Plan_Oct.xlsx', NULL, NULL, NULL, '180 KB', 'XLSX', 'Accepted', '2026-09-25 09:45:00', 1, '2026-09-26 09:00:00', 'OK.'),
(5, 1, 1, NULL, 1, 'Kickoff_Notes.docx', NULL, NULL, NULL, '120 KB', 'DOCX', 'Accepted', '2026-09-10 01:00:00', 1, '2026-09-10 12:00:00', 'OK.'),
(6, 1, 1, NULL, 2, 'Brainstorm.png', NULL, NULL, NULL, '780 KB', 'PNG', 'Accepted', '2026-09-12 06:00:00', 1, '2026-09-12 16:00:00', 'OK.'),
(7, 1, 2, NULL, 3, 'Database_Schema.sql', NULL, NULL, NULL, '120 KB', 'SQL', 'Accepted', '2026-10-01 03:00:00', 1, '2026-10-01 15:00:00', 'Approved.'),
(8, 1, 2, NULL, 2, 'API_Spec.pdf', NULL, NULL, NULL, '890 KB', 'PDF', 'Rejected', '2026-09-30 08:30:00', 1, '2026-10-01 10:00:00', 'Missing authentication section.'),
(9, 1, 2, NULL, 3, 'ER_Diagram.png', NULL, NULL, NULL, '1.1 MB', 'PNG', 'Accepted', '2026-09-15 02:00:00', 1, '2026-09-15 14:00:00', 'Nice.'),
(10, 2, 3, NULL, 6, 'Meeting_Minutes_2026-09-28.docx', NULL, NULL, NULL, '240 KB', 'DOCX', 'Accepted', '2026-09-28 06:00:00', 1, '2026-09-28 16:00:00', 'OK.'),
(11, 2, 3, NULL, 7, 'Budget_Plan.xlsx', NULL, NULL, NULL, '310 KB', 'XLSX', 'Rejected', '2026-09-26 02:00:00', 1, '2026-09-27 09:00:00', 'Numbers do not match the proposal.'),
(12, 2, 3, NULL, 8, 'Research_Notes.pdf', NULL, NULL, NULL, '1.1 MB', 'PDF', 'Unviewed', '2026-09-22 05:10:00', NULL, NULL, NULL),
(13, 4, 4, NULL, 8, 'SRS_v1.docx', NULL, NULL, NULL, '3.1 MB', 'DOCX', 'Accepted', '2026-10-05 01:30:00', 2, '2026-10-05 14:00:00', 'Looks good.'),
(14, 4, 4, NULL, 9, 'SeatMap_Mockup.png', NULL, NULL, NULL, '1.2 MB', 'PNG', 'Unviewed', '2026-10-06 03:15:00', NULL, NULL, NULL),
(15, 4, 4, NULL, 10, 'API_Design.pdf', NULL, NULL, NULL, '880 KB', 'PDF', 'Rejected', '2026-10-06 07:00:00', 2, '2026-10-07 09:00:00', 'Please add error handling.'),
(16, 5, 5, NULL, 9, 'Menu_Schema.sql', NULL, NULL, NULL, '95 KB', 'SQL', 'Accepted', '2026-10-02 02:00:00', 2, '2026-10-02 16:00:00', 'Approved.'),
(17, 5, 5, NULL, 12, 'Payment_Flow.pdf', NULL, NULL, NULL, '1.4 MB', 'PDF', 'Unviewed', '2026-10-05 00:45:00', NULL, NULL, NULL),
(18, 5, 5, NULL, 13, 'Order_Tracking.fig', NULL, NULL, NULL, '2.0 MB', 'FIG', 'Unviewed', '2026-10-06 01:20:00', NULL, NULL, NULL),
(19, 6, 6, NULL, 13, 'Dataset_Description.pdf', NULL, NULL, NULL, '1.5 MB', 'PDF', 'Accepted', '2026-09-30 06:00:00', 3, '2026-10-01 09:00:00', 'Good.'),
(20, 6, 6, NULL, 15, 'Baseline_Model.ipynb', NULL, NULL, NULL, '2.8 MB', 'IPYNB', 'Unviewed', '2026-10-06 08:40:00', NULL, NULL, NULL),
(21, 6, 6, NULL, 16, 'Prompt_Templates.txt', NULL, NULL, NULL, '45 KB', 'TXT', 'Unviewed', '2026-10-07 02:00:00', NULL, NULL, NULL),
(22, 8, 9, NULL, 20, 'Company_List.xlsx', NULL, NULL, NULL, '420 KB', 'XLSX', 'Accepted', '2026-10-05 03:00:00', 4, '2026-10-05 15:30:00', 'Verified.'),
(23, 8, 9, NULL, 21, 'Matching_Algo.pdf', NULL, NULL, NULL, '950 KB', 'PDF', 'Unviewed', '2026-10-07 05:00:00', NULL, NULL, NULL),
(24, 8, 9, NULL, 22, 'Dashboard_Wireframe.fig', NULL, NULL, NULL, '3.5 MB', 'FIG', 'Unviewed', '2026-10-07 06:30:00', NULL, NULL, NULL),
(31, 1, 1, NULL, 2, 'bell.png', NULL, NULL, 'shared1_1792252279_afd51251.png', '418.2 KB', 'PNG', 'Unviewed', '2026-10-17 15:51:19', NULL, NULL, NULL),
(32, 1, 1, NULL, 2, 'bell.png', 'bell', 'ABC', 'shared1_1792252873_0d29b20b.png', '418.2 KB', 'PNG', 'Unviewed', '2026-10-17 16:01:13', NULL, NULL, NULL),
(33, 1, 1, NULL, 2, 'bell.png', 'bell', NULL, 'shared1_1792252971_219da567.png', '418.2 KB', 'PNG', 'Unviewed', '2026-10-17 16:02:51', NULL, NULL, NULL),
(34, 1, 1, NULL, 2, 'TeamBox_logo_black.png', 'TeamBox_logo_black', NULL, 'shared1_1792253522_dd9eb8d2.png', '553.4 KB', 'PNG', 'Unviewed', '2026-10-17 16:12:02', NULL, NULL, NULL),
(37, 1, 1, 21, 2, 'bell.png', NULL, NULL, 'task21_1792384663_4e35bb69.png', '418.2 KB', 'PNG', 'Unviewed', '2026-10-19 04:37:43', NULL, NULL, NULL),
(38, 1, 1, 20, 2, 'bell.png', NULL, NULL, 'task20_1792385127_f5825b7a.png', '418.2 KB', 'PNG', 'Unviewed', '2026-10-19 04:45:27', NULL, NULL, NULL);

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
(1, 1, 1, 'Team 01', 1, 4, 4, 'Executing', '2026-09-15 10:00:00', '2026-10-07 11:11:58'),
(2, 1, 1, 'Team 02', 3, 3, 4, 'Voting', NULL, '2026-10-07 11:11:58'),
(3, 2, 1, 'Team 03', 5, 7, 4, 'Recruiting', NULL, '2026-10-07 11:11:58'),
(4, 4, 1, 'Team 04', 8, 11, 4, 'Executing', '2026-09-14 10:00:00', '2026-10-07 11:11:58'),
(5, 5, 1, 'Team 05', 9, 14, 4, 'Executing', '2026-09-15 11:30:00', '2026-10-07 11:11:58'),
(6, 6, 1, 'Team 06', 13, 17, 4, 'Executing', '2026-09-16 14:20:00', '2026-10-07 11:11:58'),
(7, 6, 1, 'Team 07', 17, 17, 4, 'Voting', NULL, '2026-10-07 11:11:58'),
(8, 7, 1, 'Team 08', 18, 18, 4, 'Recruiting', NULL, '2026-10-07 11:11:58'),
(9, 8, 1, 'Team 09', 20, 23, 4, 'Executing', '2026-09-17 09:00:00', '2026-10-07 11:11:58'),
(10, 9, 1, 'Team 10', 22, 22, 4, 'Recruiting', NULL, '2026-10-07 11:11:58');

-- --------------------------------------------------------

--
-- 資料表結構 `group_members`
--

CREATE TABLE `group_members` (
  `group_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `role` enum('leader','member') COLLATE utf8mb4_unicode_ci DEFAULT 'member',
  `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `group_members`
--

INSERT INTO `group_members` (`group_id`, `student_id`, `role`, `joined_at`) VALUES
(1, 1, 'leader', '2026-10-07 11:11:58'),
(1, 2, 'member', '2026-10-07 11:11:58'),
(1, 3, 'member', '2026-10-07 11:11:58'),
(1, 4, 'member', '2026-10-07 11:11:58'),
(2, 3, 'leader', '2026-10-07 11:11:58'),
(2, 6, 'member', '2026-10-07 11:11:58'),
(3, 5, 'leader', '2026-10-07 11:11:58'),
(3, 6, 'member', '2026-10-07 11:11:58'),
(3, 7, 'member', '2026-10-07 11:11:58'),
(4, 8, 'leader', '2026-10-07 11:11:58'),
(4, 9, 'member', '2026-10-07 11:11:58'),
(4, 10, 'member', '2026-10-07 11:11:58'),
(4, 11, 'member', '2026-10-07 11:11:58'),
(5, 9, 'leader', '2026-10-07 11:11:58'),
(5, 12, 'member', '2026-10-07 11:11:58'),
(5, 13, 'member', '2026-10-07 11:11:58'),
(5, 14, 'member', '2026-10-07 11:11:58'),
(6, 13, 'leader', '2026-10-07 11:11:58'),
(6, 15, 'member', '2026-10-07 11:11:58'),
(6, 16, 'member', '2026-10-07 11:11:58'),
(6, 17, 'member', '2026-10-07 11:11:58'),
(7, 17, 'leader', '2026-10-07 11:11:58'),
(7, 18, 'member', '2026-10-07 11:11:58'),
(8, 18, 'leader', '2026-10-07 11:11:58'),
(8, 19, 'member', '2026-10-07 11:11:58'),
(9, 20, 'leader', '2026-10-07 11:11:58'),
(9, 21, 'member', '2026-10-07 11:11:58'),
(9, 22, 'member', '2026-10-07 11:11:58'),
(9, 23, 'member', '2026-10-07 11:11:58'),
(10, 22, 'leader', '2026-10-07 11:11:58'),
(10, 23, 'member', '2026-10-07 11:11:58');

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
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `attachment_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `messages`
--

INSERT INTO `messages` (`id`, `group_id`, `sender_id`, `recipient_id`, `content`, `attachment_id`, `created_at`) VALUES
(1, 1, 1, NULL, 'Please update your assigned task progress before the team meeting.', NULL, '2026-10-07 11:11:59'),
(2, 1, 3, NULL, 'I have completed the student login page and I am currently developing the student dashboard.', NULL, '2026-10-07 11:11:59'),
(3, 1, 2, NULL, 'I have uploaded the latest project proposal to the shared files page.', NULL, '2026-10-07 11:11:59'),
(4, 1, 4, NULL, 'What time will the next team meeting begin?', NULL, '2026-10-07 11:11:59'),
(5, 1, 1, NULL, 'The meeting will begin at 3:30 PM today. Please prepare your current progress and questions.', NULL, '2026-10-07 11:11:59'),
(6, 4, 8, NULL, 'Hi team, let us start the SRS document today.', NULL, '2026-10-07 11:11:59'),
(7, 4, 9, NULL, 'OK. I will draft the introduction section.', NULL, '2026-10-07 11:11:59'),
(8, 4, 10, NULL, 'I will work on the functional requirements.', NULL, '2026-10-07 11:11:59'),
(9, 5, 9, NULL, 'Payment integration is tricky. Any ideas?', NULL, '2026-10-07 11:11:59'),
(10, 5, 12, NULL, 'Let us use Stripe test mode first.', NULL, '2026-10-07 11:11:59'),
(11, 5, 13, NULL, 'Agreed. I will set up a sandbox account.', NULL, '2026-10-07 11:11:59'),
(12, 6, 13, NULL, 'Baseline model accuracy is 78%.', NULL, '2026-10-07 11:11:59'),
(13, 6, 15, NULL, 'Can we try a bigger transformer model?', NULL, '2026-10-07 11:11:59'),
(14, 6, 16, NULL, 'Yes, I will test it this week.', NULL, '2026-10-07 11:11:59'),
(15, 9, 20, NULL, 'Company list is ready. Please review.', NULL, '2026-10-07 11:11:59'),
(16, 9, 21, NULL, 'Matching algorithm draft done. Testing now.', NULL, '2026-10-07 11:11:59'),
(17, 9, 22, NULL, 'Dashboard wireframe uploaded.', NULL, '2026-10-07 11:11:59'),
(28, 1, 1, NULL, 'hiiiiiii', NULL, '2026-10-17 15:35:35');

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
(1, 6, 1, 28, '2026-10-17 15:35:35'),
(4, 7, 1, 28, '2026-10-17 15:36:03');

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
(1, 1, 'Requirement Analysis', '2026-09-01', '2026-10-05', 'Completed', 100),
(2, 1, 'System Design', '2026-10-06', '2026-10-20', 'Completed', 100),
(3, 1, 'Core Development', '2026-10-21', '2027-01-15', 'In Progress', 55),
(4, 1, 'Testing & Documentation', '2027-01-16', '2027-03-20', 'Pending', 0),
(5, 1, 'Final Presentation', '2027-03-21', '2027-04-30', 'Pending', 0),
(6, 2, 'Requirement Analysis', '2026-09-01', '2026-10-10', 'Completed', 100),
(7, 2, 'System Design', '2026-10-11', '2026-10-31', 'In Progress', 60),
(8, 2, 'Development', '2026-11-01', '2027-02-15', 'Pending', 0),
(9, 2, 'Testing', '2027-02-16', '2027-03-31', 'Pending', 0),
(10, 2, 'Presentation', '2027-04-01', '2027-04-30', 'Pending', 0),
(11, 3, 'Requirement Analysis', '2026-09-01', '2026-10-20', 'Pending', 0),
(12, 3, 'AR Prototype', '2026-10-21', '2027-02-28', 'Pending', 0),
(13, 3, 'User Testing', '2027-03-01', '2027-04-10', 'Pending', 0),
(14, 4, 'Requirement Analysis', '2026-09-01', '2026-10-10', 'Completed', 100),
(15, 4, 'System Design', '2026-10-11', '2026-10-31', 'In Progress', 60),
(16, 4, 'Development', '2026-11-01', '2027-02-15', 'Pending', 0),
(17, 4, 'Testing', '2027-02-16', '2027-03-31', 'Pending', 0),
(18, 4, 'Presentation', '2027-04-01', '2027-04-30', 'Pending', 0),
(19, 5, 'Requirement Analysis', '2026-09-01', '2026-10-05', 'Completed', 100),
(20, 5, 'UI Design', '2026-10-06', '2026-10-25', 'In Progress', 40),
(21, 5, 'Development', '2026-10-26', '2027-02-28', 'Pending', 0),
(22, 5, 'Testing', '2027-03-01', '2027-04-10', 'Pending', 0),
(23, 6, 'Research', '2026-09-01', '2026-09-30', 'Completed', 100),
(24, 6, 'Model Training', '2026-10-01', '2027-01-10', 'In Progress', 55),
(25, 6, 'Evaluation', '2027-01-11', '2027-03-15', 'Pending', 0),
(26, 6, 'Thesis', '2027-03-16', '2027-04-30', 'Pending', 0),
(27, 7, 'Requirement Analysis', '2026-09-01', '2026-10-15', 'Pending', 0),
(28, 7, 'Hardware Setup', '2026-10-16', '2027-01-31', 'Pending', 0),
(29, 7, 'Data Collection', '2027-02-01', '2027-03-15', 'Pending', 0),
(30, 7, 'Final Presentation', '2027-03-16', '2027-04-30', 'Pending', 0),
(31, 8, 'Requirement Analysis', '2026-09-01', '2026-10-10', 'Completed', 100),
(32, 8, 'Database Design', '2026-10-11', '2026-11-10', 'In Progress', 50),
(33, 8, 'Frontend Dev', '2026-11-11', '2027-02-10', 'Pending', 0),
(34, 8, 'Backend Dev', '2026-11-11', '2027-02-10', 'Pending', 0),
(35, 8, 'Final Presentation', '2027-03-01', '2027-04-30', 'Pending', 0),
(36, 9, 'Research', '2026-09-01', '2026-10-20', 'Pending', 0),
(37, 9, 'VR Prototype', '2026-10-21', '2027-02-28', 'Pending', 0),
(38, 9, 'User Testing', '2027-03-01', '2027-04-10', 'Pending', 0),
(39, 10, 'Requirement Analysis', '2026-09-01', '2026-10-05', 'Completed', 100),
(40, 10, 'UI Design', '2026-10-06', '2026-11-15', 'In Progress', 65),
(41, 10, 'Development', '2026-11-16', '2027-02-28', 'Pending', 0),
(42, 10, 'Testing', '2027-03-01', '2027-04-10', 'Pending', 0),
(43, 11, 'Requirement Analysis', '2026-09-01', '2026-10-20', 'In Progress', 30),
(44, 11, 'System Design', '2026-10-21', '2026-11-30', 'Pending', 0),
(45, 11, 'Development', '2026-12-01', '2027-03-15', 'Pending', 0),
(46, 11, 'Testing', '2027-03-16', '2027-04-20', 'Pending', 0);

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
(1, 6, 'task', 'New Task Assigned', 'You have been assigned \"Develop Student Dashboard\".', 'student_task.php', 1, '2026-10-07 11:11:59'),
(2, 6, 'file', 'New File Uploaded', 'Student B uploaded \"Proposal_v2.docx\".', 'student_files.php', 1, '2026-10-07 11:11:59'),
(3, 6, 'team', 'New Group Message', 'A new meeting reminder was posted.', 'student_chat.php', 1, '2026-10-07 11:11:59'),
(4, 13, 'task', 'New Task Assigned', 'You have been assigned \"Design Seat Map UI\".', 'student_task.php', 0, '2026-10-07 11:11:59'),
(5, 14, 'file', 'File Approved', 'Your \"Menu_Schema.sql\" was approved.', 'student_files.php', 0, '2026-10-07 11:11:59'),
(6, 15, 'file', 'File Rejected', 'Your \"API_Design.pdf\" was rejected.', 'student_files.php', 0, '2026-10-07 11:11:59'),
(7, 18, 'task', 'Task In Progress', 'Your task \"Train Baseline Model\" is ongoing.', 'student_task.php', 0, '2026-10-07 11:11:59'),
(8, 20, 'team', 'New Group Message', 'A new message was posted in Team 06.', 'student_chat.php', 0, '2026-10-07 11:11:59'),
(9, 25, 'file', 'File Approved', 'Your \"Company_List.xlsx\" was approved.', 'student_files.php', 0, '2026-10-07 11:11:59'),
(10, 26, 'task', 'New Task Assigned', 'You have been assigned \"Matching Algorithm\".', 'student_task.php', 0, '2026-10-07 11:11:59'),
(11, 27, 'team', 'New Group Message', 'A new message was posted in Team 09.', 'student_chat.php', 0, '2026-10-07 11:11:59'),
(12, 7, 'task', 'New Task Assigned', 'You have been assigned \"Bell Interaction States\".', 'student_task.php?group_id=1', 0, '2026-10-18 10:57:54'),
(13, 7, 'task', 'New Task Assigned', 'You have been assigned \"Notification Bell Icon\".', 'student_task.php?group_id=1', 0, '2026-10-18 10:58:17'),
(14, 7, 'task', 'New Task Assigned', 'You have been assigned \"Notification Bell Icon\".', 'student_task.php?group_id=1', 0, '2026-10-18 11:07:46'),
(15, 7, 'task', 'New Task Assigned', 'You have been assigned \"Bell Interaction States\".', 'student_task.php?group_id=1', 0, '2026-10-19 04:35:08'),
(16, 6, 'task', 'Task Completed - Needs Confirmation', 'The assignee marked \"Notification Bell Icon\" as completed. Please confirm.', 'student_task.php?group_id=1', 0, '2026-10-19 04:51:32'),
(17, 7, 'task', 'Task Confirmed', 'Your task \"Notification Bell Icon\" has been confirmed. +10 contribution points.', 'student_task.php?group_id=1', 0, '2026-10-19 04:51:54');

-- --------------------------------------------------------

--
-- 資料表結構 `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `supervisor_id` int(11) NOT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `status` enum('Recruiting','Waiting_Send','Voting','Executing','Completed','Archived') COLLATE utf8mb4_unicode_ci DEFAULT 'Recruiting',
  `progress` int(11) DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `projects`
--

INSERT INTO `projects` (`id`, `name`, `category`, `course_id`, `supervisor_id`, `period_start`, `period_end`, `status`, `progress`, `description`) VALUES
(1, 'TeamBox', 'Software Engineering', 1, 1, '2026-09-01', '2027-04-30', 'Executing', 60, 'Cross-platform collaboration and management system for student teams.'),
(2, 'Smart Attendance', 'Mobile App', 1, 1, '2026-09-01', '2027-04-30', 'Executing', 35, 'Face recognition based attendance system.'),
(3, 'Campus Map AR', 'AR / VR', 1, 1, '2026-09-01', '2027-04-30', 'Recruiting', 10, 'AR navigation for campus buildings.'),
(4, 'Smart Library', 'Web App', 1, 2, '2026-09-01', '2027-04-30', 'Executing', 40, 'Library seat booking and recommendation system.'),
(5, 'Campus Food Order', 'Mobile App', 1, 2, '2026-09-01', '2027-04-30', 'Executing', 25, 'Mobile app to order food from campus canteens.'),
(6, 'AI Study Buddy', 'Machine Learning', 1, 3, '2026-09-01', '2027-04-30', 'Executing', 55, 'AI assistant for personalised study plans.'),
(7, 'Green Campus', 'IoT', 1, 3, '2026-09-01', '2027-04-30', 'Recruiting', 10, 'IoT energy monitoring for campus buildings.'),
(8, 'Career Path Finder', 'Web App', 1, 4, '2026-09-01', '2027-04-30', 'Executing', 30, 'Web platform to match students with internships.'),
(9, 'Virtual Lab', 'VR', 1, 4, '2026-09-01', '2027-04-30', 'Recruiting', 5, 'VR-based virtual laboratory for science students.'),
(10, 'Campus Events', 'Mobile App', 1, 5, '2026-09-01', '2027-04-30', 'Executing', 45, 'Mobile app to discover and manage campus events.'),
(11, 'Study Group Match', 'Web App', 1, 5, '2026-09-01', '2027-04-30', 'Voting', 15, 'Platform to match students into study groups.');

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
  `grade_level` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `students`
--

INSERT INTO `students` (`id`, `user_id`, `student_number`, `major`, `grade_level`) VALUES
(1, 6, 'S48291537', 'Software Engineering', 14),
(2, 7, 'S15942736', 'Software Engineering', 14),
(3, 8, 'S73658120', 'Software Engineering', 14),
(4, 9, 'S20473819', 'Software Engineering', 14),
(5, 10, 'S91826504', 'Software Engineering', 14),
(6, 11, 'S35718492', 'Software Engineering', 14),
(7, 12, 'S62048571', 'Software Engineering', 14),
(8, 13, 'S17395028', 'Software Engineering', 14),
(9, 14, 'S84926315', 'Software Engineering', 14),
(10, 15, 'S50274186', 'Software Engineering', 14),
(11, 16, 'S31486972', 'Information Technology', 14),
(12, 17, 'S76592043', 'Software Engineering', 14),
(13, 18, 'S09851426', 'Cloud Computing', 14),
(14, 19, 'S47203581', 'Software Engineering', 14),
(15, 20, 'S61573924', 'Information Technology', 14),
(16, 21, 'S28639174', 'Software Engineering', 14),
(17, 22, 'S93051768', 'Cloud Computing', 14),
(18, 23, 'S34718059', 'Software Engineering', 14),
(19, 24, 'S80245391', 'Information Technology', 14),
(20, 25, 'S15972806', 'Software Engineering', 14),
(21, 26, 'S62048913', 'Software Engineering', 14),
(22, 27, 'S47123895', 'Cloud Computing', 14),
(23, 28, 'S90471625', 'Software Engineering', 14),
(24, 29, 'S23850147', 'Information Technology', 14),
(25, 30, 'S76093184', 'Software Engineering', 14);

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
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表的匯出資料 `tasks`
--

INSERT INTO `tasks` (`id`, `project_id`, `group_id`, `title`, `description`, `assigned_to`, `created_by`, `status`, `priority`, `due_date`, `completed_at`, `created_at`) VALUES
(1, 1, 1, 'Complete Project Proposal', 'Draft and submit the FYP proposal.', 1, 6, 'Completed', 'High', '2026-09-30', NULL, '2026-10-07 11:11:59'),
(2, 1, 1, 'Requirement Analysis', 'Analyse requirements and produce document.', 3, 6, 'Completed', 'Medium', '2026-10-05', NULL, '2026-10-07 11:11:59'),
(3, 1, 1, 'Create System Interface', 'Design the main UI screens.', 4, 6, 'In Progress', 'High', '2026-10-10', NULL, '2026-10-07 11:11:59'),
(4, 1, 1, 'Database Schema Design', 'Design the MySQL schema for TeamBox.', 3, 6, 'Pending', 'Medium', '2026-10-15', NULL, '2026-10-07 11:11:59'),
(5, 1, 2, 'Android Prototype', 'Build a first Android prototype.', 2, 8, 'Pending', 'High', '2026-10-25', NULL, '2026-10-07 11:11:59'),
(6, 4, 4, 'Write SRS Document', 'Software requirement spec.', 8, 13, 'Completed', 'High', '2026-10-05', NULL, '2026-10-07 11:11:59'),
(7, 4, 4, 'Design Seat Map UI', 'Interactive seat map design.', 9, 13, 'In Progress', 'Medium', '2026-10-15', NULL, '2026-10-07 11:11:59'),
(8, 4, 4, 'Booking API', 'Implement booking API.', 10, 13, 'Pending', 'High', '2026-10-25', NULL, '2026-10-07 11:11:59'),
(9, 5, 5, 'Menu Database', 'Create menu database schema.', 9, 14, 'Completed', 'Medium', '2026-10-01', NULL, '2026-10-07 11:11:59'),
(10, 5, 5, 'Payment Integration', 'Integrate payment gateway.', 12, 14, 'In Progress', 'High', '2026-10-20', NULL, '2026-10-07 11:11:59'),
(11, 5, 5, 'Order Tracking UI', 'Design order tracking screen.', 13, 14, 'Pending', 'Low', '2026-10-30', NULL, '2026-10-07 11:11:59'),
(12, 6, 6, 'Data Collection', 'Collect study material dataset.', 13, 18, 'Completed', 'High', '2026-09-30', NULL, '2026-10-07 11:11:59'),
(13, 6, 6, 'Train Baseline Model', 'Train first ML baseline.', 15, 18, 'In Progress', 'High', '2026-10-25', NULL, '2026-10-07 11:11:59'),
(14, 6, 6, 'Prompt Engineering', 'Design prompt templates.', 16, 18, 'Pending', 'Medium', '2026-11-10', NULL, '2026-10-07 11:11:59'),
(15, 8, 9, 'Company Database', 'Collect company data.', 20, 22, 'Completed', 'Medium', '2026-10-05', NULL, '2026-10-07 11:11:59'),
(16, 8, 9, 'Matching Algorithm', 'Build matching algorithm.', 21, 22, 'In Progress', 'High', '2026-10-28', NULL, '2026-10-07 11:11:59'),
(17, 8, 9, 'Frontend Dashboard', 'Build student dashboard.', 22, 22, 'Pending', 'Medium', '2026-11-15', NULL, '2026-10-07 11:11:59'),
(18, 1, 1, 'ABC', 'XFGFHJHK', 2, 6, 'Completed', 'High', '2026-10-22', NULL, '2026-10-18 10:57:54'),
(19, 1, 1, 'Notification Bell Research', 'Review existing notification patterns and document the icon requirements for TeamBox.', 2, 6, 'Pending', 'Low', '2026-10-27', NULL, '2026-10-18 10:58:17'),
(20, 1, 1, 'Notification Bell Icon', 'Design the notification bell icon used in the teacher and student headers.', 2, 6, 'Completed', 'Low', '2026-10-27', '2026-10-19 12:51:54', '2026-10-18 11:07:46'),
(21, 1, 1, 'Bell Interaction States', 'Prepare hover and unread states for the notification bell and upload the final assets.', 2, 6, 'Completed', 'Medium', '2026-10-22', NULL, '2026-10-19 04:35:08');

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
(1, 1, '2026-10-07 11:11:58'),
(1, 2, '2026-10-07 11:11:58'),
(1, 3, '2026-10-07 11:11:58'),
(1, 4, '2026-10-07 11:11:58'),
(1, 5, '2026-10-07 11:11:58'),
(1, 6, '2026-10-07 11:11:58'),
(1, 7, '2026-10-07 11:11:58'),
(1, 8, '2026-10-07 11:11:58'),
(1, 9, '2026-10-07 11:11:58'),
(1, 10, '2026-10-07 11:11:58'),
(1, 11, '2026-10-07 11:11:58'),
(1, 12, '2026-10-07 11:11:58'),
(2, 13, '2026-10-07 11:11:58'),
(2, 14, '2026-10-07 11:11:58'),
(2, 15, '2026-10-07 11:11:58'),
(2, 16, '2026-10-07 11:11:58'),
(2, 17, '2026-10-07 11:11:58'),
(3, 18, '2026-10-07 11:11:58'),
(3, 19, '2026-10-07 11:11:58'),
(3, 20, '2026-10-07 11:11:58'),
(3, 21, '2026-10-07 11:11:58'),
(4, 22, '2026-10-07 11:11:58'),
(4, 23, '2026-10-07 11:11:58'),
(4, 24, '2026-10-07 11:11:58'),
(5, 25, '2026-10-07 11:11:58');

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
(1, 'Esther Yuen', 'wyyuen@vtc.edu.hk', '12345', 'teacher', NULL, '2026-10-07 11:11:58'),
(2, 'Dr. Alan Ho', 'alanho@vtc.edu.hk', '12345', 'teacher', NULL, '2026-10-07 11:11:58'),
(3, 'Prof. Rebecca Lam', 'rebeccalam@vtc.edu.hk', '12345', 'teacher', NULL, '2026-10-07 11:11:58'),
(4, 'Dr. Vincent Ng', 'vincentng@vtc.edu.hk', '12345', 'teacher', NULL, '2026-10-07 11:11:58'),
(5, 'Ms. Karen Tsang', 'karentsang@vtc.edu.hk', '12345', 'teacher', NULL, '2026-10-07 11:11:58'),
(6, 'CHAN Ho Yin', '260222111@stu.vtc.edu.hk', '12345', 'student', '2026-10-19 12:51:50', '2026-10-07 11:11:58'),
(7, 'CHUI Ka Chun', '260222112@stu.vtc.edu.hk', '12345', 'student', '2026-10-19 12:52:27', '2026-10-07 11:11:58'),
(8, 'LAI Ka-Yu Savio', '260222113@stu.vtc.edu.hk', '12345', 'student', NULL, '2026-10-07 11:11:58'),
(9, 'MIAO Tung', '260222114@stu.vtc.edu.hk', '12345', 'student', NULL, '2026-10-07 11:11:58'),
(10, 'WONG Mei Ling', '260222115@stu.vtc.edu.hk', '12345', 'student', NULL, '2026-10-07 11:11:58'),
(11, 'LEE Chun Kit', '260222116@stu.vtc.edu.hk', '12345', 'student', NULL, '2026-10-07 11:11:58'),
(12, 'NG Wing Yan', '260222117@stu.vtc.edu.hk', '12345', 'student', NULL, '2026-10-07 11:11:58'),
(13, 'TAM Chi Hang', '260222201@stu.vtc.edu.hk', '12345', 'student', NULL, '2026-10-07 11:11:58'),
(14, 'HO Wing Sze', '260222202@stu.vtc.edu.hk', '12345', 'student', NULL, '2026-10-07 11:11:58'),
(15, 'LAM Ka Ho', '260222203@stu.vtc.edu.hk', '12345', 'student', NULL, '2026-10-07 11:11:58'),
(16, 'YIP Man Yee', '260222204@stu.vtc.edu.hk', '12345', 'student', NULL, '2026-10-07 11:11:58'),
(17, 'SO Chun Ming', '260222205@stu.vtc.edu.hk', '12345', 'student', NULL, '2026-10-07 11:11:58'),
(18, 'CHEUNG Ka Yan', '260222206@stu.vtc.edu.hk', '12345', 'student', NULL, '2026-10-07 11:11:58'),
(19, 'CHU Ho Yin', '260222207@stu.vtc.edu.hk', '12345', 'student', NULL, '2026-10-07 11:11:58'),
(20, 'FUNG Wing Chi', '260222208@stu.vtc.edu.hk', '12345', 'student', NULL, '2026-10-07 11:11:58'),
(21, 'KWOK Chi Wai', '260222209@stu.vtc.edu.hk', '12345', 'student', NULL, '2026-10-07 11:11:58'),
(22, 'LEUNG Mei Mei', '260222210@stu.vtc.edu.hk', '12345', 'student', NULL, '2026-10-07 11:11:58'),
(23, 'MAK Ho Cheung', '260222211@stu.vtc.edu.hk', '12345', 'student', NULL, '2026-10-07 11:11:58'),
(24, 'NGAI Ka Wai', '260222212@stu.vtc.edu.hk', '12345', 'student', NULL, '2026-10-07 11:11:58'),
(25, 'POON Yuen Ting', '260222213@stu.vtc.edu.hk', '12345', 'student', NULL, '2026-10-07 11:11:58'),
(26, 'SIU Chun Hei', '260222214@stu.vtc.edu.hk', '12345', 'student', NULL, '2026-10-07 11:11:58'),
(27, 'TSE Wing Lam', '260222215@stu.vtc.edu.hk', '12345', 'student', NULL, '2026-10-07 11:11:58'),
(28, 'WU Ka Man', '260222216@stu.vtc.edu.hk', '12345', 'student', NULL, '2026-10-07 11:11:58'),
(29, 'YIU Ho Kwan', '260222217@stu.vtc.edu.hk', '12345', 'student', NULL, '2026-10-07 11:11:58'),
(30, 'ZHAO Wei', '260222218@stu.vtc.edu.hk', '12345', 'student', '2026-10-18 18:14:13', '2026-10-07 11:11:58');

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
-- 資料表索引 `contributions`
--
ALTER TABLE `contributions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `student_id` (`student_id`);

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
  ADD KEY `task_id` (`task_id`);

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
  ADD KEY `recipient_id` (`recipient_id`);

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
  ADD KEY `course_id` (`course_id`);

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
  ADD UNIQUE KEY `student_number` (`student_number`);

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
  ADD KEY `student_id` (`student_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- 使用資料表 AUTO_INCREMENT `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- 使用資料表 AUTO_INCREMENT `announcement_targets`
--
ALTER TABLE `announcement_targets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表 AUTO_INCREMENT `contributions`
--
ALTER TABLE `contributions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用資料表 AUTO_INCREMENT `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用資料表 AUTO_INCREMENT `course_enrollments`
--
ALTER TABLE `course_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- 使用資料表 AUTO_INCREMENT `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- 使用資料表 AUTO_INCREMENT `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- 使用資料表 AUTO_INCREMENT `group_requests`
--
ALTER TABLE `group_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- 使用資料表 AUTO_INCREMENT `message_reads`
--
ALTER TABLE `message_reads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用資料表 AUTO_INCREMENT `milestones`
--
ALTER TABLE `milestones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- 使用資料表 AUTO_INCREMENT `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- 使用資料表 AUTO_INCREMENT `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- 使用資料表 AUTO_INCREMENT `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- 使用資料表 AUTO_INCREMENT `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- 使用資料表 AUTO_INCREMENT `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用資料表 AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

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
-- 資料表的 Constraints `contributions`
--
ALTER TABLE `contributions`
  ADD CONSTRAINT `contributions_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contributions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `files_ibfk_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL;

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
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`supervisor_id`) REFERENCES `teachers` (`id`),
  ADD CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- =========================================================
-- Project creation module (added 2026-10-20)
-- =========================================================
ALTER TABLE `projects`
    ADD COLUMN `requirements` TEXT NULL AFTER `description`,
    ADD COLUMN `team_mode` ENUM('Free','Random','Teacher_Assigned') NOT NULL DEFAULT 'Free',
    ADD COLUMN `team_deadline` DATE NULL,
    ADD COLUMN `allow_cross_class` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN `default_max_members` INT NOT NULL DEFAULT 4,
    ADD COLUMN `leader_permissions` TEXT NULL,
    ADD COLUMN `post_deadline_action` ENUM('Lock','Notify') NOT NULL DEFAULT 'Lock',
    ADD COLUMN `visibility` ENUM('All','Course') NOT NULL DEFAULT 'Course',
    ADD COLUMN `reminder_days` INT NOT NULL DEFAULT 3,
    ADD COLUMN `allow_extension` TINYINT(1) NOT NULL DEFAULT 1,
    ADD COLUMN `is_draft` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN `last_reminder_sent` DATE NULL,
    ADD COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD KEY `idx_projects_draft` (`is_draft`);

ALTER TABLE `announcements`
    ADD COLUMN `channels` VARCHAR(50) NOT NULL DEFAULT 'inapp',
    ADD COLUMN `status` ENUM('Draft','Scheduled','Sent') NOT NULL DEFAULT 'Sent',
    ADD COLUMN `scheduled_at` DATETIME NULL,
    ADD COLUMN `sent_at` DATETIME NULL,
    ADD COLUMN `email_sent_count` INT NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS `project_attachments` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `project_id` INT(11) NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `display_name` VARCHAR(255) DEFAULT NULL,
    `stored_name` VARCHAR(255) NOT NULL,
    `file_size` VARCHAR(30) DEFAULT NULL,
    `file_type` VARCHAR(100) DEFAULT NULL,
    `uploaded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `project_id` (`project_id`),
    CONSTRAINT `project_attachments_ibfk_1`
        FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `project_extension_requests` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `project_id` INT(11) NOT NULL,
    `student_id` INT(11) NOT NULL,
    `requested_end` DATE NOT NULL,
    `reason` TEXT DEFAULT NULL,
    `status` ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    `reviewed_by` INT(11) DEFAULT NULL,
    `reviewed_at` DATETIME DEFAULT NULL,
    `review_note` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `project_id` (`project_id`),
    KEY `student_id` (`student_id`),
    KEY `reviewed_by` (`reviewed_by`),
    CONSTRAINT `project_ext_req_ibfk_1`
        FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
    CONSTRAINT `project_ext_req_ibfk_2`
        FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
    CONSTRAINT `project_ext_req_ibfk_3`
        FOREIGN KEY (`reviewed_by`) REFERENCES `teachers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =========================================================
-- Teacher group chat (added 2026-10-20)
-- =========================================================
ALTER TABLE `messages`
    MODIFY `sender_id` INT(11) NULL,
    ADD COLUMN `sender_user_id` INT(11) NULL AFTER `sender_id`,
    ADD KEY `idx_messages_sender_user` (`sender_user_id`),
    ADD CONSTRAINT `fk_messages_sender_user`
        FOREIGN KEY (`sender_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;


-- =========================================================
-- Pinned group announcements (added 2026-10-20)
-- =========================================================
ALTER TABLE `messages`
    ADD COLUMN `is_pinned` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN `pinned_at` DATETIME NULL,
    ADD COLUMN `pinned_by_user_id` INT(11) NULL,
    ADD KEY `idx_messages_pinned` (`group_id`, `is_pinned`),
    ADD CONSTRAINT `fk_messages_pinned_by`
        FOREIGN KEY (`pinned_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;


-- =========================================================
-- Demo group requests pending teacher approval
-- =========================================================
INSERT INTO `group_requests` (`group_name`, `project_id`, `submitted_by`, `status`) VALUES
('Team 02', 1, 3, 'Pending'),
('Team 03', 2, 5, 'Pending');

-- Team 03 has submitted its formation request and waits for approval.
UPDATE `groups` SET `status` = 'Waiting_Send' WHERE `id` = 3 AND `status` = 'Recruiting';

-- Members snapshot for the pending requests.
INSERT INTO `request_members` (`request_id`, `student_id`, `is_leader`)
SELECT r.id, x.student_id, x.is_leader
FROM `group_requests` r
INNER JOIN (
    SELECT 'Team 02' AS group_name, 1 AS project_id, 3 AS student_id, 1 AS is_leader
    UNION ALL SELECT 'Team 02', 1, 6, 0
    UNION ALL SELECT 'Team 03', 2, 5, 1
    UNION ALL SELECT 'Team 03', 2, 6, 0
    UNION ALL SELECT 'Team 03', 2, 7, 0
) x ON x.group_name = r.group_name AND x.project_id = r.project_id
WHERE r.status = 'Pending'
ON DUPLICATE KEY UPDATE `is_leader` = VALUES(`is_leader`);
