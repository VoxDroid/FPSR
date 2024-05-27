-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: event_management_system
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

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
-- Table structure for table `comment_votes`
--

DROP TABLE IF EXISTS `comment_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comment_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `vote_type` enum('like','dislike') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_comment_unique` (`user_id`,`event_id`,`comment_id`),
  KEY `event_id` (`event_id`),
  KEY `comment_id` (`comment_id`),
  CONSTRAINT `comment_votes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comment_votes_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comment_votes_ibfk_3` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comment_votes`
--

LOCK TABLES `comment_votes` WRITE;
/*!40000 ALTER TABLE `comment_votes` DISABLE KEYS */;
INSERT INTO `comment_votes` VALUES (1,1,19,2,'like'),(2,1,2,3,'dislike');
/*!40000 ALTER TABLE `comment_votes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `likes` int(11) DEFAULT 0,
  `dislikes` int(11) DEFAULT 0,
  `date_commented` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
INSERT INTO `comments` VALUES (2,19,1,'test',0,0,'2024-05-27 07:06:15'),(3,2,1,'fhj',0,0,'2024-05-27 07:08:27');
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_votes`
--

DROP TABLE IF EXISTS `event_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `vote_type` enum('like','dislike') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_event_unique` (`user_id`,`event_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `event_votes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_votes_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_votes`
--

LOCK TABLES `event_votes` WRITE;
/*!40000 ALTER TABLE `event_votes` DISABLE KEYS */;
INSERT INTO `event_votes` VALUES (4,1,19,'like');
/*!40000 ALTER TABLE `event_votes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `facility` varchar(100) NOT NULL,
  `duration` int(11) NOT NULL,
  `status` enum('pending','active','denied','ongoing','completed') NOT NULL,
  `date_requested` timestamp NOT NULL DEFAULT current_timestamp(),
  `event_start` datetime DEFAULT NULL,
  `event_end` datetime DEFAULT NULL,
  `likes` int(11) DEFAULT 0,
  `dislikes` int(11) DEFAULT 0,
  `remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=153 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (1,1,'Event 1','Description for Event 1','Facility A',3,'completed','2024-05-26 02:56:01','2024-05-09 09:00:00','2024-05-09 12:00:00',10,5,NULL),(2,1,'Event 2','Description for Event 2','Facility B',2,'active','2024-05-26 02:56:01','2024-05-10 10:00:00','2024-05-10 12:00:00',15,8,NULL),(3,2,'Event 3','Description for Event 3','Facility C',4,'denied','2024-05-26 02:56:01','2024-05-11 08:00:00','2024-05-11 12:00:00',8,3,NULL),(4,2,'Event 4','Description for Event 4','Facility D',5,'completed','2024-05-26 02:56:01','2024-05-12 10:00:00','2024-05-12 15:00:00',20,10,NULL),(5,1,'Event 5','Description for Event 5','Facility E',2,'','2024-05-26 02:56:01','2024-05-13 09:00:00','2024-05-13 11:00:00',12,6,NULL),(6,2,'Event 6','Description for Event 6','Facility F',3,'completed','2024-05-26 02:56:01','2024-05-14 11:00:00','2024-05-14 14:00:00',18,9,NULL),(7,1,'Event 7','Description for Event 7','Facility G',4,'active','2024-05-26 02:56:01','2024-05-15 08:00:00','2024-05-15 12:00:00',25,12,NULL),(8,2,'Event 8','Description for Event 8','Facility H',3,'denied','2024-05-26 02:56:01','2024-05-16 10:00:00','2024-05-16 13:00:00',9,4,NULL),(9,1,'Event 9','Description for Event 9','Facility I',2,'completed','2024-05-26 02:56:01','2024-05-17 09:00:00','2024-05-17 11:00:00',14,7,NULL),(10,2,'Event 10','Description for Event 10','Facility J',5,'','2024-05-26 02:56:01','2024-05-18 11:00:00','2024-05-18 16:00:00',22,11,NULL),(11,1,'Event 11','Description for Event 11','Facility K',3,'completed','2024-05-26 02:56:01','2024-05-19 08:00:00','2024-05-19 11:00:00',30,15,NULL),(12,2,'Event 12','Description for Event 12','Facility L',4,'active','2024-05-26 02:56:01','2024-05-20 10:00:00','2024-05-20 14:00:00',35,18,NULL),(13,1,'Event 13','Description for Event 13','Facility M',2,'denied','2024-05-26 02:56:01','2024-05-21 09:00:00','2024-05-21 11:00:00',10,5,NULL),(14,2,'Event 14','Description for Event 14','Facility N',3,'completed','2024-05-26 02:56:01','2024-05-22 11:00:00','2024-05-22 14:00:00',40,20,NULL),(15,1,'Event 15','Description for Event 15','Facility O',5,'','2024-05-26 02:56:01','2024-05-23 08:00:00','2024-05-23 13:00:00',18,9,NULL),(16,2,'Event 16','Description for Event 16','Facility P',4,'completed','2024-05-26 02:56:01','2024-05-24 10:00:00','2024-05-24 14:00:00',45,22,NULL),(17,1,'Event 17','Description for Event 17','Facility Q',3,'active','2024-05-26 02:56:01','2024-05-25 09:00:00','2024-05-25 12:00:00',50,25,NULL),(18,2,'Event 18','Description for Event 18','Facility R',2,'denied','2024-05-26 02:56:01','2024-05-26 11:00:00','2024-05-26 13:00:00',15,7,NULL),(19,1,'Event 19','Description for Event 19','Facility S',3,'ongoing','2024-05-26 02:56:01','2024-05-27 08:00:00','2024-05-27 11:00:00',61,30,NULL),(21,1,'Event 21','Description for Event 21','Facility U',2,'completed','2024-05-26 02:56:01','2024-05-29 09:00:00','2024-05-29 11:00:00',70,35,NULL),(22,2,'Event 22','Description for Event 22','Facility V',3,'active','2024-05-26 02:56:01','2024-05-30 11:00:00','2024-05-30 14:00:00',30,15,NULL),(23,1,'Event 23','Description for Event 23','Facility W',4,'denied','2024-05-26 02:56:01','2024-05-31 08:00:00','2024-05-31 12:00:00',35,18,NULL),(24,2,'Event 24','Description for Event 24','Facility X',5,'ongoing','2024-05-26 02:56:01','2024-06-01 10:00:00','2024-06-01 15:00:00',40,20,NULL),(25,1,'Event 25','Description for Event 25','Facility Y',2,'','2024-05-26 02:56:01','2024-06-02 09:00:00','2024-06-02 11:00:00',45,22,NULL),(26,2,'Event 26','Description for Event 26','Facility Z',3,'completed','2024-05-26 02:56:01','2024-06-03 11:00:00','2024-06-03 14:00:00',50,25,NULL),(27,1,'Event 27','Description for Event 27','Facility AA',4,'active','2024-05-26 02:56:01','2024-06-04 08:00:00','2024-06-04 12:00:00',55,27,NULL),(28,2,'Event 28','Description for Event 28','Facility BB',3,'denied','2024-05-26 02:56:01','2024-06-05 10:00:00','2024-06-05 13:00:00',60,30,NULL),(29,1,'Event 29','Description for Event 29','Facility CC',2,'ongoing','2024-05-26 02:56:01','2024-06-06 09:00:00','2024-06-06 11:00:00',65,32,NULL),(30,2,'Event 30','Description for Event 30','Facility DD',5,'','2024-05-26 02:56:01','2024-06-07 11:00:00','2024-06-07 16:00:00',70,35,NULL),(31,1,'Event 1','Description for Event 1','Facility A',3,'completed','2024-05-26 02:56:01','2024-05-09 09:00:00','2024-05-09 12:00:00',10,5,NULL),(32,1,'Event 2','Description for Event 2','Facility B',2,'active','2024-05-26 02:56:01','2024-05-10 10:00:00','2024-05-10 12:00:00',15,8,NULL),(33,2,'Event 3','Description for Event 3','Facility C',4,'denied','2024-05-26 02:56:01','2024-05-11 08:00:00','2024-05-11 12:00:00',8,3,NULL),(34,2,'Event 4','Description for Event 4','Facility D',5,'completed','2024-05-26 02:56:01','2024-05-12 10:00:00','2024-05-12 15:00:00',20,10,NULL),(35,1,'Event 5','Description for Event 5','Facility E',2,'denied','2024-05-26 02:56:01','2024-05-13 09:00:00','2024-05-13 11:00:00',12,6,'test'),(36,2,'Event 6','Description for Event 6','Facility F',3,'completed','2024-05-26 02:56:01','2024-05-14 11:00:00','2024-05-14 14:00:00',18,9,NULL),(37,1,'Event 7','Description for Event 7','Facility G',4,'active','2024-05-26 02:56:01','2024-05-15 08:00:00','2024-05-15 12:00:00',25,12,NULL),(38,2,'Event 8','Description for Event 8','Facility H',3,'denied','2024-05-26 02:56:01','2024-05-16 10:00:00','2024-05-16 13:00:00',9,4,NULL),(39,1,'Event 9','Description for Event 9','Facility I',2,'completed','2024-05-26 02:56:01','2024-05-17 09:00:00','2024-05-17 11:00:00',14,7,NULL),(40,2,'Event 10','Description for Event 10','Facility J',5,'active','2024-05-26 02:56:01','2024-05-18 11:00:00','2024-05-18 16:00:00',22,11,'approveda'),(41,1,'Event 11','Description for Event 11','Facility K',3,'completed','2024-05-26 02:56:01','2024-05-19 08:00:00','2024-05-19 11:00:00',30,15,NULL),(42,2,'Event 12','Description for Event 12','Facility L',4,'active','2024-05-26 02:56:01','2024-05-20 10:00:00','2024-05-20 14:00:00',35,18,NULL),(43,1,'Event 13','Description for Event 13','Facility M',2,'denied','2024-05-26 02:56:01','2024-05-21 09:00:00','2024-05-21 11:00:00',10,5,NULL),(44,2,'Event 14','Description for Event 14','Facility N',3,'completed','2024-05-26 02:56:01','2024-05-22 11:00:00','2024-05-22 14:00:00',40,20,NULL),(46,2,'Event 16','Description for Event 16','Facility P',4,'completed','2024-05-26 02:56:01','2024-05-24 10:00:00','2024-05-24 14:00:00',45,22,NULL),(47,1,'Event 17','Description for Event 17','Facility Q',3,'active','2024-05-26 02:56:01','2024-05-25 09:00:00','2024-05-25 12:00:00',50,25,NULL),(48,2,'Event 18','Description for Event 18','Facility R',2,'denied','2024-05-26 02:56:01','2024-05-26 11:00:00','2024-05-26 13:00:00',15,7,NULL),(49,1,'Event 19','Description for Event 19','Facility S',3,'ongoing','2024-05-26 02:56:01','2024-05-27 08:00:00','2024-05-27 11:00:00',60,30,NULL),(50,2,'Event 20','Description for Event 20','Facility T',4,'denied','2024-05-26 02:56:01','2024-05-28 10:00:00','2024-05-28 14:00:00',25,12,'nah'),(51,1,'Event 21','Description for Event 21','Facility U',2,'completed','2024-05-26 02:56:01','2024-05-29 09:00:00','2024-05-29 11:00:00',70,35,NULL),(52,2,'Event 22','Description for Event 22','Facility V',3,'active','2024-05-26 02:56:01','2024-05-30 11:00:00','2024-05-30 14:00:00',30,15,NULL),(53,1,'Event 23','Description for Event 23','Facility W',4,'denied','2024-05-26 02:56:01','2024-05-31 08:00:00','2024-05-31 12:00:00',35,18,NULL),(54,2,'Event 24','Description for Event 24','Facility X',5,'ongoing','2024-05-26 02:56:01','2024-06-01 10:00:00','2024-06-01 15:00:00',40,20,NULL),(55,1,'Event 25','Description for Event 25','Facility Y',2,'denied','2024-05-26 02:56:01','2024-06-02 09:00:00','2024-06-02 11:00:00',45,22,''),(56,2,'Event 26','Description for Event 26','Facility Z',3,'completed','2024-05-26 02:56:01','2024-06-03 11:00:00','2024-06-03 14:00:00',50,25,NULL),(57,1,'Event 27','Description for Event 27','Facility AA',4,'active','2024-05-26 02:56:01','2024-06-04 08:00:00','2024-06-04 12:00:00',55,27,NULL),(58,2,'Event 28','Description for Event 28','Facility BB',3,'denied','2024-05-26 02:56:01','2024-06-05 10:00:00','2024-06-05 13:00:00',60,30,NULL),(59,1,'Event 29','Description for Event 29','Facility CC',2,'ongoing','2024-05-26 02:56:01','2024-06-06 09:00:00','2024-06-06 11:00:00',65,32,NULL),(61,1,'Event 1','Description for Event 1','Facility A',3,'completed','2024-05-26 02:56:02','2024-05-09 09:00:00','2024-05-09 12:00:00',10,5,NULL),(62,1,'Event 2','Description for Event 2','Facility B',2,'active','2024-05-26 02:56:02','2024-05-10 10:00:00','2024-05-10 12:00:00',15,8,NULL),(63,2,'Event 3','Description for Event 3','Facility C',4,'denied','2024-05-26 02:56:02','2024-05-11 08:00:00','2024-05-11 12:00:00',8,3,NULL),(64,2,'Event 4','Description for Event 4','Facility D',5,'completed','2024-05-26 02:56:02','2024-05-12 10:00:00','2024-05-12 15:00:00',20,10,NULL),(66,2,'Event 6','Description for Event 6','Facility F',3,'completed','2024-05-26 02:56:02','2024-05-14 11:00:00','2024-05-14 14:00:00',18,9,NULL),(67,1,'Event 7','Description for Event 7','Facility G',4,'active','2024-05-26 02:56:02','2024-05-15 08:00:00','2024-05-15 12:00:00',25,12,NULL),(68,2,'Event 8','Description for Event 8','Facility H',3,'denied','2024-05-26 02:56:02','2024-05-16 10:00:00','2024-05-16 13:00:00',9,4,NULL),(69,1,'Event 9','Description for Event 9','Facility I',2,'completed','2024-05-26 02:56:02','2024-05-17 09:00:00','2024-05-17 11:00:00',14,7,NULL),(71,1,'Event 11','Description for Event 11','Facility K',3,'completed','2024-05-26 02:56:02','2024-05-19 08:00:00','2024-05-19 11:00:00',30,15,NULL),(72,2,'Event 12','Description for Event 12','Facility L',4,'active','2024-05-26 02:56:02','2024-05-20 10:00:00','2024-05-20 14:00:00',35,18,NULL),(73,1,'Event 13','Description for Event 13','Facility M',2,'denied','2024-05-26 02:56:02','2024-05-21 09:00:00','2024-05-21 11:00:00',10,5,NULL),(74,2,'Event 14','Description for Event 14','Facility N',3,'completed','2024-05-26 02:56:02','2024-05-22 11:00:00','2024-05-22 14:00:00',40,20,NULL),(75,1,'Event 15','Description for Event 15','Facility O',5,'pending','2024-05-26 02:56:02','2024-05-23 08:00:00','2024-05-23 13:00:00',18,9,NULL),(76,2,'Event 16','Description for Event 16','Facility P',4,'completed','2024-05-26 02:56:02','2024-05-24 10:00:00','2024-05-24 14:00:00',45,22,NULL),(77,1,'Event 17','Description for Event 17','Facility Q',3,'active','2024-05-26 02:56:02','2024-05-25 09:00:00','2024-05-25 12:00:00',50,25,NULL),(78,2,'Event 18','Description for Event 18','Facility R',2,'denied','2024-05-26 02:56:02','2024-05-26 11:00:00','2024-05-26 13:00:00',15,7,NULL),(79,1,'Event 19','Description for Event 19','Facility S',3,'ongoing','2024-05-26 02:56:02','2024-05-27 08:00:00','2024-05-27 11:00:00',60,30,NULL),(80,2,'Event 20','Description for Event 20','Facility T',4,'pending','2024-05-26 02:56:02','2024-05-28 10:00:00','2024-05-28 14:00:00',25,12,NULL),(81,1,'Event 21','Description for Event 21','Facility U',2,'completed','2024-05-26 02:56:02','2024-05-29 09:00:00','2024-05-29 11:00:00',70,35,NULL),(82,2,'Event 22','Description for Event 22','Facility V',3,'active','2024-05-26 02:56:02','2024-05-30 11:00:00','2024-05-30 14:00:00',30,15,NULL),(83,1,'Event 23','Description for Event 23','Facility W',4,'denied','2024-05-26 02:56:02','2024-05-31 08:00:00','2024-05-31 12:00:00',35,18,NULL),(84,2,'Event 24','Description for Event 24','Facility X',5,'ongoing','2024-05-26 02:56:02','2024-06-01 10:00:00','2024-06-01 15:00:00',40,20,NULL),(85,1,'Event 25','Description for Event 25','Facility Y',2,'pending','2024-05-26 02:56:02','2024-06-02 09:00:00','2024-06-02 11:00:00',45,22,NULL),(86,2,'Event 26','Description for Event 26','Facility Z',3,'completed','2024-05-26 02:56:02','2024-06-03 11:00:00','2024-06-03 14:00:00',50,25,NULL),(87,1,'Event 27','Description for Event 27','Facility AA',4,'active','2024-05-26 02:56:02','2024-06-04 08:00:00','2024-06-04 12:00:00',55,27,NULL),(88,2,'Event 28','Description for Event 28','Facility BB',3,'denied','2024-05-26 02:56:02','2024-06-05 10:00:00','2024-06-05 13:00:00',60,30,NULL),(89,1,'Event 29','Description for Event 29','Facility CC',2,'ongoing','2024-05-26 02:56:02','2024-06-06 09:00:00','2024-06-06 11:00:00',65,32,NULL),(90,2,'Event 30','Description for Event 30','Facility DD',5,'pending','2024-05-26 02:56:02','2024-06-07 11:00:00','2024-06-07 16:00:00',70,35,NULL),(91,1,'Event 1','Description for Event 1','Facility A',3,'completed','2024-05-26 02:56:02','2024-05-09 09:00:00','2024-05-09 12:00:00',10,5,NULL),(92,1,'Event 2','Description for Event 2','Facility B',2,'active','2024-05-26 02:56:02','2024-05-10 10:00:00','2024-05-10 12:00:00',15,8,NULL),(93,2,'Event 3','Description for Event 3','Facility C',4,'denied','2024-05-26 02:56:02','2024-05-11 08:00:00','2024-05-11 12:00:00',8,3,NULL),(94,2,'Event 4','Description for Event 4','Facility D',5,'completed','2024-05-26 02:56:02','2024-05-12 10:00:00','2024-05-12 15:00:00',20,10,NULL),(95,1,'Event 5','Description for Event 5','Facility E',2,'pending','2024-05-26 02:56:02','2024-05-13 09:00:00','2024-05-13 11:00:00',12,6,NULL),(96,2,'Event 6','Description for Event 6','Facility F',3,'completed','2024-05-26 02:56:02','2024-05-14 11:00:00','2024-05-14 14:00:00',18,9,NULL),(97,1,'Event 7','Description for Event 7','Facility G',4,'active','2024-05-26 02:56:02','2024-05-15 08:00:00','2024-05-15 12:00:00',25,12,NULL),(98,2,'Event 8','Description for Event 8','Facility H',3,'denied','2024-05-26 02:56:02','2024-05-16 10:00:00','2024-05-16 13:00:00',9,4,NULL),(99,1,'Event 9','Description for Event 9','Facility I',2,'completed','2024-05-26 02:56:02','2024-05-17 09:00:00','2024-05-17 11:00:00',14,7,NULL),(100,2,'Event 10','Description for Event 10','Facility J',5,'pending','2024-05-26 02:56:02','2024-05-18 11:00:00','2024-05-18 16:00:00',22,11,NULL),(101,1,'Event 11','Description for Event 11','Facility K',3,'completed','2024-05-26 02:56:02','2024-05-19 08:00:00','2024-05-19 11:00:00',30,15,NULL),(102,2,'Event 12','Description for Event 12','Facility L',4,'active','2024-05-26 02:56:02','2024-05-20 10:00:00','2024-05-20 14:00:00',35,18,NULL),(103,1,'Event 13','Description for Event 13','Facility M',2,'denied','2024-05-26 02:56:02','2024-05-21 09:00:00','2024-05-21 11:00:00',10,5,NULL),(104,2,'Event 14','Description for Event 14','Facility N',3,'completed','2024-05-26 02:56:02','2024-05-22 11:00:00','2024-05-22 14:00:00',40,20,NULL),(105,1,'Event 15','Description for Event 15','Facility O',5,'pending','2024-05-26 02:56:02','2024-05-23 08:00:00','2024-05-23 13:00:00',18,9,NULL),(106,2,'Event 16','Description for Event 16','Facility P',4,'completed','2024-05-26 02:56:02','2024-05-24 10:00:00','2024-05-24 14:00:00',45,22,NULL),(107,1,'Event 17','Description for Event 17','Facility Q',3,'active','2024-05-26 02:56:02','2024-05-25 09:00:00','2024-05-25 12:00:00',50,25,NULL),(108,2,'Event 18','Description for Event 18','Facility R',2,'denied','2024-05-26 02:56:02','2024-05-26 11:00:00','2024-05-26 13:00:00',15,7,NULL),(109,1,'Event 19','Description for Event 19','Facility S',3,'ongoing','2024-05-26 02:56:02','2024-05-27 08:00:00','2024-05-27 11:00:00',60,30,NULL),(110,2,'Event 20','Description for Event 20','Facility T',4,'pending','2024-05-26 02:56:02','2024-05-28 10:00:00','2024-05-28 14:00:00',25,12,NULL),(111,1,'Event 21','Description for Event 21','Facility U',2,'completed','2024-05-26 02:56:02','2024-05-29 09:00:00','2024-05-29 11:00:00',70,35,NULL),(112,2,'Event 22','Description for Event 22','Facility V',3,'active','2024-05-26 02:56:02','2024-05-30 11:00:00','2024-05-30 14:00:00',30,15,NULL),(113,1,'Event 23','Description for Event 23','Facility W',4,'denied','2024-05-26 02:56:02','2024-05-31 08:00:00','2024-05-31 12:00:00',35,18,NULL),(114,2,'Event 24','Description for Event 24','Facility X',5,'ongoing','2024-05-26 02:56:02','2024-06-01 10:00:00','2024-06-01 15:00:00',40,20,NULL),(115,1,'Event 25','Description for Event 25','Facility Y',2,'pending','2024-05-26 02:56:02','2024-06-02 09:00:00','2024-06-02 11:00:00',45,22,NULL),(116,2,'Event 26','Description for Event 26','Facility Z',3,'completed','2024-05-26 02:56:02','2024-06-03 11:00:00','2024-06-03 14:00:00',50,25,NULL),(117,1,'Event 27','Description for Event 27','Facility AA',4,'active','2024-05-26 02:56:02','2024-06-04 08:00:00','2024-06-04 12:00:00',55,27,NULL),(118,2,'Event 28','Description for Event 28','Facility BB',3,'denied','2024-05-26 02:56:02','2024-06-05 10:00:00','2024-06-05 13:00:00',60,30,NULL),(119,1,'Event 29','Description for Event 29','Facility CC',2,'ongoing','2024-05-26 02:56:02','2024-06-06 09:00:00','2024-06-06 11:00:00',65,32,NULL),(120,2,'Event 30','Description for Event 30','Facility DD',5,'pending','2024-05-26 02:56:02','2024-06-07 11:00:00','2024-06-07 16:00:00',70,35,NULL),(121,1,'Event 1','Description for Event 1','Facility A',3,'completed','2024-05-26 02:56:02','2024-05-09 09:00:00','2024-05-09 12:00:00',10,5,NULL),(122,1,'Event 2','Description for Event 2','Facility B',2,'active','2024-05-26 02:56:02','2024-05-10 10:00:00','2024-05-10 12:00:00',15,8,NULL),(123,2,'Event 3','Description for Event 3','Facility C',4,'denied','2024-05-26 02:56:02','2024-05-11 08:00:00','2024-05-11 12:00:00',8,3,NULL),(124,2,'Event 4','Description for Event 4','Facility D',5,'completed','2024-05-26 02:56:02','2024-05-12 10:00:00','2024-05-12 15:00:00',20,10,NULL),(125,1,'Event 5','Description for Event 5','Facility E',2,'pending','2024-05-26 02:56:02','2024-05-13 09:00:00','2024-05-13 11:00:00',12,6,NULL),(126,2,'Event 6','Description for Event 6','Facility F',3,'completed','2024-05-26 02:56:02','2024-05-14 11:00:00','2024-05-14 14:00:00',18,9,NULL),(127,1,'Event 7','Description for Event 7','Facility G',4,'active','2024-05-26 02:56:02','2024-05-15 08:00:00','2024-05-15 12:00:00',25,12,NULL),(128,2,'Event 8','Description for Event 8','Facility H',3,'denied','2024-05-26 02:56:02','2024-05-16 10:00:00','2024-05-16 13:00:00',9,4,NULL),(129,1,'Event 9','Description for Event 9','Facility I',2,'completed','2024-05-26 02:56:02','2024-05-17 09:00:00','2024-05-17 11:00:00',14,7,NULL),(130,2,'Event 10','Description for Event 10','Facility J',5,'pending','2024-05-26 02:56:02','2024-05-18 11:00:00','2024-05-18 16:00:00',22,11,NULL),(131,1,'Event 11','Description for Event 11','Facility K',3,'completed','2024-05-26 02:56:02','2024-05-19 08:00:00','2024-05-19 11:00:00',30,15,NULL),(132,2,'Event 12','Description for Event 12','Facility L',4,'active','2024-05-26 02:56:02','2024-05-20 10:00:00','2024-05-20 14:00:00',35,18,NULL),(133,1,'Event 13','Description for Event 13','Facility M',2,'denied','2024-05-26 02:56:02','2024-05-21 09:00:00','2024-05-21 11:00:00',10,5,NULL),(134,2,'Event 14','Description for Event 14','Facility N',3,'completed','2024-05-26 02:56:02','2024-05-22 11:00:00','2024-05-22 14:00:00',40,20,NULL),(135,1,'Event 15','Description for Event 15','Facility O',5,'pending','2024-05-26 02:56:02','2024-05-23 08:00:00','2024-05-23 13:00:00',18,9,NULL),(136,2,'Event 16','Description for Event 16','Facility P',4,'completed','2024-05-26 02:56:02','2024-05-24 10:00:00','2024-05-24 14:00:00',45,22,NULL),(137,1,'Event 17','Description for Event 17','Facility Q',3,'active','2024-05-26 02:56:02','2024-05-25 09:00:00','2024-05-25 12:00:00',50,25,NULL),(138,2,'Event 18','Description for Event 18','Facility R',2,'denied','2024-05-26 02:56:02','2024-05-26 11:00:00','2024-05-26 13:00:00',15,7,NULL),(139,1,'Event 19','Description for Event 19','Facility S',3,'completed','2024-05-26 02:56:02','2024-05-26 08:00:00','2024-05-26 11:00:00',60,30,NULL),(140,2,'Event 20','Description for Event 20','Facility T',4,'pending','2024-05-26 02:56:02','2024-05-28 10:00:00','2024-05-28 14:00:00',25,12,NULL),(141,1,'Event 21','Description for Event 21','Facility U',2,'completed','2024-05-26 02:56:02','2024-05-29 09:00:00','2024-05-29 11:00:00',70,35,NULL),(142,2,'Event 22','Description for Event 22','Facility V',3,'active','2024-05-26 02:56:02','2024-05-30 11:00:00','2024-05-30 14:00:00',30,15,NULL),(143,1,'Event 23','Description for Event 23','Facility W',4,'denied','2024-05-26 02:56:02','2024-05-31 08:00:00','2024-05-31 12:00:00',35,18,NULL),(144,2,'Event 24','Description for Event 24','Facility X',5,'ongoing','2024-05-26 02:56:02','2024-06-01 10:00:00','2024-06-01 15:00:00',40,20,NULL),(145,1,'Event 25','Description for Event 25','Facility Y',2,'pending','2024-05-26 02:56:02','2024-06-02 09:00:00','2024-06-02 11:00:00',45,22,NULL),(146,2,'Event 26','Description for Event 26','Facility Z',3,'completed','2024-05-26 02:56:02','2024-06-03 11:00:00','2024-06-03 14:00:00',50,25,NULL),(147,1,'Event 27','Description for Event 27','Facility AA',4,'active','2024-05-26 02:56:02','2024-06-04 08:00:00','2024-06-04 12:00:00',55,27,NULL),(148,2,'Event 28','Description for Event 28','Facility BB',3,'denied','2024-05-26 02:56:02','2024-06-05 10:00:00','2024-06-05 13:00:00',60,30,NULL),(149,1,'Event 29','Description for Event 29','Facility CC',2,'ongoing','2024-05-26 02:56:02','2024-06-06 09:00:00','2024-06-06 11:00:00',65,32,NULL),(150,2,'Event 30','Description for Event 30','Facility DD',5,'pending','2024-05-26 02:56:02','2024-06-07 11:00:00','2024-06-07 16:00:00',70,35,NULL),(151,2,'test125','testsad','12312431',24,'pending','2024-05-26 03:25:34','2024-05-28 11:25:00','2024-05-29 11:25:00',0,0,NULL),(152,2,'tests','12312412','124124',24,'completed','2024-05-24 17:47:32','2024-05-24 01:47:00','2024-05-25 01:47:00',0,0,NULL);
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reset_token` varchar(255) DEFAULT NULL,
  `token_creation_time` datetime DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') NOT NULL,
  `can_request_event` tinyint(1) DEFAULT 1,
  `can_review_request` tinyint(1) DEFAULT 0,
  `can_delete_user` tinyint(1) DEFAULT 0,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,NULL,'admin','$2y$10$PWHl9oxxQQGwoUop2R5y8uhRSl1rIojFIXS.O4HUs3MXlTUrZX2Ku','female','jandreijandrei103@gmail.com','../ASSETS/IMG/DPFP/female.png','admin',1,1,1,'2024-05-26 00:04:04',1),(2,NULL,NULL,'user','$2y$10$UOpA9g4TcOcD0vnJjAPJAOEFwjS65DN9CWyfUfxKOP8UWrn1TsEL2','female','','../ASSETS/IMG/DPFP/female.png','user',1,0,0,'2024-05-26 00:04:04',0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-05-27 20:47:16
