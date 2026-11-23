-- Lost & Found - importable database (SWE 322 project)
-- phpMyAdmin: Import this file, or simply open sql/setup.php in the browser.
-- Contains 4 tables: Student, Category, ItemReports, Claims (5+ rows each).
-- Demo logins: admin / Admin@1234   and   sara_ali / Student@123

/*M!999999\- enable the sandbox mode */ 
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
CREATE DATABASE /*!32312 IF NOT EXISTS*/ `yu_lost_found` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `yu_lost_found`;
DROP TABLE IF EXISTS `Category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(50) NOT NULL,
  `icon` varchar(10) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40000 ALTER TABLE `Category` DISABLE KEYS */;
INSERT INTO `Category` VALUES (1,'Electronics','🎧');
INSERT INTO `Category` VALUES (2,'Books & Notes','📘');
INSERT INTO `Category` VALUES (3,'Accessories','🔑');
INSERT INTO `Category` VALUES (4,'Clothing','🧥');
INSERT INTO `Category` VALUES (5,'Cards & Documents','💳');
INSERT INTO `Category` VALUES (6,'Other','📦');
/*!40000 ALTER TABLE `Category` ENABLE KEYS */;
DROP TABLE IF EXISTS `Claims`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Claims` (
  `ClaimID` int(11) NOT NULL AUTO_INCREMENT,
  `ReportID` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `Message` text NOT NULL,
  `ClaimStatus` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ClaimID`),
  UNIQUE KEY `one_claim_per_student` (`ReportID`,`user_id`),
  KEY `fk_claims_user` (`user_id`),
  CONSTRAINT `fk_claims_report` FOREIGN KEY (`ReportID`) REFERENCES `ItemReports` (`ReportID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_claims_user` FOREIGN KEY (`user_id`) REFERENCES `Student` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40000 ALTER TABLE `Claims` DISABLE KEYS */;
INSERT INTO `Claims` VALUES (1,2,2210001,'These look like my keys - the red tag has my initials.','pending','2026-11-23 14:33:41');
INSERT INTO `Claims` VALUES (2,4,2210005,'This is my ID card, I lost it on Sunday.','pending','2026-11-23 14:33:41');
INSERT INTO `Claims` VALUES (3,6,2210004,'The umbrella is mine, I left it at the north gate after class.','approved','2026-11-23 14:33:41');
INSERT INTO `Claims` VALUES (4,1,2210002,'I think I saw headphones like these in the library on Tuesday.','pending','2026-11-23 14:33:41');
INSERT INTO `Claims` VALUES (5,5,2210003,'I believe I saw this jacket in the cafeteria lost-property box.','rejected','2026-11-23 14:33:41');
/*!40000 ALTER TABLE `Claims` ENABLE KEYS */;
DROP TABLE IF EXISTS `ItemReports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ItemReports` (
  `ReportID` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `Title` varchar(100) NOT NULL,
  `Location` varchar(120) NOT NULL,
  `ItemDate` date NOT NULL,
  `Description` text NOT NULL,
  `Contact` varchar(120) DEFAULT NULL,
  `Photo` longblob DEFAULT NULL,
  `PhotoMime` varchar(50) DEFAULT NULL,
  `Status` enum('lost','found') NOT NULL,
  `ReportState` enum('open','returned') NOT NULL DEFAULT 'open',
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ReportID`),
  KEY `fk_reports_user` (`user_id`),
  KEY `fk_reports_category` (`category_id`),
  CONSTRAINT `fk_reports_category` FOREIGN KEY (`category_id`) REFERENCES `Category` (`category_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_reports_user` FOREIGN KEY (`user_id`) REFERENCES `Student` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40000 ALTER TABLE `ItemReports` DISABLE KEYS */;
INSERT INTO `ItemReports` VALUES (1,2210001,1,'Wireless headphones','Main Library','2026-11-18','Black wireless headphones, possibly left on a study desk in the library.','sara@example.com',NULL,NULL,'lost','open','2026-11-18 12:00:00');
INSERT INTO `ItemReports` VALUES (2,2210002,3,'Set of keys','Main Entrance','2026-11-17','A set of keys with a red tag, found near the main entrance.','omar@example.com',NULL,NULL,'found','open','2026-11-17 12:00:00');
INSERT INTO `ItemReports` VALUES (3,2210003,2,'Blue notebook','Engineering Building','2026-11-16','Medium-sized blue notebook with lecture notes inside.','laila@example.com',NULL,NULL,'lost','returned','2026-11-16 12:00:00');
INSERT INTO `ItemReports` VALUES (4,2210004,5,'ID card','Hall B','2026-11-15','ID card found on the floor outside hall B.','yousef@example.com',NULL,NULL,'found','open','2026-11-15 12:00:00');
INSERT INTO `ItemReports` VALUES (5,2210005,4,'Grey jacket','Cafeteria','2026-11-14','Light grey zip jacket left on a chair in the cafeteria.','noor@example.com',NULL,NULL,'lost','open','2026-11-14 12:00:00');
INSERT INTO `ItemReports` VALUES (6,2210002,6,'Black umbrella','North Gate','2026-11-13','Black folding umbrella found near the north parking entrance.','omar@example.com',NULL,NULL,'found','returned','2026-11-13 12:00:00');
INSERT INTO `ItemReports` VALUES (7,2210001,6,'Water bottle','Gym entrance','2026-11-12','Steel water bottle with a green cap found by the gym entrance.','sara@example.com',NULL,NULL,'found','open','2026-11-12 12:00:00');
INSERT INTO `ItemReports` VALUES (8,2210003,1,'Scientific calculator','Room B204','2026-11-10','Casio fx-991 calculator with a name sticker on the back, lost after an exam.','laila@example.com',NULL,NULL,'lost','open','2026-11-10 12:00:00');
/*!40000 ALTER TABLE `ItemReports` ENABLE KEYS */;
DROP TABLE IF EXISTS `Student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Student` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('student','admin') NOT NULL DEFAULT 'student',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_name` (`user_name`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40000 ALTER TABLE `Student` DISABLE KEYS */;
INSERT INTO `Student` VALUES (1000,'admin','$2y$10$ee.W63NO4nHlTHYiZ2CgPuRxuj92ZtTtyR5di56foX7j1nGU7Qi7q','admin@example.com','admin','2026-11-23 14:33:40');
INSERT INTO `Student` VALUES (2210001,'sara_ali','$2y$10$bz44zrH.IXR9brhG30AyieT7eOexG0IOKvbMHxSy3cHvpiU.jDAty','sara@example.com','student','2026-11-23 14:33:40');
INSERT INTO `Student` VALUES (2210002,'omar_k','$2y$10$/0fMGaOkr8tpbcSgwmx/0e9TKAKa2/vKTDctqemsJqedR.xAa0Lqm','omar@example.com','student','2026-11-23 14:33:40');
INSERT INTO `Student` VALUES (2210003,'laila_h','$2y$10$dq.6s5WOt5F8Hbww2Xo5M.ufLHhYR.2JpdVC6o/Fe8HJWKGsZwXle','laila@example.com','student','2026-11-23 14:33:41');
INSERT INTO `Student` VALUES (2210004,'yousef_m','$2y$10$arjUqr2tmlTODzColUXD1.BgUuAb/2tf4WykWMBgsBGVPRQzqWH/a','yousef@example.com','student','2026-11-23 14:33:41');
INSERT INTO `Student` VALUES (2210005,'noor_s','$2y$10$icH//JFGuab0yztrj7pF0.Al686cwt.ktRy.fQyU1qykX2SjrKA9O','noor@example.com','student','2026-11-23 14:33:41');
/*!40000 ALTER TABLE `Student` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
