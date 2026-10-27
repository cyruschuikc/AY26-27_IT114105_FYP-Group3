-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: teambox
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activities`
--

DROP TABLE IF EXISTS `activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `actor_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `detail` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `group_id` (`group_id`),
  KEY `actor_id` (`actor_id`),
  CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activities_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activities_ibfk_3` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=297 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activities`
--

LOCK TABLES `activities` WRITE;
/*!40000 ALTER TABLE `activities` DISABLE KEYS */;
INSERT INTO `activities` VALUES (169,1,1,187,'joined_team','SE-1A · Team 01','2026-10-20 13:29:03'),(170,1,1,188,'joined_team','SE-1A · Team 01','2026-10-20 13:29:03'),(171,1,1,189,'joined_team','SE-1A · Team 01','2026-10-20 13:29:03'),(172,1,1,190,'joined_team','SE-1A · Team 01','2026-10-20 13:29:03'),(173,1,2,191,'joined_team','SE-1A · Team 02','2026-10-20 13:29:03'),(174,1,2,192,'joined_team','SE-1A · Team 02','2026-10-20 13:29:03'),(175,1,2,193,'joined_team','SE-1A · Team 02','2026-10-20 13:29:03'),(176,1,2,194,'joined_team','SE-1A · Team 02','2026-10-20 13:29:03'),(177,1,3,195,'joined_team','SE-1A · Team 03','2026-10-20 13:29:03'),(178,1,3,196,'joined_team','SE-1A · Team 03','2026-10-20 13:29:03'),(179,1,3,197,'joined_team','SE-1A · Team 03','2026-10-20 13:29:03'),(180,1,3,198,'joined_team','SE-1A · Team 03','2026-10-20 13:29:03'),(181,1,4,199,'joined_team','SE-1A · Team 04','2026-10-20 13:29:03'),(182,1,4,200,'joined_team','SE-1A · Team 04','2026-10-20 13:29:03'),(183,1,4,201,'joined_team','SE-1A · Team 04','2026-10-20 13:29:03'),(184,1,4,202,'joined_team','SE-1A · Team 04','2026-10-20 13:29:03'),(185,2,5,6,'joined_team','SE-2B · Team 05','2026-10-20 13:29:03'),(186,2,5,7,'joined_team','SE-2B · Team 05','2026-10-20 13:29:03'),(187,2,5,8,'joined_team','SE-2B · Team 05','2026-10-20 13:29:03'),(188,2,5,9,'joined_team','SE-2B · Team 05','2026-10-20 13:29:03'),(189,2,6,247,'joined_team','SE-2B · Team 06','2026-10-20 13:29:03'),(190,2,6,248,'joined_team','SE-2B · Team 06','2026-10-20 13:29:03'),(191,2,6,249,'joined_team','SE-2B · Team 06','2026-10-20 13:29:03'),(192,2,6,250,'joined_team','SE-2B · Team 06','2026-10-20 13:29:03'),(193,2,7,251,'joined_team','SE-2B · Team 07','2026-10-20 13:29:03'),(194,2,7,252,'joined_team','SE-2B · Team 07','2026-10-20 13:29:03'),(195,2,7,253,'joined_team','SE-2B · Team 07','2026-10-20 13:29:03'),(196,2,7,254,'joined_team','SE-2B · Team 07','2026-10-20 13:29:03'),(197,2,8,255,'joined_team','SE-2B · Team 08','2026-10-20 13:29:03'),(198,2,8,256,'joined_team','SE-2B · Team 08','2026-10-20 13:29:03'),(199,2,8,257,'joined_team','SE-2B · Team 08','2026-10-20 13:29:03'),(200,2,8,258,'joined_team','SE-2B · Team 08','2026-10-20 13:29:03'),(201,3,9,263,'joined_team','CY-1A · Team 09','2026-10-20 13:29:03'),(202,3,9,264,'joined_team','CY-1A · Team 09','2026-10-20 13:29:03'),(203,3,9,265,'joined_team','CY-1A · Team 09','2026-10-20 13:29:03'),(204,3,9,266,'joined_team','CY-1A · Team 09','2026-10-20 13:29:03'),(205,3,10,267,'joined_team','CY-1A · Team 10','2026-10-20 13:29:03'),(206,3,10,268,'joined_team','CY-1A · Team 10','2026-10-20 13:29:03'),(207,3,10,269,'joined_team','CY-1A · Team 10','2026-10-20 13:29:03'),(208,3,10,270,'joined_team','CY-1A · Team 10','2026-10-20 13:29:03'),(209,3,11,271,'joined_team','CY-1A · Team 11','2026-10-20 13:29:03'),(210,3,11,272,'joined_team','CY-1A · Team 11','2026-10-20 13:29:03'),(211,3,11,273,'joined_team','CY-1A · Team 11','2026-10-20 13:29:03'),(212,3,11,274,'joined_team','CY-1A · Team 11','2026-10-20 13:29:03'),(213,3,12,275,'joined_team','CY-1A · Team 12','2026-10-20 13:29:03'),(214,3,12,276,'joined_team','CY-1A · Team 12','2026-10-20 13:29:03'),(215,3,12,277,'joined_team','CY-1A · Team 12','2026-10-20 13:29:03'),(216,3,12,278,'joined_team','CY-1A · Team 12','2026-10-20 13:29:03'),(217,4,13,207,'joined_team','SE-1B · Team 13','2026-10-20 13:29:03'),(218,4,13,208,'joined_team','SE-1B · Team 13','2026-10-20 13:29:03'),(219,4,13,209,'joined_team','SE-1B · Team 13','2026-10-20 13:29:03'),(220,4,13,210,'joined_team','SE-1B · Team 13','2026-10-20 13:29:03'),(221,4,14,211,'joined_team','SE-1B · Team 14','2026-10-20 13:29:03'),(222,4,14,212,'joined_team','SE-1B · Team 14','2026-10-20 13:29:03'),(223,4,14,213,'joined_team','SE-1B · Team 14','2026-10-20 13:29:03'),(224,4,14,214,'joined_team','SE-1B · Team 14','2026-10-20 13:29:03'),(225,4,15,215,'joined_team','SE-1B · Team 15','2026-10-20 13:29:03'),(226,4,15,216,'joined_team','SE-1B · Team 15','2026-10-20 13:29:03'),(227,4,15,217,'joined_team','SE-1B · Team 15','2026-10-20 13:29:03'),(228,4,15,218,'joined_team','SE-1B · Team 15','2026-10-20 13:29:03'),(229,4,16,219,'joined_team','SE-1B · Team 16','2026-10-20 13:29:03'),(230,4,16,220,'joined_team','SE-1B · Team 16','2026-10-20 13:29:03'),(231,4,16,221,'joined_team','SE-1B · Team 16','2026-10-20 13:29:03'),(232,4,16,222,'joined_team','SE-1B · Team 16','2026-10-20 13:29:03'),(233,5,17,283,'joined_team','CY-1B · Team 17','2026-10-20 13:29:03'),(234,5,17,284,'joined_team','CY-1B · Team 17','2026-10-20 13:29:03'),(235,5,17,285,'joined_team','CY-1B · Team 17','2026-10-20 13:29:03'),(236,5,17,286,'joined_team','CY-1B · Team 17','2026-10-20 13:29:03'),(237,5,18,287,'joined_team','CY-1B · Team 18','2026-10-20 13:29:03'),(238,5,18,288,'joined_team','CY-1B · Team 18','2026-10-20 13:29:03'),(239,5,18,289,'joined_team','CY-1B · Team 18','2026-10-20 13:29:03'),(240,5,18,290,'joined_team','CY-1B · Team 18','2026-10-20 13:29:03'),(241,5,19,291,'joined_team','CY-1B · Team 19','2026-10-20 13:29:03'),(242,5,19,292,'joined_team','CY-1B · Team 19','2026-10-20 13:29:03'),(243,5,19,293,'joined_team','CY-1B · Team 19','2026-10-20 13:29:03'),(244,5,19,294,'joined_team','CY-1B · Team 19','2026-10-20 13:29:03'),(245,5,20,295,'joined_team','CY-1B · Team 20','2026-10-20 13:29:03'),(246,5,20,296,'joined_team','CY-1B · Team 20','2026-10-20 13:29:03'),(247,5,20,297,'joined_team','CY-1B · Team 20','2026-10-20 13:29:03'),(248,5,20,298,'joined_team','CY-1B · Team 20','2026-10-20 13:29:03'),(249,6,21,227,'joined_team','SE-2A · Team 21','2026-10-20 13:29:03'),(250,6,21,228,'joined_team','SE-2A · Team 21','2026-10-20 13:29:03'),(251,6,21,229,'joined_team','SE-2A · Team 21','2026-10-20 13:29:03'),(252,6,21,230,'joined_team','SE-2A · Team 21','2026-10-20 13:29:03'),(253,6,22,231,'joined_team','SE-2A · Team 22','2026-10-20 13:29:03'),(254,6,22,232,'joined_team','SE-2A · Team 22','2026-10-20 13:29:03'),(255,6,22,233,'joined_team','SE-2A · Team 22','2026-10-20 13:29:03'),(256,6,22,234,'joined_team','SE-2A · Team 22','2026-10-20 13:29:03'),(257,6,23,235,'joined_team','SE-2A · Team 23','2026-10-20 13:29:03'),(258,6,23,236,'joined_team','SE-2A · Team 23','2026-10-20 13:29:03'),(259,6,23,237,'joined_team','SE-2A · Team 23','2026-10-20 13:29:03'),(260,6,23,238,'joined_team','SE-2A · Team 23','2026-10-20 13:29:03'),(261,6,24,239,'joined_team','SE-2A · Team 24','2026-10-20 13:29:03'),(262,6,24,240,'joined_team','SE-2A · Team 24','2026-10-20 13:29:03'),(263,6,24,241,'joined_team','SE-2A · Team 24','2026-10-20 13:29:03'),(264,6,24,242,'joined_team','SE-2A · Team 24','2026-10-20 13:29:03'),(265,7,25,303,'joined_team','CY-2A · Team 25','2026-10-20 13:29:03'),(266,7,25,304,'joined_team','CY-2A · Team 25','2026-10-20 13:29:03'),(267,7,25,305,'joined_team','CY-2A · Team 25','2026-10-20 13:29:03'),(268,7,25,306,'joined_team','CY-2A · Team 25','2026-10-20 13:29:03'),(269,7,26,307,'joined_team','CY-2A · Team 26','2026-10-20 13:29:03'),(270,7,26,308,'joined_team','CY-2A · Team 26','2026-10-20 13:29:03'),(271,7,26,309,'joined_team','CY-2A · Team 26','2026-10-20 13:29:03'),(272,7,26,310,'joined_team','CY-2A · Team 26','2026-10-20 13:29:03'),(273,7,27,311,'joined_team','CY-2A · Team 27','2026-10-20 13:29:03'),(274,7,27,312,'joined_team','CY-2A · Team 27','2026-10-20 13:29:03'),(275,7,27,313,'joined_team','CY-2A · Team 27','2026-10-20 13:29:03'),(276,7,27,314,'joined_team','CY-2A · Team 27','2026-10-20 13:29:03'),(277,7,28,315,'joined_team','CY-2A · Team 28','2026-10-20 13:29:03'),(278,7,28,316,'joined_team','CY-2A · Team 28','2026-10-20 13:29:03'),(279,7,28,317,'joined_team','CY-2A · Team 28','2026-10-20 13:29:03'),(280,7,28,318,'joined_team','CY-2A · Team 28','2026-10-20 13:29:03'),(281,8,29,323,'joined_team','CY-2B · Team 29','2026-10-20 13:29:03'),(282,8,29,324,'joined_team','CY-2B · Team 29','2026-10-20 13:29:03'),(283,8,29,325,'joined_team','CY-2B · Team 29','2026-10-20 13:29:03'),(284,8,29,326,'joined_team','CY-2B · Team 29','2026-10-20 13:29:03'),(285,8,30,327,'joined_team','CY-2B · Team 30','2026-10-20 13:29:03'),(286,8,30,328,'joined_team','CY-2B · Team 30','2026-10-20 13:29:03'),(287,8,30,329,'joined_team','CY-2B · Team 30','2026-10-20 13:29:03'),(288,8,30,330,'joined_team','CY-2B · Team 30','2026-10-20 13:29:03'),(289,8,31,331,'joined_team','CY-2B · Team 31','2026-10-20 13:29:03'),(290,8,31,332,'joined_team','CY-2B · Team 31','2026-10-20 13:29:03'),(291,8,31,333,'joined_team','CY-2B · Team 31','2026-10-20 13:29:03'),(292,8,31,334,'joined_team','CY-2B · Team 31','2026-10-20 13:29:03'),(293,8,32,335,'joined_team','CY-2B · Team 32','2026-10-20 13:29:03'),(294,8,32,336,'joined_team','CY-2B · Team 32','2026-10-20 13:29:03'),(295,8,32,337,'joined_team','CY-2B · Team 32','2026-10-20 13:29:03'),(296,8,32,338,'joined_team','CY-2B · Team 32','2026-10-20 13:29:03');
/*!40000 ALTER TABLE `activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcement_targets`
--

DROP TABLE IF EXISTS `announcement_targets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcement_targets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `announcement_id` int(11) NOT NULL,
  `target_type` enum('course','group','student') NOT NULL,
  `target_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ann` (`announcement_id`),
  KEY `idx_target` (`target_type`,`target_id`),
  CONSTRAINT `announcement_targets_ibfk_1` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcement_targets`
--

LOCK TABLES `announcement_targets` WRITE;
/*!40000 ALTER TABLE `announcement_targets` DISABLE KEYS */;
/*!40000 ALTER TABLE `announcement_targets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teacher_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `target_group_id` int(11) DEFAULT NULL,
  `target_project_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `channels` varchar(50) NOT NULL DEFAULT 'inapp',
  `status` enum('Draft','Scheduled','Sent') NOT NULL DEFAULT 'Sent',
  `scheduled_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `email_sent_count` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `target_group_id` (`target_group_id`),
  KEY `target_project_id` (`target_project_id`),
  CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `announcements_ibfk_2` FOREIGN KEY (`target_group_id`) REFERENCES `groups` (`id`) ON DELETE SET NULL,
  CONSTRAINT `announcements_ibfk_3` FOREIGN KEY (`target_project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_code` varchar(20) NOT NULL,
  `programme` varchar(100) NOT NULL,
  `year_level` tinyint(4) NOT NULL,
  `section` char(1) NOT NULL,
  `course_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_classes_code` (`class_code`),
  KEY `idx_classes_course` (`course_id`),
  KEY `idx_classes_teacher` (`teacher_id`),
  CONSTRAINT `fk_classes_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  CONSTRAINT `fk_classes_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classes`
--

LOCK TABLES `classes` WRITE;
/*!40000 ALTER TABLE `classes` DISABLE KEYS */;
INSERT INTO `classes` VALUES (1,'SE-1A','Software Engineering',1,'A',1,1),(2,'SE-1B','Software Engineering',1,'B',1,2),(3,'SE-2A','Software Engineering',2,'A',1,3),(4,'SE-2B','Software Engineering',2,'B',1,1),(5,'CY-1A','Cybersecurity',1,'A',1,1),(6,'CY-1B','Cybersecurity',1,'B',1,2),(7,'CY-2A','Cybersecurity',2,'A',1,4),(8,'CY-2B','Cybersecurity',2,'B',1,5);
/*!40000 ALTER TABLE `classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contributions`
--

DROP TABLE IF EXISTS `contributions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contributions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `contribution_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `contributions_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contributions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contributions`
--

LOCK TABLES `contributions` WRITE;
/*!40000 ALTER TABLE `contributions` DISABLE KEYS */;
/*!40000 ALTER TABLE `contributions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_enrollments`
--

DROP TABLE IF EXISTS `course_enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_enrollments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_enroll` (`course_id`,`student_id`),
  UNIQUE KEY `uq_course_enrollment` (`course_id`,`student_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `course_enrollments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_enrollments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=346 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_enrollments`
--

LOCK TABLES `course_enrollments` WRITE;
/*!40000 ALTER TABLE `course_enrollments` DISABLE KEYS */;
INSERT INTO `course_enrollments` VALUES (186,1,182,'2026-10-20 13:29:02'),(187,1,183,'2026-10-20 13:29:02'),(188,1,184,'2026-10-20 13:29:02'),(189,1,185,'2026-10-20 13:29:02'),(190,1,186,'2026-10-20 13:29:02'),(191,1,187,'2026-10-20 13:29:02'),(192,1,188,'2026-10-20 13:29:02'),(193,1,189,'2026-10-20 13:29:02'),(194,1,190,'2026-10-20 13:29:02'),(195,1,191,'2026-10-20 13:29:02'),(196,1,192,'2026-10-20 13:29:02'),(197,1,193,'2026-10-20 13:29:02'),(198,1,194,'2026-10-20 13:29:02'),(199,1,195,'2026-10-20 13:29:02'),(200,1,196,'2026-10-20 13:29:02'),(201,1,197,'2026-10-20 13:29:02'),(202,1,198,'2026-10-20 13:29:02'),(203,1,199,'2026-10-20 13:29:02'),(204,1,200,'2026-10-20 13:29:02'),(205,1,201,'2026-10-20 13:29:02'),(206,1,202,'2026-10-20 13:29:02'),(207,1,203,'2026-10-20 13:29:02'),(208,1,204,'2026-10-20 13:29:02'),(209,1,205,'2026-10-20 13:29:02'),(210,1,206,'2026-10-20 13:29:02'),(211,1,207,'2026-10-20 13:29:02'),(212,1,208,'2026-10-20 13:29:02'),(213,1,209,'2026-10-20 13:29:02'),(214,1,210,'2026-10-20 13:29:02'),(215,1,211,'2026-10-20 13:29:02'),(216,1,212,'2026-10-20 13:29:02'),(217,1,213,'2026-10-20 13:29:02'),(218,1,214,'2026-10-20 13:29:02'),(219,1,215,'2026-10-20 13:29:02'),(220,1,216,'2026-10-20 13:29:02'),(221,1,217,'2026-10-20 13:29:02'),(222,1,218,'2026-10-20 13:29:02'),(223,1,219,'2026-10-20 13:29:02'),(224,1,220,'2026-10-20 13:29:02'),(225,1,221,'2026-10-20 13:29:02'),(226,1,222,'2026-10-20 13:29:02'),(227,1,223,'2026-10-20 13:29:02'),(228,1,224,'2026-10-20 13:29:02'),(229,1,225,'2026-10-20 13:29:02'),(230,1,226,'2026-10-20 13:29:02'),(231,1,227,'2026-10-20 13:29:02'),(232,1,228,'2026-10-20 13:29:02'),(233,1,229,'2026-10-20 13:29:02'),(234,1,230,'2026-10-20 13:29:02'),(235,1,231,'2026-10-20 13:29:02'),(236,1,232,'2026-10-20 13:29:02'),(237,1,233,'2026-10-20 13:29:02'),(238,1,234,'2026-10-20 13:29:02'),(239,1,235,'2026-10-20 13:29:02'),(240,1,236,'2026-10-20 13:29:02'),(241,1,237,'2026-10-20 13:29:02'),(242,1,238,'2026-10-20 13:29:02'),(243,1,239,'2026-10-20 13:29:02'),(244,1,240,'2026-10-20 13:29:02'),(245,1,241,'2026-10-20 13:29:02'),(246,1,1,'2026-10-20 13:29:02'),(247,1,2,'2026-10-20 13:29:02'),(248,1,3,'2026-10-20 13:29:02'),(249,1,4,'2026-10-20 13:29:02'),(250,1,242,'2026-10-20 13:29:02'),(251,1,243,'2026-10-20 13:29:02'),(252,1,244,'2026-10-20 13:29:02'),(253,1,245,'2026-10-20 13:29:02'),(254,1,246,'2026-10-20 13:29:02'),(255,1,247,'2026-10-20 13:29:02'),(256,1,248,'2026-10-20 13:29:02'),(257,1,249,'2026-10-20 13:29:02'),(258,1,250,'2026-10-20 13:29:02'),(259,1,251,'2026-10-20 13:29:02'),(260,1,252,'2026-10-20 13:29:02'),(261,1,253,'2026-10-20 13:29:02'),(262,1,254,'2026-10-20 13:29:02'),(263,1,255,'2026-10-20 13:29:02'),(264,1,256,'2026-10-20 13:29:02'),(265,1,257,'2026-10-20 13:29:02'),(266,1,258,'2026-10-20 13:29:02'),(267,1,259,'2026-10-20 13:29:02'),(268,1,260,'2026-10-20 13:29:02'),(269,1,261,'2026-10-20 13:29:02'),(270,1,262,'2026-10-20 13:29:02'),(271,1,263,'2026-10-20 13:29:02'),(272,1,264,'2026-10-20 13:29:02'),(273,1,265,'2026-10-20 13:29:02'),(274,1,266,'2026-10-20 13:29:02'),(275,1,267,'2026-10-20 13:29:02'),(276,1,268,'2026-10-20 13:29:02'),(277,1,269,'2026-10-20 13:29:02'),(278,1,270,'2026-10-20 13:29:02'),(279,1,271,'2026-10-20 13:29:02'),(280,1,272,'2026-10-20 13:29:02'),(281,1,273,'2026-10-20 13:29:02'),(282,1,274,'2026-10-20 13:29:02'),(283,1,275,'2026-10-20 13:29:02'),(284,1,276,'2026-10-20 13:29:02'),(285,1,277,'2026-10-20 13:29:02'),(286,1,278,'2026-10-20 13:29:02'),(287,1,279,'2026-10-20 13:29:02'),(288,1,280,'2026-10-20 13:29:02'),(289,1,281,'2026-10-20 13:29:02'),(290,1,282,'2026-10-20 13:29:02'),(291,1,283,'2026-10-20 13:29:02'),(292,1,284,'2026-10-20 13:29:02'),(293,1,285,'2026-10-20 13:29:02'),(294,1,286,'2026-10-20 13:29:02'),(295,1,287,'2026-10-20 13:29:02'),(296,1,288,'2026-10-20 13:29:02'),(297,1,289,'2026-10-20 13:29:02'),(298,1,290,'2026-10-20 13:29:02'),(299,1,291,'2026-10-20 13:29:02'),(300,1,292,'2026-10-20 13:29:02'),(301,1,293,'2026-10-20 13:29:02'),(302,1,294,'2026-10-20 13:29:02'),(303,1,295,'2026-10-20 13:29:02'),(304,1,296,'2026-10-20 13:29:02'),(305,1,297,'2026-10-20 13:29:02'),(306,1,298,'2026-10-20 13:29:02'),(307,1,299,'2026-10-20 13:29:02'),(308,1,300,'2026-10-20 13:29:02'),(309,1,301,'2026-10-20 13:29:02'),(310,1,302,'2026-10-20 13:29:02'),(311,1,303,'2026-10-20 13:29:02'),(312,1,304,'2026-10-20 13:29:02'),(313,1,305,'2026-10-20 13:29:02'),(314,1,306,'2026-10-20 13:29:02'),(315,1,307,'2026-10-20 13:29:02'),(316,1,308,'2026-10-20 13:29:02'),(317,1,309,'2026-10-20 13:29:02'),(318,1,310,'2026-10-20 13:29:02'),(319,1,311,'2026-10-20 13:29:02'),(320,1,312,'2026-10-20 13:29:02'),(321,1,313,'2026-10-20 13:29:03'),(322,1,314,'2026-10-20 13:29:03'),(323,1,315,'2026-10-20 13:29:03'),(324,1,316,'2026-10-20 13:29:03'),(325,1,317,'2026-10-20 13:29:03'),(326,1,318,'2026-10-20 13:29:03'),(327,1,319,'2026-10-20 13:29:03'),(328,1,320,'2026-10-20 13:29:03'),(329,1,321,'2026-10-20 13:29:03'),(330,1,322,'2026-10-20 13:29:03'),(331,1,323,'2026-10-20 13:29:03'),(332,1,324,'2026-10-20 13:29:03'),(333,1,325,'2026-10-20 13:29:03'),(334,1,326,'2026-10-20 13:29:03'),(335,1,327,'2026-10-20 13:29:03'),(336,1,328,'2026-10-20 13:29:03'),(337,1,329,'2026-10-20 13:29:03'),(338,1,330,'2026-10-20 13:29:03'),(339,1,331,'2026-10-20 13:29:03'),(340,1,332,'2026-10-20 13:29:03'),(341,1,333,'2026-10-20 13:29:03'),(342,1,334,'2026-10-20 13:29:03'),(343,1,335,'2026-10-20 13:29:03'),(344,1,336,'2026-10-20 13:29:03'),(345,1,337,'2026-10-20 13:29:03');
/*!40000 ALTER TABLE `course_enrollments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (1,'ITE4116M','Final Year Project',1,'2026/27 S1','2026-10-07 11:11:58'),(2,'ITE3118','Web Application Dev',1,'2026/27 S1','2026-10-07 11:11:58'),(3,'ITE3120','Mobile Application Dev',2,'2026/27 S1','2026-10-07 11:11:58'),(4,'ITE3220','Database Systems',3,'2026/27 S1','2026-10-07 11:11:58'),(5,'ITE3330','Software Engineering',4,'2026/27 S1','2026-10-07 11:11:58'),(6,'ITE3440','AI & Machine Learning',5,'2026/27 S1','2026-10-07 11:11:58');
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `task_id` int(11) DEFAULT NULL,
  `uploader_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `stored_name` varchar(255) DEFAULT NULL,
  `file_size` varchar(30) DEFAULT NULL,
  `file_type` varchar(30) DEFAULT NULL,
  `status` enum('Unviewed','Accepted','Rejected') DEFAULT 'Unviewed',
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_note` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `group_id` (`group_id`),
  KEY `uploader_id` (`uploader_id`),
  KEY `reviewed_by` (`reviewed_by`),
  KEY `task_id` (`task_id`),
  CONSTRAINT `files_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `files_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `files_ibfk_3` FOREIGN KEY (`uploader_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `files_ibfk_4` FOREIGN KEY (`reviewed_by`) REFERENCES `teachers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `files_ibfk_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files`
--

LOCK TABLES `files` WRITE;
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
/*!40000 ALTER TABLE `files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_members`
--

DROP TABLE IF EXISTS `group_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_members` (
  `group_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `role` enum('leader','member') DEFAULT 'member',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`group_id`,`student_id`),
  UNIQUE KEY `uq_group_member_course` (`student_id`,`course_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `group_members_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_members_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_members`
--

LOCK TABLES `group_members` WRITE;
/*!40000 ALTER TABLE `group_members` DISABLE KEYS */;
INSERT INTO `group_members` VALUES (1,182,NULL,NULL,'leader','2026-10-20 13:29:03'),(1,183,NULL,NULL,'member','2026-10-20 13:29:03'),(1,184,NULL,NULL,'member','2026-10-20 13:29:03'),(1,185,NULL,NULL,'member','2026-10-20 13:29:03'),(2,186,NULL,NULL,'leader','2026-10-20 13:29:03'),(2,187,NULL,NULL,'member','2026-10-20 13:29:03'),(2,188,NULL,NULL,'member','2026-10-20 13:29:03'),(2,189,NULL,NULL,'member','2026-10-20 13:29:03'),(3,190,NULL,NULL,'leader','2026-10-20 13:29:03'),(3,191,NULL,NULL,'member','2026-10-20 13:29:03'),(3,192,NULL,NULL,'member','2026-10-20 13:29:03'),(3,193,NULL,NULL,'member','2026-10-20 13:29:03'),(4,194,NULL,NULL,'leader','2026-10-20 13:29:03'),(4,195,NULL,NULL,'member','2026-10-20 13:29:03'),(4,196,NULL,NULL,'member','2026-10-20 13:29:03'),(4,197,NULL,NULL,'member','2026-10-20 13:29:03'),(5,1,NULL,NULL,'leader','2026-10-20 13:29:03'),(5,2,NULL,NULL,'member','2026-10-20 13:29:03'),(5,3,NULL,NULL,'member','2026-10-20 13:29:03'),(5,4,NULL,NULL,'member','2026-10-20 13:29:03'),(6,242,NULL,NULL,'leader','2026-10-20 13:29:03'),(6,243,NULL,NULL,'member','2026-10-20 13:29:03'),(6,244,NULL,NULL,'member','2026-10-20 13:29:03'),(6,245,NULL,NULL,'member','2026-10-20 13:29:03'),(7,246,NULL,NULL,'leader','2026-10-20 13:29:03'),(7,247,NULL,NULL,'member','2026-10-20 13:29:03'),(7,248,NULL,NULL,'member','2026-10-20 13:29:03'),(7,249,NULL,NULL,'member','2026-10-20 13:29:03'),(8,250,NULL,NULL,'leader','2026-10-20 13:29:03'),(8,251,NULL,NULL,'member','2026-10-20 13:29:03'),(8,252,NULL,NULL,'member','2026-10-20 13:29:03'),(8,253,NULL,NULL,'member','2026-10-20 13:29:03'),(9,258,NULL,NULL,'leader','2026-10-20 13:29:03'),(9,259,NULL,NULL,'member','2026-10-20 13:29:03'),(9,260,NULL,NULL,'member','2026-10-20 13:29:03'),(9,261,NULL,NULL,'member','2026-10-20 13:29:03'),(10,262,NULL,NULL,'leader','2026-10-20 13:29:03'),(10,263,NULL,NULL,'member','2026-10-20 13:29:03'),(10,264,NULL,NULL,'member','2026-10-20 13:29:03'),(10,265,NULL,NULL,'member','2026-10-20 13:29:03'),(11,266,NULL,NULL,'leader','2026-10-20 13:29:03'),(11,267,NULL,NULL,'member','2026-10-20 13:29:03'),(11,268,NULL,NULL,'member','2026-10-20 13:29:03'),(11,269,NULL,NULL,'member','2026-10-20 13:29:03'),(12,270,NULL,NULL,'leader','2026-10-20 13:29:03'),(12,271,NULL,NULL,'member','2026-10-20 13:29:03'),(12,272,NULL,NULL,'member','2026-10-20 13:29:03'),(12,273,NULL,NULL,'member','2026-10-20 13:29:03'),(13,202,NULL,NULL,'leader','2026-10-20 13:29:03'),(13,203,NULL,NULL,'member','2026-10-20 13:29:03'),(13,204,NULL,NULL,'member','2026-10-20 13:29:03'),(13,205,NULL,NULL,'member','2026-10-20 13:29:03'),(14,206,NULL,NULL,'leader','2026-10-20 13:29:03'),(14,207,NULL,NULL,'member','2026-10-20 13:29:03'),(14,208,NULL,NULL,'member','2026-10-20 13:29:03'),(14,209,NULL,NULL,'member','2026-10-20 13:29:03'),(15,210,NULL,NULL,'leader','2026-10-20 13:29:03'),(15,211,NULL,NULL,'member','2026-10-20 13:29:03'),(15,212,NULL,NULL,'member','2026-10-20 13:29:03'),(15,213,NULL,NULL,'member','2026-10-20 13:29:03'),(16,214,NULL,NULL,'leader','2026-10-20 13:29:03'),(16,215,NULL,NULL,'member','2026-10-20 13:29:03'),(16,216,NULL,NULL,'member','2026-10-20 13:29:03'),(16,217,NULL,NULL,'member','2026-10-20 13:29:03'),(17,278,NULL,NULL,'leader','2026-10-20 13:29:03'),(17,279,NULL,NULL,'member','2026-10-20 13:29:03'),(17,280,NULL,NULL,'member','2026-10-20 13:29:03'),(17,281,NULL,NULL,'member','2026-10-20 13:29:03'),(18,282,NULL,NULL,'leader','2026-10-20 13:29:03'),(18,283,NULL,NULL,'member','2026-10-20 13:29:03'),(18,284,NULL,NULL,'member','2026-10-20 13:29:03'),(18,285,NULL,NULL,'member','2026-10-20 13:29:03'),(19,286,NULL,NULL,'leader','2026-10-20 13:29:03'),(19,287,NULL,NULL,'member','2026-10-20 13:29:03'),(19,288,NULL,NULL,'member','2026-10-20 13:29:03'),(19,289,NULL,NULL,'member','2026-10-20 13:29:03'),(20,290,NULL,NULL,'leader','2026-10-20 13:29:03'),(20,291,NULL,NULL,'member','2026-10-20 13:29:03'),(20,292,NULL,NULL,'member','2026-10-20 13:29:03'),(20,293,NULL,NULL,'member','2026-10-20 13:29:03'),(21,222,NULL,NULL,'leader','2026-10-20 13:29:03'),(21,223,NULL,NULL,'member','2026-10-20 13:29:03'),(21,224,NULL,NULL,'member','2026-10-20 13:29:03'),(21,225,NULL,NULL,'member','2026-10-20 13:29:03'),(22,226,NULL,NULL,'leader','2026-10-20 13:29:03'),(22,227,NULL,NULL,'member','2026-10-20 13:29:03'),(22,228,NULL,NULL,'member','2026-10-20 13:29:03'),(22,229,NULL,NULL,'member','2026-10-20 13:29:03'),(23,230,NULL,NULL,'leader','2026-10-20 13:29:03'),(23,231,NULL,NULL,'member','2026-10-20 13:29:03'),(23,232,NULL,NULL,'member','2026-10-20 13:29:03'),(23,233,NULL,NULL,'member','2026-10-20 13:29:03'),(24,234,NULL,NULL,'leader','2026-10-20 13:29:03'),(24,235,NULL,NULL,'member','2026-10-20 13:29:03'),(24,236,NULL,NULL,'member','2026-10-20 13:29:03'),(24,237,NULL,NULL,'member','2026-10-20 13:29:03'),(25,298,NULL,NULL,'leader','2026-10-20 13:29:03'),(25,299,NULL,NULL,'member','2026-10-20 13:29:03'),(25,300,NULL,NULL,'member','2026-10-20 13:29:03'),(25,301,NULL,NULL,'member','2026-10-20 13:29:03'),(26,302,NULL,NULL,'leader','2026-10-20 13:29:03'),(26,303,NULL,NULL,'member','2026-10-20 13:29:03'),(26,304,NULL,NULL,'member','2026-10-20 13:29:03'),(26,305,NULL,NULL,'member','2026-10-20 13:29:03'),(27,306,NULL,NULL,'leader','2026-10-20 13:29:03'),(27,307,NULL,NULL,'member','2026-10-20 13:29:03'),(27,308,NULL,NULL,'member','2026-10-20 13:29:03'),(27,309,NULL,NULL,'member','2026-10-20 13:29:03'),(28,310,NULL,NULL,'leader','2026-10-20 13:29:03'),(28,311,NULL,NULL,'member','2026-10-20 13:29:03'),(28,312,NULL,NULL,'member','2026-10-20 13:29:03'),(28,313,NULL,NULL,'member','2026-10-20 13:29:03'),(29,318,NULL,NULL,'leader','2026-10-20 13:29:03'),(29,319,NULL,NULL,'member','2026-10-20 13:29:03'),(29,320,NULL,NULL,'member','2026-10-20 13:29:03'),(29,321,NULL,NULL,'member','2026-10-20 13:29:03'),(30,322,NULL,NULL,'leader','2026-10-20 13:29:03'),(30,323,NULL,NULL,'member','2026-10-20 13:29:03'),(30,324,NULL,NULL,'member','2026-10-20 13:29:03'),(30,325,NULL,NULL,'member','2026-10-20 13:29:03'),(31,326,NULL,NULL,'leader','2026-10-20 13:29:03'),(31,327,NULL,NULL,'member','2026-10-20 13:29:03'),(31,328,NULL,NULL,'member','2026-10-20 13:29:03'),(31,329,NULL,NULL,'member','2026-10-20 13:29:03'),(32,330,NULL,NULL,'leader','2026-10-20 13:29:03'),(32,331,NULL,NULL,'member','2026-10-20 13:29:03'),(32,332,NULL,NULL,'member','2026-10-20 13:29:03'),(32,333,NULL,NULL,'member','2026-10-20 13:29:03');
/*!40000 ALTER TABLE `group_members` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_group_members_validate_insert`
         BEFORE INSERT ON `group_members`
         FOR EACH ROW
         BEGIN
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
         END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `group_requests`
--

DROP TABLE IF EXISTS `group_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `group_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(100) NOT NULL,
  `project_id` int(11) NOT NULL,
  `submitted_by` int(11) NOT NULL,
  `status` enum('Pending','Accepted','Rejected') DEFAULT 'Pending',
  `review_note` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `submitted_by` (`submitted_by`),
  KEY `reviewed_by` (`reviewed_by`),
  CONSTRAINT `group_requests_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_requests_ibfk_2` FOREIGN KEY (`submitted_by`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_requests_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `teachers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_requests`
--

LOCK TABLES `group_requests` WRITE;
/*!40000 ALTER TABLE `group_requests` DISABLE KEYS */;
INSERT INTO `group_requests` VALUES (6,'Team 03',1,190,'Pending',NULL,'2026-10-20 13:29:03',NULL,NULL),(7,'Team 07',2,246,'Pending',NULL,'2026-10-20 13:29:03',NULL,NULL),(8,'Team 11',3,266,'Pending',NULL,'2026-10-20 13:29:03',NULL,NULL);
/*!40000 ALTER TABLE `group_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `leader_id` int(11) DEFAULT NULL,
  `creator_id` int(11) DEFAULT NULL,
  `max_members` int(11) DEFAULT 4,
  `status` enum('Recruiting','Waiting_Send','Voting','Executing','Completed') DEFAULT 'Recruiting',
  `formed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `course_id` (`course_id`),
  KEY `leader_id` (`leader_id`),
  KEY `creator_id` (`creator_id`),
  CONSTRAINT `groups_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `groups_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `groups_ibfk_3` FOREIGN KEY (`leader_id`) REFERENCES `students` (`id`) ON DELETE SET NULL,
  CONSTRAINT `groups_ibfk_creator` FOREIGN KEY (`creator_id`) REFERENCES `students` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,1,1,'Team 01',182,182,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(2,1,1,'Team 02',186,186,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(3,1,1,'Team 03',190,190,4,'Waiting_Send','2026-10-20 21:29:03','2026-10-20 13:29:03'),(4,1,1,'Team 04',194,194,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(5,2,1,'Team 05',1,1,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(6,2,1,'Team 06',242,242,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(7,2,1,'Team 07',246,246,4,'Waiting_Send','2026-10-20 21:29:03','2026-10-20 13:29:03'),(8,2,1,'Team 08',250,250,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(9,3,1,'Team 09',258,258,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(10,3,1,'Team 10',262,262,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(11,3,1,'Team 11',266,266,4,'Waiting_Send','2026-10-20 21:29:03','2026-10-20 13:29:03'),(12,3,1,'Team 12',270,270,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(13,4,1,'Team 13',202,202,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(14,4,1,'Team 14',206,206,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(15,4,1,'Team 15',210,210,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(16,4,1,'Team 16',214,214,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(17,5,1,'Team 17',278,278,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(18,5,1,'Team 18',282,282,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(19,5,1,'Team 19',286,286,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(20,5,1,'Team 20',290,290,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(21,6,1,'Team 21',222,222,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(22,6,1,'Team 22',226,226,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(23,6,1,'Team 23',230,230,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(24,6,1,'Team 24',234,234,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(25,7,1,'Team 25',298,298,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(26,7,1,'Team 26',302,302,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(27,7,1,'Team 27',306,306,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(28,7,1,'Team 28',310,310,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(29,8,1,'Team 29',318,318,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(30,8,1,'Team 30',322,322,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(31,8,1,'Team 31',326,326,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03'),(32,8,1,'Team 32',330,330,4,'Executing','2026-10-20 21:29:03','2026-10-20 13:29:03');
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invitations`
--

DROP TABLE IF EXISTS `invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invitations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `inviter_id` int(11) NOT NULL,
  `invitee_id` int(11) NOT NULL,
  `status` enum('Pending','Accepted','Rejected','Cancelled','Leave') DEFAULT 'Pending',
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `responded_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_invite` (`group_id`,`invitee_id`),
  KEY `inviter_id` (`inviter_id`),
  KEY `invitee_id` (`invitee_id`),
  CONSTRAINT `invitations_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invitations_ibfk_2` FOREIGN KEY (`inviter_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invitations_ibfk_3` FOREIGN KEY (`invitee_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invitations`
--

LOCK TABLES `invitations` WRITE;
/*!40000 ALTER TABLE `invitations` DISABLE KEYS */;
/*!40000 ALTER TABLE `invitations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leader_votes`
--

DROP TABLE IF EXISTS `leader_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leader_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `voter_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vote` (`group_id`,`voter_id`),
  KEY `voter_id` (`voter_id`),
  KEY `candidate_id` (`candidate_id`),
  CONSTRAINT `leader_votes_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leader_votes_ibfk_2` FOREIGN KEY (`voter_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leader_votes_ibfk_3` FOREIGN KEY (`candidate_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leader_votes`
--

LOCK TABLES `leader_votes` WRITE;
/*!40000 ALTER TABLE `leader_votes` DISABLE KEYS */;
/*!40000 ALTER TABLE `leader_votes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_reads`
--

DROP TABLE IF EXISTS `message_reads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_reads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `last_read_message_id` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_read` (`user_id`,`group_id`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `message_reads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `message_reads_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_reads`
--

LOCK TABLES `message_reads` WRITE;
/*!40000 ALTER TABLE `message_reads` DISABLE KEYS */;
/*!40000 ALTER TABLE `message_reads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `sender_user_id` int(11) DEFAULT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `attachment_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `pinned_at` datetime DEFAULT NULL,
  `pinned_by_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`),
  KEY `sender_id` (`sender_id`),
  KEY `attachment_id` (`attachment_id`),
  KEY `recipient_id` (`recipient_id`),
  KEY `idx_messages_sender_user` (`sender_user_id`),
  KEY `idx_messages_pinned` (`group_id`,`is_pinned`),
  KEY `fk_messages_pinned_by` (`pinned_by_user_id`),
  CONSTRAINT `fk_messages_pinned_by` FOREIGN KEY (`pinned_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_messages_sender_user` FOREIGN KEY (`sender_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`attachment_id`) REFERENCES `files` (`id`) ON DELETE SET NULL,
  CONSTRAINT `messages_ibfk_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `milestones`
--

DROP TABLE IF EXISTS `milestones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `milestones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending',
  `progress` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `milestones_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `milestones`
--

LOCK TABLES `milestones` WRITE;
/*!40000 ALTER TABLE `milestones` DISABLE KEYS */;
INSERT INTO `milestones` VALUES (73,1,'Proposal and planning','2026-09-01','2026-10-15','Pending',0),(74,1,'Prototype checkpoint','2026-10-16','2026-11-20','Pending',0),(75,1,'Final submission','2026-11-21','2027-01-20','Pending',0),(76,2,'Proposal and planning','2026-09-01','2026-10-15','Pending',0),(77,2,'Prototype checkpoint','2026-10-16','2026-11-20','Pending',0),(78,2,'Final submission','2026-11-21','2027-01-20','Pending',0),(79,3,'Proposal and planning','2026-09-01','2026-10-15','Pending',0),(80,3,'Prototype checkpoint','2026-10-16','2026-11-20','Pending',0),(81,3,'Final submission','2026-11-21','2027-01-20','Pending',0),(82,4,'Proposal and planning','2026-09-01','2026-10-15','Pending',0),(83,4,'Prototype checkpoint','2026-10-16','2026-11-20','Pending',0),(84,4,'Final submission','2026-11-21','2027-01-20','Pending',0),(85,5,'Proposal and planning','2026-09-01','2026-10-15','Pending',0),(86,5,'Prototype checkpoint','2026-10-16','2026-11-20','Pending',0),(87,5,'Final submission','2026-11-21','2027-01-20','Pending',0),(88,6,'Proposal and planning','2026-09-01','2026-10-15','Pending',0),(89,6,'Prototype checkpoint','2026-10-16','2026-11-20','Pending',0),(90,6,'Final submission','2026-11-21','2027-01-20','Pending',0),(91,7,'Proposal and planning','2026-09-01','2026-10-15','Pending',0),(92,7,'Prototype checkpoint','2026-10-16','2026-11-20','Pending',0),(93,7,'Final submission','2026-11-21','2027-01-20','Pending',0),(94,8,'Proposal and planning','2026-09-01','2026-10-15','Pending',0),(95,8,'Prototype checkpoint','2026-10-16','2026-11-20','Pending',0),(96,8,'Final submission','2026-11-21','2027-01-20','Pending',0);
/*!40000 ALTER TABLE `milestones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('task','file','team','project','system') NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=120 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_attachments`
--

DROP TABLE IF EXISTS `project_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `stored_name` varchar(255) NOT NULL,
  `file_size` varchar(30) DEFAULT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `project_attachments_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_attachments`
--

LOCK TABLES `project_attachments` WRITE;
/*!40000 ALTER TABLE `project_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_extension_requests`
--

DROP TABLE IF EXISTS `project_extension_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_extension_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `requested_end` date NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `student_id` (`student_id`),
  KEY `reviewed_by` (`reviewed_by`),
  CONSTRAINT `project_ext_req_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_ext_req_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_ext_req_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `teachers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_extension_requests`
--

LOCK TABLES `project_extension_requests` WRITE;
/*!40000 ALTER TABLE `project_extension_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_extension_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `assessment_scope` varchar(30) DEFAULT NULL,
  `assessment_weight` tinyint(3) unsigned DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `supervisor_id` int(11) NOT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `status` enum('Recruiting','Waiting_Send','Voting','Executing','Completed','Archived') DEFAULT 'Recruiting',
  `progress` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `team_mode` enum('Free','Random','Teacher_Assigned') NOT NULL DEFAULT 'Free',
  `team_deadline` date DEFAULT NULL,
  `allow_cross_class` tinyint(1) NOT NULL DEFAULT 0,
  `default_max_members` int(11) NOT NULL DEFAULT 4,
  `leader_permissions` text DEFAULT NULL,
  `post_deadline_action` enum('Lock','Notify') NOT NULL DEFAULT 'Lock',
  `visibility` enum('All','Course') NOT NULL DEFAULT 'Course',
  `reminder_days` int(11) NOT NULL DEFAULT 3,
  `allow_extension` tinyint(1) NOT NULL DEFAULT 1,
  `is_draft` tinyint(1) NOT NULL DEFAULT 0,
  `last_reminder_sent` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `supervisor_id` (`supervisor_id`),
  KEY `course_id` (`course_id`),
  KEY `idx_projects_draft` (`is_draft`),
  KEY `fk_projects_class` (`class_id`),
  CONSTRAINT `fk_projects_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`supervisor_id`) REFERENCES `teachers` (`id`),
  CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (1,'TeamBox','Final Year Project',NULL,NULL,1,1,1,'2026-09-01','2027-01-31','Executing',20,'TeamBox is a Final Year Project for class SE-1A.','Students work in teams of four and submit progress through TeamBox.','Random','2026-10-18',0,4,'[\"invite\",\"remove\",\"rename\",\"assign\",\"submit\"]','Lock','Course',3,1,0,NULL,'2026-10-20 13:29:03'),(2,'Smart Attendance','Final Year Project',NULL,NULL,1,4,1,'2026-09-01','2027-01-31','Executing',20,'Smart Attendance is a Final Year Project for class SE-2B.','Students work in teams of four and submit progress through TeamBox.','Random','2026-11-03',0,4,'[\"invite\",\"remove\",\"rename\",\"assign\",\"submit\"]','Lock','Course',3,1,0,NULL,'2026-10-20 13:29:03'),(3,'Campus Map AR','Final Year Project',NULL,NULL,1,5,1,'2026-09-01','2027-01-31','Executing',20,'Campus Map AR is a Final Year Project for class CY-1A.','Students work in teams of four and submit progress through TeamBox.','Random','2026-11-10',0,4,'[\"invite\",\"remove\",\"rename\",\"assign\",\"submit\"]','Lock','Course',3,1,0,NULL,'2026-10-20 13:29:03'),(4,'Smart Library','Final Year Project',NULL,NULL,1,2,2,'2026-09-01','2027-01-31','Executing',20,'Smart Library is a Final Year Project for class SE-1B.','Students work in teams of four and submit progress through TeamBox.','Random','2026-11-05',0,4,'[\"invite\",\"remove\",\"rename\",\"assign\",\"submit\"]','Lock','Course',3,1,0,NULL,'2026-10-20 13:29:03'),(5,'Campus Food Order','Final Year Project',NULL,NULL,1,6,2,'2026-09-01','2027-01-31','Executing',20,'Campus Food Order is a Final Year Project for class CY-1B.','Students work in teams of four and submit progress through TeamBox.','Random','2026-11-07',0,4,'[\"invite\",\"remove\",\"rename\",\"assign\",\"submit\"]','Lock','Course',3,1,0,NULL,'2026-10-20 13:29:03'),(6,'AI Study Buddy','Final Year Project',NULL,NULL,1,3,3,'2026-09-01','2027-01-31','Executing',20,'AI Study Buddy is a Final Year Project for class SE-2A.','Students work in teams of four and submit progress through TeamBox.','Random','2026-11-07',0,4,'[\"invite\",\"remove\",\"rename\",\"assign\",\"submit\"]','Lock','Course',3,1,0,NULL,'2026-10-20 13:29:03'),(7,'Green Campus','Final Year Project',NULL,NULL,1,7,4,'2026-09-01','2027-01-31','Executing',20,'Green Campus is a Final Year Project for class CY-2A.','Students work in teams of four and submit progress through TeamBox.','Random','2026-11-09',0,4,'[\"invite\",\"remove\",\"rename\",\"assign\",\"submit\"]','Lock','Course',3,1,0,NULL,'2026-10-20 13:29:03'),(8,'Career Path Finder','Final Year Project',NULL,NULL,1,8,5,'2026-09-01','2027-01-31','Executing',20,'Career Path Finder is a Final Year Project for class CY-2B.','Students work in teams of four and submit progress through TeamBox.','Random','2026-11-11',0,4,'[\"invite\",\"remove\",\"rename\",\"assign\",\"submit\"]','Lock','Course',3,1,0,NULL,'2026-10-20 13:29:03');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `request_members`
--

DROP TABLE IF EXISTS `request_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `request_members` (
  `request_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `is_leader` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`request_id`,`student_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `request_members_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `group_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `request_members_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `request_members`
--

LOCK TABLES `request_members` WRITE;
/*!40000 ALTER TABLE `request_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `request_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `major` varchar(100) DEFAULT NULL,
  `grade_level` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `student_number` (`student_number`),
  KEY `fk_students_class` (`class_id`),
  CONSTRAINT `fk_students_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=338 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,6,'S48291537','Software Engineering',13,4),(2,7,'S15942736','Software Engineering',13,4),(3,8,'S73658120','Software Engineering',13,4),(4,9,'S20473819','Software Engineering',13,4),(182,187,'S27000000','Software Engineering',12,1),(183,188,'S27000001','Software Engineering',12,1),(184,189,'S27000002','Software Engineering',12,1),(185,190,'S27000003','Software Engineering',12,1),(186,191,'S27000004','Software Engineering',12,1),(187,192,'S27000005','Software Engineering',12,1),(188,193,'S27000006','Software Engineering',12,1),(189,194,'S27000007','Software Engineering',12,1),(190,195,'S27000008','Software Engineering',12,1),(191,196,'S27000009','Software Engineering',12,1),(192,197,'S27000010','Software Engineering',12,1),(193,198,'S27000011','Software Engineering',12,1),(194,199,'S27000012','Software Engineering',12,1),(195,200,'S27000013','Software Engineering',12,1),(196,201,'S27000014','Software Engineering',12,1),(197,202,'S27000015','Software Engineering',12,1),(198,203,'S27000016','Software Engineering',12,1),(199,204,'S27000017','Software Engineering',12,1),(200,205,'S27000018','Software Engineering',12,1),(201,206,'S27000019','Software Engineering',12,1),(202,207,'S27000020','Software Engineering',12,2),(203,208,'S27000021','Software Engineering',12,2),(204,209,'S27000022','Software Engineering',12,2),(205,210,'S27000023','Software Engineering',12,2),(206,211,'S27000024','Software Engineering',12,2),(207,212,'S27000025','Software Engineering',12,2),(208,213,'S27000026','Software Engineering',12,2),(209,214,'S27000027','Software Engineering',12,2),(210,215,'S27000028','Software Engineering',12,2),(211,216,'S27000029','Software Engineering',12,2),(212,217,'S27000030','Software Engineering',12,2),(213,218,'S27000031','Software Engineering',12,2),(214,219,'S27000032','Software Engineering',12,2),(215,220,'S27000033','Software Engineering',12,2),(216,221,'S27000034','Software Engineering',12,2),(217,222,'S27000035','Software Engineering',12,2),(218,223,'S27000036','Software Engineering',12,2),(219,224,'S27000037','Software Engineering',12,2),(220,225,'S27000038','Software Engineering',12,2),(221,226,'S27000039','Software Engineering',12,2),(222,227,'S27000040','Software Engineering',13,3),(223,228,'S27000041','Software Engineering',13,3),(224,229,'S27000042','Software Engineering',13,3),(225,230,'S27000043','Software Engineering',13,3),(226,231,'S27000044','Software Engineering',13,3),(227,232,'S27000045','Software Engineering',13,3),(228,233,'S27000046','Software Engineering',13,3),(229,234,'S27000047','Software Engineering',13,3),(230,235,'S27000048','Software Engineering',13,3),(231,236,'S27000049','Software Engineering',13,3),(232,237,'S27000050','Software Engineering',13,3),(233,238,'S27000051','Software Engineering',13,3),(234,239,'S27000052','Software Engineering',13,3),(235,240,'S27000053','Software Engineering',13,3),(236,241,'S27000054','Software Engineering',13,3),(237,242,'S27000055','Software Engineering',13,3),(238,243,'S27000056','Software Engineering',13,3),(239,244,'S27000057','Software Engineering',13,3),(240,245,'S27000058','Software Engineering',13,3),(241,246,'S27000059','Software Engineering',13,3),(242,247,'S27000060','Software Engineering',13,4),(243,248,'S27000061','Software Engineering',13,4),(244,249,'S27000062','Software Engineering',13,4),(245,250,'S27000063','Software Engineering',13,4),(246,251,'S27000064','Software Engineering',13,4),(247,252,'S27000065','Software Engineering',13,4),(248,253,'S27000066','Software Engineering',13,4),(249,254,'S27000067','Software Engineering',13,4),(250,255,'S27000068','Software Engineering',13,4),(251,256,'S27000069','Software Engineering',13,4),(252,257,'S27000070','Software Engineering',13,4),(253,258,'S27000071','Software Engineering',13,4),(254,259,'S27000072','Software Engineering',13,4),(255,260,'S27000073','Software Engineering',13,4),(256,261,'S27000074','Software Engineering',13,4),(257,262,'S27000075','Software Engineering',13,4),(258,263,'S27000076','Cybersecurity',12,5),(259,264,'S27000077','Cybersecurity',12,5),(260,265,'S27000078','Cybersecurity',12,5),(261,266,'S27000079','Cybersecurity',12,5),(262,267,'S27000080','Cybersecurity',12,5),(263,268,'S27000081','Cybersecurity',12,5),(264,269,'S27000082','Cybersecurity',12,5),(265,270,'S27000083','Cybersecurity',12,5),(266,271,'S27000084','Cybersecurity',12,5),(267,272,'S27000085','Cybersecurity',12,5),(268,273,'S27000086','Cybersecurity',12,5),(269,274,'S27000087','Cybersecurity',12,5),(270,275,'S27000088','Cybersecurity',12,5),(271,276,'S27000089','Cybersecurity',12,5),(272,277,'S27000090','Cybersecurity',12,5),(273,278,'S27000091','Cybersecurity',12,5),(274,279,'S27000092','Cybersecurity',12,5),(275,280,'S27000093','Cybersecurity',12,5),(276,281,'S27000094','Cybersecurity',12,5),(277,282,'S27000095','Cybersecurity',12,5),(278,283,'S27000096','Cybersecurity',12,6),(279,284,'S27000097','Cybersecurity',12,6),(280,285,'S27000098','Cybersecurity',12,6),(281,286,'S27000099','Cybersecurity',12,6),(282,287,'S27000100','Cybersecurity',12,6),(283,288,'S27000101','Cybersecurity',12,6),(284,289,'S27000102','Cybersecurity',12,6),(285,290,'S27000103','Cybersecurity',12,6),(286,291,'S27000104','Cybersecurity',12,6),(287,292,'S27000105','Cybersecurity',12,6),(288,293,'S27000106','Cybersecurity',12,6),(289,294,'S27000107','Cybersecurity',12,6),(290,295,'S27000108','Cybersecurity',12,6),(291,296,'S27000109','Cybersecurity',12,6),(292,297,'S27000110','Cybersecurity',12,6),(293,298,'S27000111','Cybersecurity',12,6),(294,299,'S27000112','Cybersecurity',12,6),(295,300,'S27000113','Cybersecurity',12,6),(296,301,'S27000114','Cybersecurity',12,6),(297,302,'S27000115','Cybersecurity',12,6),(298,303,'S27000116','Cybersecurity',13,7),(299,304,'S27000117','Cybersecurity',13,7),(300,305,'S27000118','Cybersecurity',13,7),(301,306,'S27000119','Cybersecurity',13,7),(302,307,'S27000120','Cybersecurity',13,7),(303,308,'S27000121','Cybersecurity',13,7),(304,309,'S27000122','Cybersecurity',13,7),(305,310,'S27000123','Cybersecurity',13,7),(306,311,'S27000124','Cybersecurity',13,7),(307,312,'S27000125','Cybersecurity',13,7),(308,313,'S27000126','Cybersecurity',13,7),(309,314,'S27000127','Cybersecurity',13,7),(310,315,'S27000128','Cybersecurity',13,7),(311,316,'S27000129','Cybersecurity',13,7),(312,317,'S27000130','Cybersecurity',13,7),(313,318,'S27000131','Cybersecurity',13,7),(314,319,'S27000132','Cybersecurity',13,7),(315,320,'S27000133','Cybersecurity',13,7),(316,321,'S27000134','Cybersecurity',13,7),(317,322,'S27000135','Cybersecurity',13,7),(318,323,'S27000136','Cybersecurity',13,8),(319,324,'S27000137','Cybersecurity',13,8),(320,325,'S27000138','Cybersecurity',13,8),(321,326,'S27000139','Cybersecurity',13,8),(322,327,'S27000140','Cybersecurity',13,8),(323,328,'S27000141','Cybersecurity',13,8),(324,329,'S27000142','Cybersecurity',13,8),(325,330,'S27000143','Cybersecurity',13,8),(326,331,'S27000144','Cybersecurity',13,8),(327,332,'S27000145','Cybersecurity',13,8),(328,333,'S27000146','Cybersecurity',13,8),(329,334,'S27000147','Cybersecurity',13,8),(330,335,'S27000148','Cybersecurity',13,8),(331,336,'S27000149','Cybersecurity',13,8),(332,337,'S27000150','Cybersecurity',13,8),(333,338,'S27000151','Cybersecurity',13,8),(334,339,'S27000152','Cybersecurity',13,8),(335,340,'S27000153','Cybersecurity',13,8),(336,341,'S27000154','Cybersecurity',13,8),(337,342,'S27000155','Cybersecurity',13,8);
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `status` enum('Pending','In Progress','Waiting_Confirm','Completed') DEFAULT 'Pending',
  `priority` enum('Low','Medium','High') DEFAULT 'Medium',
  `due_date` date DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `group_id` (`group_id`),
  KEY `assigned_to` (`assigned_to`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tasks_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `students` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` VALUES (80,1,1,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',182,187,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(81,1,1,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',183,187,'In Progress','Medium','2026-10-19',NULL,'2026-10-20 13:29:03'),(82,1,2,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',186,191,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(83,1,2,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',187,191,'In Progress','Medium','2026-10-27',NULL,'2026-10-20 13:29:03'),(84,1,4,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',194,199,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(85,1,4,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',195,199,'In Progress','Medium','2026-10-27',NULL,'2026-10-20 13:29:03'),(86,2,5,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',1,6,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(87,2,5,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',2,6,'In Progress','Medium','2026-10-19',NULL,'2026-10-20 13:29:03'),(88,2,6,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',242,247,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(89,2,6,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',243,247,'In Progress','Medium','2026-10-27',NULL,'2026-10-20 13:29:03'),(90,2,8,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',250,255,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(91,2,8,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',251,255,'In Progress','Medium','2026-10-27',NULL,'2026-10-20 13:29:03'),(92,3,9,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',258,263,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(93,3,9,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',259,263,'In Progress','Medium','2026-10-19',NULL,'2026-10-20 13:29:03'),(94,3,10,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',262,267,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(95,3,10,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',263,267,'In Progress','Medium','2026-10-27',NULL,'2026-10-20 13:29:03'),(96,3,12,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',270,275,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(97,3,12,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',271,275,'In Progress','Medium','2026-10-27',NULL,'2026-10-20 13:29:03'),(98,4,13,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',202,207,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(99,4,13,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',203,207,'In Progress','Medium','2026-10-19',NULL,'2026-10-20 13:29:03'),(100,4,14,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',206,211,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(101,4,14,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',207,211,'In Progress','Medium','2026-10-27',NULL,'2026-10-20 13:29:03'),(102,4,15,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',210,215,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(103,4,15,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',211,215,'In Progress','Medium','2026-10-27',NULL,'2026-10-20 13:29:03'),(104,4,16,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',214,219,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(105,4,16,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',215,219,'In Progress','Medium','2026-10-27',NULL,'2026-10-20 13:29:03'),(106,5,17,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',278,283,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(107,5,17,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',279,283,'In Progress','Medium','2026-10-19',NULL,'2026-10-20 13:29:03'),(108,5,18,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',282,287,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(109,5,18,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',283,287,'In Progress','Medium','2026-10-27',NULL,'2026-10-20 13:29:03'),(110,5,19,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',286,291,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(111,5,19,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',287,291,'In Progress','Medium','2026-10-27',NULL,'2026-10-20 13:29:03'),(112,5,20,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',290,295,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(113,5,20,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',291,295,'In Progress','Medium','2026-10-27',NULL,'2026-10-20 13:29:03'),(114,6,21,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',222,227,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(115,6,21,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',223,227,'In Progress','Medium','2026-10-19',NULL,'2026-10-20 13:29:03'),(116,6,22,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',226,231,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(117,6,22,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',227,231,'In Progress','Medium','2026-10-27',NULL,'2026-10-20 13:29:03'),(118,6,23,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',230,235,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(119,6,23,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',231,235,'In Progress','Medium','2026-10-27',NULL,'2026-10-20 13:29:03'),(120,6,24,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',234,239,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(121,6,24,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',235,239,'In Progress','Medium','2026-10-27',NULL,'2026-10-20 13:29:03'),(122,7,25,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',298,303,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(123,7,25,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',299,303,'In Progress','Medium','2026-10-19',NULL,'2026-10-20 13:29:03'),(124,7,26,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',302,307,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(125,7,26,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',303,307,'In Progress','Medium','2026-10-27',NULL,'2026-10-20 13:29:03'),(126,7,27,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',306,311,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(127,7,27,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',307,311,'In Progress','Medium','2026-10-27',NULL,'2026-10-20 13:29:03'),(128,7,28,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',310,315,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(129,7,28,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',311,315,'In Progress','Medium','2026-10-27',NULL,'2026-10-20 13:29:03'),(130,8,29,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',318,323,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(131,8,29,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',319,323,'In Progress','Medium','2026-10-19',NULL,'2026-10-20 13:29:03'),(132,8,30,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',322,327,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(133,8,30,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',323,327,'In Progress','Medium','2026-10-27',NULL,'2026-10-20 13:29:03'),(134,8,31,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',326,331,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(135,8,31,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',327,331,'In Progress','Medium','2026-10-27',NULL,'2026-10-20 13:29:03'),(136,8,32,'Prepare project proposal','Create the initial proposal and agree on team responsibilities.',330,335,'Completed','Medium','2026-10-06','2026-10-10 15:29:03','2026-10-20 13:29:03'),(137,8,32,'Complete prototype checkpoint','Prepare the current prototype and document the completed work.',331,335,'In Progress','Medium','2026-10-27',NULL,'2026-10-20 13:29:03');
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_student`
--

DROP TABLE IF EXISTS `teacher_student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_student` (
  `teacher_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`teacher_id`,`student_id`),
  UNIQUE KEY `uq_teacher_student_student` (`student_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `teacher_student_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_student_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_student`
--

LOCK TABLES `teacher_student` WRITE;
/*!40000 ALTER TABLE `teacher_student` DISABLE KEYS */;
INSERT INTO `teacher_student` VALUES (1,1,'2026-10-20 13:29:02'),(1,2,'2026-10-20 13:29:02'),(1,3,'2026-10-20 13:29:02'),(1,4,'2026-10-20 13:29:02'),(1,182,'2026-10-20 13:29:02'),(1,183,'2026-10-20 13:29:02'),(1,184,'2026-10-20 13:29:02'),(1,185,'2026-10-20 13:29:02'),(1,186,'2026-10-20 13:29:02'),(1,187,'2026-10-20 13:29:02'),(1,188,'2026-10-20 13:29:02'),(1,189,'2026-10-20 13:29:02'),(1,190,'2026-10-20 13:29:02'),(1,191,'2026-10-20 13:29:02'),(1,192,'2026-10-20 13:29:02'),(1,193,'2026-10-20 13:29:02'),(1,194,'2026-10-20 13:29:02'),(1,195,'2026-10-20 13:29:02'),(1,196,'2026-10-20 13:29:02'),(1,197,'2026-10-20 13:29:02'),(1,198,'2026-10-20 13:29:02'),(1,199,'2026-10-20 13:29:02'),(1,200,'2026-10-20 13:29:02'),(1,201,'2026-10-20 13:29:02'),(1,242,'2026-10-20 13:29:02'),(1,243,'2026-10-20 13:29:02'),(1,244,'2026-10-20 13:29:02'),(1,245,'2026-10-20 13:29:02'),(1,246,'2026-10-20 13:29:02'),(1,247,'2026-10-20 13:29:02'),(1,248,'2026-10-20 13:29:02'),(1,249,'2026-10-20 13:29:02'),(1,250,'2026-10-20 13:29:02'),(1,251,'2026-10-20 13:29:02'),(1,252,'2026-10-20 13:29:02'),(1,253,'2026-10-20 13:29:02'),(1,254,'2026-10-20 13:29:02'),(1,255,'2026-10-20 13:29:02'),(1,256,'2026-10-20 13:29:02'),(1,257,'2026-10-20 13:29:02'),(1,258,'2026-10-20 13:29:02'),(1,259,'2026-10-20 13:29:02'),(1,260,'2026-10-20 13:29:02'),(1,261,'2026-10-20 13:29:02'),(1,262,'2026-10-20 13:29:02'),(1,263,'2026-10-20 13:29:02'),(1,264,'2026-10-20 13:29:02'),(1,265,'2026-10-20 13:29:02'),(1,266,'2026-10-20 13:29:02'),(1,267,'2026-10-20 13:29:02'),(1,268,'2026-10-20 13:29:02'),(1,269,'2026-10-20 13:29:02'),(1,270,'2026-10-20 13:29:02'),(1,271,'2026-10-20 13:29:02'),(1,272,'2026-10-20 13:29:02'),(1,273,'2026-10-20 13:29:02'),(1,274,'2026-10-20 13:29:02'),(1,275,'2026-10-20 13:29:02'),(1,276,'2026-10-20 13:29:02'),(1,277,'2026-10-20 13:29:02'),(2,202,'2026-10-20 13:29:02'),(2,203,'2026-10-20 13:29:02'),(2,204,'2026-10-20 13:29:02'),(2,205,'2026-10-20 13:29:02'),(2,206,'2026-10-20 13:29:02'),(2,207,'2026-10-20 13:29:02'),(2,208,'2026-10-20 13:29:02'),(2,209,'2026-10-20 13:29:02'),(2,210,'2026-10-20 13:29:02'),(2,211,'2026-10-20 13:29:02'),(2,212,'2026-10-20 13:29:02'),(2,213,'2026-10-20 13:29:02'),(2,214,'2026-10-20 13:29:02'),(2,215,'2026-10-20 13:29:02'),(2,216,'2026-10-20 13:29:02'),(2,217,'2026-10-20 13:29:02'),(2,218,'2026-10-20 13:29:02'),(2,219,'2026-10-20 13:29:02'),(2,220,'2026-10-20 13:29:02'),(2,221,'2026-10-20 13:29:02'),(2,278,'2026-10-20 13:29:02'),(2,279,'2026-10-20 13:29:02'),(2,280,'2026-10-20 13:29:02'),(2,281,'2026-10-20 13:29:02'),(2,282,'2026-10-20 13:29:02'),(2,283,'2026-10-20 13:29:02'),(2,284,'2026-10-20 13:29:02'),(2,285,'2026-10-20 13:29:02'),(2,286,'2026-10-20 13:29:02'),(2,287,'2026-10-20 13:29:02'),(2,288,'2026-10-20 13:29:02'),(2,289,'2026-10-20 13:29:02'),(2,290,'2026-10-20 13:29:02'),(2,291,'2026-10-20 13:29:02'),(2,292,'2026-10-20 13:29:02'),(2,293,'2026-10-20 13:29:02'),(2,294,'2026-10-20 13:29:02'),(2,295,'2026-10-20 13:29:02'),(2,296,'2026-10-20 13:29:02'),(2,297,'2026-10-20 13:29:02'),(3,222,'2026-10-20 13:29:02'),(3,223,'2026-10-20 13:29:02'),(3,224,'2026-10-20 13:29:02'),(3,225,'2026-10-20 13:29:02'),(3,226,'2026-10-20 13:29:02'),(3,227,'2026-10-20 13:29:02'),(3,228,'2026-10-20 13:29:02'),(3,229,'2026-10-20 13:29:02'),(3,230,'2026-10-20 13:29:02'),(3,231,'2026-10-20 13:29:02'),(3,232,'2026-10-20 13:29:02'),(3,233,'2026-10-20 13:29:02'),(3,234,'2026-10-20 13:29:02'),(3,235,'2026-10-20 13:29:02'),(3,236,'2026-10-20 13:29:02'),(3,237,'2026-10-20 13:29:02'),(3,238,'2026-10-20 13:29:02'),(3,239,'2026-10-20 13:29:02'),(3,240,'2026-10-20 13:29:02'),(3,241,'2026-10-20 13:29:02'),(4,298,'2026-10-20 13:29:02'),(4,299,'2026-10-20 13:29:02'),(4,300,'2026-10-20 13:29:02'),(4,301,'2026-10-20 13:29:02'),(4,302,'2026-10-20 13:29:02'),(4,303,'2026-10-20 13:29:02'),(4,304,'2026-10-20 13:29:02'),(4,305,'2026-10-20 13:29:02'),(4,306,'2026-10-20 13:29:02'),(4,307,'2026-10-20 13:29:02'),(4,308,'2026-10-20 13:29:02'),(4,309,'2026-10-20 13:29:02'),(4,310,'2026-10-20 13:29:02'),(4,311,'2026-10-20 13:29:02'),(4,312,'2026-10-20 13:29:02'),(4,313,'2026-10-20 13:29:02'),(4,314,'2026-10-20 13:29:03'),(4,315,'2026-10-20 13:29:03'),(4,316,'2026-10-20 13:29:03'),(4,317,'2026-10-20 13:29:03'),(5,318,'2026-10-20 13:29:03'),(5,319,'2026-10-20 13:29:03'),(5,320,'2026-10-20 13:29:03'),(5,321,'2026-10-20 13:29:03'),(5,322,'2026-10-20 13:29:03'),(5,323,'2026-10-20 13:29:03'),(5,324,'2026-10-20 13:29:03'),(5,325,'2026-10-20 13:29:03'),(5,326,'2026-10-20 13:29:03'),(5,327,'2026-10-20 13:29:03'),(5,328,'2026-10-20 13:29:03'),(5,329,'2026-10-20 13:29:03'),(5,330,'2026-10-20 13:29:03'),(5,331,'2026-10-20 13:29:03'),(5,332,'2026-10-20 13:29:03'),(5,333,'2026-10-20 13:29:03'),(5,334,'2026-10-20 13:29:03'),(5,335,'2026-10-20 13:29:03'),(5,336,'2026-10-20 13:29:03'),(5,337,'2026-10-20 13:29:03');
/*!40000 ALTER TABLE `teacher_student` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teachers`
--

DROP TABLE IF EXISTS `teachers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teachers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `teacher_code` varchar(6) NOT NULL,
  `hire_date` date DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `teacher_code` (`teacher_code`),
  CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teachers`
--

LOCK TABLES `teachers` WRITE;
/*!40000 ALTER TABLE `teachers` DISABLE KEYS */;
INSERT INTO `teachers` VALUES (1,1,'T48291','2022-09-01','Information Technology','Senior Lecturer'),(2,2,'T15942','2020-09-01','Information Technology','Lecturer'),(3,3,'T73658','2017-09-01','Computer Science','Associate Professor'),(4,4,'T20473','2021-09-01','Information Technology','Lecturer'),(5,5,'T91826','2018-09-01','Software Engineering','Senior Lecturer');
/*!40000 ALTER TABLE `teachers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('teacher','student','admin') NOT NULL,
  `last_active_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=343 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Esther Yuen','wyyuen@vtc.edu.hk','12345','teacher','2026-10-28 00:33:49','2026-10-07 11:11:58'),(2,'Dr. Alan Ho','alanho@vtc.edu.hk','12345','teacher','2026-10-20 18:36:02','2026-10-07 11:11:58'),(3,'Prof. Rebecca Lam','rebeccalam@vtc.edu.hk','12345','teacher',NULL,'2026-10-07 11:11:58'),(4,'Dr. Vincent Ng','vincentng@vtc.edu.hk','12345','teacher',NULL,'2026-10-07 11:11:58'),(5,'Ms. Karen Tsang','karentsang@vtc.edu.hk','12345','teacher','2026-10-20 15:08:34','2026-10-07 11:11:58'),(6,'CHAN Ho Yin','260222111@stu.vtc.edu.hk','12345','student','2026-10-20 15:43:11','2026-10-07 11:11:58'),(7,'CHUI Ka Chun','260222112@stu.vtc.edu.hk','12345','student','2026-10-19 12:52:27','2026-10-07 11:11:58'),(8,'LAI Ka-Yu Savio','260222113@stu.vtc.edu.hk','12345','student',NULL,'2026-10-07 11:11:58'),(9,'MIAO Tung','260222114@stu.vtc.edu.hk','12345','student',NULL,'2026-10-07 11:11:58'),(187,'AU Ka Chun','student1@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(188,'CHAN Wing Yan','student2@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(189,'CHENG Ho Yin','student3@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(190,'CHEUNG Mei Ling','student4@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(191,'CHIU Chun Hei','student5@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(192,'CHOW Ka Yan','student6@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(193,'CHU Hiu Tung','student7@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(194,'FUNG Sze Wing','student8@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(195,'HE Man Kit','student9@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(196,'HO Cheuk Yiu','student10@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(197,'HUANG Yuen Ting','student11@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(198,'HUI Tsz Hin','student12@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(199,'IP Ho Kwan','student13@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(200,'KWAN Wai Lam','student14@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(201,'KWOK Ching Man','student15@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(202,'LAI Ka Ho','student16@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(203,'LAM Wing Chi','student17@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(204,'LAU Chun Ming','student18@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(205,'LEE Mei Yi','student19@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(206,'LEUNG Tsz Ching','student20@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(207,'LI Chi Hang','student21@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(208,'LIN Man Yee','student22@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(209,'LO Ka Wai','student23@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(210,'LUO Ho Cheung','student24@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(211,'MAK Wing Lam','student25@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(212,'NG Ka Chun','student26@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(213,'NGAI Wing Yan','student27@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(214,'POON Ho Yin','student28@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(215,'QIU Mei Ling','student29@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(216,'SIU Chun Hei','student30@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(217,'SO Ka Yan','student31@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(218,'TAM Hiu Tung','student32@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(219,'TANG Sze Wing','student33@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(220,'TSE Man Kit','student34@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(221,'WONG Cheuk Yiu','student35@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(222,'WU Yuen Ting','student36@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(223,'XU Tsz Hin','student37@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(224,'YAU Ho Kwan','student38@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(225,'YIP Wai Lam','student39@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(226,'YU Ching Man','student40@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(227,'AU Ka Ho','student41@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(228,'CHAN Wing Chi','student42@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(229,'CHENG Chun Ming','student43@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(230,'CHEUNG Mei Yi','student44@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(231,'CHIU Tsz Ching','student45@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(232,'CHOW Chi Hang','student46@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(233,'CHU Man Yee','student47@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(234,'FUNG Ka Wai','student48@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(235,'HE Ho Cheung','student49@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(236,'HO Wing Lam','student50@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(237,'HUANG Ka Chun','student51@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(238,'HUI Wing Yan','student52@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(239,'IP Ho Yin','student53@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(240,'KWAN Mei Ling','student54@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(241,'KWOK Chun Hei','student55@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(242,'LAI Ka Yan','student56@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(243,'LAM Hiu Tung','student57@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(244,'LAU Sze Wing','student58@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(245,'LEE Man Kit','student59@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(246,'LEUNG Cheuk Yiu','student60@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(247,'LI Yuen Ting','student61@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(248,'LIN Tsz Hin','student62@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(249,'LO Ho Kwan','student63@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(250,'LUO Wai Lam','student64@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(251,'MAK Ching Man','student65@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(252,'NG Ka Ho','student66@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(253,'NGAI Wing Chi','student67@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(254,'POON Chun Ming','student68@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(255,'QIU Mei Yi','student69@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(256,'SIU Tsz Ching','student70@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(257,'SO Chi Hang','student71@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(258,'TAM Man Yee','student72@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(259,'TANG Ka Wai','student73@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(260,'TSE Ho Cheung','student74@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(261,'WONG Wing Lam','student75@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(262,'WU Ka Chun','student76@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(263,'XU Wing Yan','student77@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(264,'YAU Ho Yin','student78@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(265,'YIP Mei Ling','student79@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(266,'YU Chun Hei','student80@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(267,'AU Ka Yan','student81@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(268,'CHAN Hiu Tung','student82@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(269,'CHENG Sze Wing','student83@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(270,'CHEUNG Man Kit','student84@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(271,'CHIU Cheuk Yiu','student85@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(272,'CHOW Yuen Ting','student86@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(273,'CHU Tsz Hin','student87@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(274,'FUNG Ho Kwan','student88@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(275,'HE Wai Lam','student89@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(276,'HO Ching Man','student90@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(277,'HUANG Ka Ho','student91@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(278,'HUI Wing Chi','student92@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(279,'IP Chun Ming','student93@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(280,'KWAN Mei Yi','student94@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(281,'KWOK Tsz Ching','student95@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(282,'LAI Chi Hang','student96@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(283,'LAM Man Yee','student97@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(284,'LAU Ka Wai','student98@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(285,'LEE Ho Cheung','student99@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(286,'LEUNG Wing Lam','student100@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(287,'LI Ka Chun','student101@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(288,'LIN Wing Yan','student102@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(289,'LO Ho Yin','student103@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(290,'LUO Mei Ling','student104@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(291,'MAK Chun Hei','student105@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(292,'NG Ka Yan','student106@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(293,'NGAI Hiu Tung','student107@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(294,'POON Sze Wing','student108@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(295,'QIU Man Kit','student109@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(296,'SIU Cheuk Yiu','student110@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(297,'SO Yuen Ting','student111@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(298,'TAM Tsz Hin','student112@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(299,'TANG Ho Kwan','student113@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(300,'TSE Wai Lam','student114@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(301,'WONG Ching Man','student115@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(302,'WU Ka Ho','student116@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(303,'XU Wing Chi','student117@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(304,'YAU Chun Ming','student118@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(305,'YIP Mei Yi','student119@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(306,'YU Tsz Ching','student120@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(307,'AU Chi Hang','student121@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(308,'CHAN Man Yee','student122@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(309,'CHENG Ka Wai','student123@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(310,'CHEUNG Ho Cheung','student124@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(311,'CHIU Wing Lam','student125@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(312,'CHOW Ka Chun','student126@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(313,'CHU Wing Yan','student127@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(314,'FUNG Ho Yin','student128@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(315,'HE Mei Ling','student129@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(316,'HO Chun Hei','student130@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(317,'HUANG Ka Yan','student131@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(318,'HUI Hiu Tung','student132@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(319,'IP Sze Wing','student133@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(320,'KWAN Man Kit','student134@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(321,'KWOK Cheuk Yiu','student135@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(322,'LAI Yuen Ting','student136@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(323,'LAM Tsz Hin','student137@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(324,'LAU Ho Kwan','student138@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(325,'LEE Wai Lam','student139@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(326,'LEUNG Ching Man','student140@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(327,'LI Ka Ho','student141@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(328,'LIN Wing Chi','student142@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(329,'LO Chun Ming','student143@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(330,'LUO Mei Yi','student144@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(331,'MAK Tsz Ching','student145@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(332,'NG Chi Hang','student146@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(333,'NGAI Man Yee','student147@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(334,'POON Ka Wai','student148@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(335,'QIU Ho Cheung','student149@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(336,'SIU Wing Lam','student150@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(337,'SO Ka Chun','student151@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(338,'TAM Wing Yan','student152@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(339,'TANG Ho Yin','student153@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(340,'TSE Mei Ling','student154@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(341,'WONG Chun Hei','student155@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02'),(342,'WU Ka Yan','student156@student.teambox.local','12345','student',NULL,'2026-10-20 13:29:02');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'teambox'
--

--
-- Dumping routines for database 'teambox'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-28  1:27:14
