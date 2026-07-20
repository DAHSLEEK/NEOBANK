-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: neobank_db
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
-- Table structure for table `account`
--

DROP TABLE IF EXISTS `account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account` (
  `account_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `branch_id` int(10) unsigned NOT NULL,
  `account_number` varchar(30) NOT NULL,
  `account_type` varchar(50) NOT NULL,
  `account_category` varchar(20) NOT NULL DEFAULT 'CUSTOMER',
  `account_name` varchar(150) DEFAULT NULL,
  `date_opened` date NOT NULL,
  `time_created` datetime NOT NULL DEFAULT current_timestamp(),
  `current_time` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`account_id`),
  UNIQUE KEY `account_number` (`account_number`),
  KEY `fk_account_customer` (`customer_id`),
  KEY `fk_account_branch` (`branch_id`),
  CONSTRAINT `fk_account_branch` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`branch_id`),
  CONSTRAINT `fk_account_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account`
--

LOCK TABLES `account` WRITE;
/*!40000 ALTER TABLE `account` DISABLE KEYS */;
INSERT INTO `account` VALUES (1,1,1,'NEO-00100001','Current','CUSTOMER','James Okafor Current','2020-01-15','2026-06-29 12:34:00','2026-06-29 12:34:00'),(2,1,1,'NEO-00100002','Savings','CUSTOMER','James Okafor Savings','2020-01-15','2026-06-29 12:34:00','2026-06-29 12:34:00'),(3,2,2,'NEO-00200001','Current','CUSTOMER','Amelia Thornton Current','2019-06-10','2026-06-29 12:34:00','2026-06-29 12:34:00'),(4,3,3,'NEO-00300001','Business','CUSTOMER','Al-Rashid Business Account','2018-03-22','2026-06-29 12:34:00','2026-06-29 12:34:00'),(5,4,4,'NEO-00400001','Current','CUSTOMER','Priya Sharma Current','2021-09-05','2026-06-29 12:34:00','2026-06-29 12:34:00'),(6,5,1,'NEO-00500001','Savings','CUSTOMER','David Williams Savings','2017-11-30','2026-06-29 12:34:00','2026-06-29 12:34:00'),(7,6,2,'NEO-00600001','Current','CUSTOMER','Fatima Nwosu Current','2022-04-18','2026-06-29 12:34:00','2026-06-29 12:34:00'),(8,7,5,'NEO-00700001','Savings','CUSTOMER','Chen Wei Savings','2023-01-09','2026-06-29 12:34:00','2026-06-29 12:34:00'),(9,8,3,'NEO-00800001','Business','CUSTOMER','Mitchell Consulting Account','2016-07-14','2026-06-29 12:34:00','2026-06-29 12:34:00'),(10,9,4,'NEO-00900001','Business','CUSTOMER','Asante Enterprises Account','2015-02-28','2026-06-29 12:34:00','2026-06-29 12:34:00'),(11,10,6,'NEO-01000001','Current','CUSTOMER','Elena Petrova Current','2023-08-20','2026-06-29 12:34:00','2026-06-29 12:34:00'),(12,11,7,'NEO-01100001','Savings','CUSTOMER','Thomas Hughes Savings','2010-05-01','2026-06-29 12:34:00','2026-06-29 12:34:00'),(13,12,8,'NEO-01200001','Current','CUSTOMER','Aisha Kamara Current','2022-11-11','2026-06-29 12:34:00','2026-06-29 12:34:00'),(14,13,9,'NEO-01300001','Business','CUSTOMER','Rossi Restaurant Account','2019-04-03','2026-06-29 12:34:00','2026-06-29 12:34:00'),(15,14,10,'NEO-01400001','Current','CUSTOMER','Hannah Osei Current','2021-07-25','2026-06-29 12:34:00','2026-06-29 12:34:00'),(16,16,2,'NEO-00000016','Savings','CUSTOMER','Yewande Oduntan','2026-06-30','2026-06-30 12:19:47','2026-06-30 12:19:47'),(17,17,5,'NEO-00000018','Savings','CUSTOMER','Sekinat Omobolanle Lateef','2026-07-02','2026-07-02 00:51:20','2026-07-02 01:02:26'),(35,NULL,1,'NEO-CASH-0001','Internal','INTERNAL-CASH','London Central Cash Account','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(36,NULL,2,'NEO-CASH-0002','Internal','INTERNAL-CASH','Manchester Piccadilly Cash Account','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(37,NULL,3,'NEO-CASH-0003','Internal','INTERNAL-CASH','Birmingham City Cash Account','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(38,NULL,4,'NEO-CASH-0004','Internal','INTERNAL-CASH','Leeds Metropolitan Cash Account','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(39,NULL,5,'NEO-CASH-0005','Internal','INTERNAL-CASH','Glasgow West End Cash Account','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(40,NULL,6,'NEO-CASH-0006','Internal','INTERNAL-CASH','Edinburgh Royal Mile Cash Account','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(41,NULL,7,'NEO-CASH-0007','Internal','INTERNAL-CASH','Bristol Harbourside Cash Account','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(42,NULL,8,'NEO-CASH-0008','Internal','INTERNAL-CASH','Liverpool One Cash Account','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(43,NULL,9,'NEO-CASH-0009','Internal','INTERNAL-CASH','Sheffield Central Cash Account','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(44,NULL,10,'NEO-CASH-0010','Internal','INTERNAL-CASH','Cardiff Bay Cash Account','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(45,NULL,11,'NEO-CASH-0011','Internal','INTERNAL-CASH','Nottingham Castle Cash Account','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(46,NULL,12,'NEO-CASH-0012','Internal','INTERNAL-CASH','Leicester Square Cash Account','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(47,NULL,13,'NEO-CASH-0013','Internal','INTERNAL-CASH','Newcastle Quayside Cash Account','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(48,NULL,14,'NEO-CASH-0014','Internal','INTERNAL-CASH','Southampton Docks Cash Account','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(49,NULL,15,'NEO-CASH-0015','Internal','INTERNAL-CASH','Reading Thames Cash Account','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(50,NULL,1,'NEO-PAY-0001','Internal','INTERNAL-PAYABLE','London Central Accounts Payable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(51,NULL,2,'NEO-PAY-0002','Internal','INTERNAL-PAYABLE','Manchester Piccadilly Accounts Payable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(52,NULL,3,'NEO-PAY-0003','Internal','INTERNAL-PAYABLE','Birmingham City Accounts Payable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(53,NULL,4,'NEO-PAY-0004','Internal','INTERNAL-PAYABLE','Leeds Metropolitan Accounts Payable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(54,NULL,5,'NEO-PAY-0005','Internal','INTERNAL-PAYABLE','Glasgow West End Accounts Payable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(55,NULL,6,'NEO-PAY-0006','Internal','INTERNAL-PAYABLE','Edinburgh Royal Mile Accounts Payable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(56,NULL,7,'NEO-PAY-0007','Internal','INTERNAL-PAYABLE','Bristol Harbourside Accounts Payable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(57,NULL,8,'NEO-PAY-0008','Internal','INTERNAL-PAYABLE','Liverpool One Accounts Payable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(58,NULL,9,'NEO-PAY-0009','Internal','INTERNAL-PAYABLE','Sheffield Central Accounts Payable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(59,NULL,10,'NEO-PAY-0010','Internal','INTERNAL-PAYABLE','Cardiff Bay Accounts Payable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(60,NULL,11,'NEO-PAY-0011','Internal','INTERNAL-PAYABLE','Nottingham Castle Accounts Payable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(61,NULL,12,'NEO-PAY-0012','Internal','INTERNAL-PAYABLE','Leicester Square Accounts Payable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(62,NULL,13,'NEO-PAY-0013','Internal','INTERNAL-PAYABLE','Newcastle Quayside Accounts Payable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(63,NULL,14,'NEO-PAY-0014','Internal','INTERNAL-PAYABLE','Southampton Docks Accounts Payable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(64,NULL,15,'NEO-PAY-0015','Internal','INTERNAL-PAYABLE','Reading Thames Accounts Payable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(65,NULL,1,'NEO-REC-0001','Internal','INTERNAL-RECEIVABLE','London Central Accounts Receivable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(66,NULL,2,'NEO-REC-0002','Internal','INTERNAL-RECEIVABLE','Manchester Piccadilly Accounts Receivable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(67,NULL,3,'NEO-REC-0003','Internal','INTERNAL-RECEIVABLE','Birmingham City Accounts Receivable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(68,NULL,4,'NEO-REC-0004','Internal','INTERNAL-RECEIVABLE','Leeds Metropolitan Accounts Receivable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(69,NULL,5,'NEO-REC-0005','Internal','INTERNAL-RECEIVABLE','Glasgow West End Accounts Receivable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(70,NULL,6,'NEO-REC-0006','Internal','INTERNAL-RECEIVABLE','Edinburgh Royal Mile Accounts Receivable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(71,NULL,7,'NEO-REC-0007','Internal','INTERNAL-RECEIVABLE','Bristol Harbourside Accounts Receivable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(72,NULL,8,'NEO-REC-0008','Internal','INTERNAL-RECEIVABLE','Liverpool One Accounts Receivable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(73,NULL,9,'NEO-REC-0009','Internal','INTERNAL-RECEIVABLE','Sheffield Central Accounts Receivable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(74,NULL,10,'NEO-REC-0010','Internal','INTERNAL-RECEIVABLE','Cardiff Bay Accounts Receivable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(75,NULL,11,'NEO-REC-0011','Internal','INTERNAL-RECEIVABLE','Nottingham Castle Accounts Receivable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(76,NULL,12,'NEO-REC-0012','Internal','INTERNAL-RECEIVABLE','Leicester Square Accounts Receivable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(77,NULL,13,'NEO-REC-0013','Internal','INTERNAL-RECEIVABLE','Newcastle Quayside Accounts Receivable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(78,NULL,14,'NEO-REC-0014','Internal','INTERNAL-RECEIVABLE','Southampton Docks Accounts Receivable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(79,NULL,15,'NEO-REC-0015','Internal','INTERNAL-RECEIVABLE','Reading Thames Accounts Receivable','2026-07-02','2026-07-02 01:28:39','2026-07-02 01:28:39'),(98,NULL,16,'NEO-CASH-0016','Internal','INTERNAL-CASH','NeoBank Head Office Cash Account','2026-07-02','2026-07-02 11:43:03','2026-07-02 11:43:03'),(99,NULL,16,'NEO-PAY-0016','Internal','INTERNAL-PAYABLE','NeoBank Head Office Accounts Payable','2026-07-02','2026-07-02 11:43:03','2026-07-02 11:43:03'),(100,NULL,16,'NEO-REC-0016','Internal','INTERNAL-RECEIVABLE','NeoBank Head Office Accounts Receivable','2026-07-02','2026-07-02 11:43:03','2026-07-02 11:43:03'),(101,18,5,'NEO-01400002','Current','CUSTOMER','Queen Nneoma Nze','2026-07-19','2026-07-19 21:51:15','2026-07-19 21:51:15');
/*!40000 ALTER TABLE `account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_balance`
--

DROP TABLE IF EXISTS `account_balance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_balance` (
  `balance_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(10) unsigned NOT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'GBP',
  `balance_date` date NOT NULL,
  `total_credit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_debit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `time_created` datetime NOT NULL DEFAULT current_timestamp(),
  `current_time` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`balance_id`),
  KEY `fk_balance_account` (`account_id`),
  CONSTRAINT `fk_balance_account` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_balance`
--

LOCK TABLES `account_balance` WRITE;
/*!40000 ALTER TABLE `account_balance` DISABLE KEYS */;
INSERT INTO `account_balance` VALUES (1,1,3370.00,'GBP','2026-07-02',15000.00,11630.00,'2026-06-29 12:34:00','2026-07-02 01:57:09'),(2,2,12000.00,'GBP','2026-06-28',20000.00,8000.00,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(3,3,3200.50,'GBP','2026-06-28',9500.00,6299.50,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(4,4,87500.00,'GBP','2026-06-28',150000.00,62500.00,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(5,5,1810.75,'GBP','2026-07-07',5010.00,3199.25,'2026-06-29 12:34:00','2026-07-07 12:45:24'),(6,6,25000.00,'GBP','2026-06-28',40000.00,15000.00,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(7,7,300.00,'GBP','2026-07-07',2000.00,1700.00,'2026-06-29 12:34:00','2026-07-07 12:46:13'),(8,8,670.25,'GBP','2026-07-07',1850.00,1179.75,'2026-06-29 12:34:00','2026-07-07 12:46:13'),(9,9,45000.00,'GBP','2026-06-28',80000.00,35000.00,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(10,10,120000.00,'GBP','2026-06-28',200000.00,80000.00,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(11,11,980.00,'GBP','2026-06-28',3000.00,2020.00,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(12,12,15600.00,'GBP','2026-06-28',30000.00,14400.00,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(13,13,2100.00,'GBP','2026-06-28',6000.00,3900.00,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(14,14,8500.50,'GBP','2026-07-07',18000.00,9499.50,'2026-06-29 12:34:00','2026-07-07 12:32:01'),(15,15,500.00,'GBP','2026-06-28',1200.00,700.00,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(16,16,1180.00,'GBP','2026-07-07',1190.00,10.00,'2026-06-30 12:19:47','2026-07-07 13:12:10'),(17,17,50.00,'GBP','2026-07-02',50.00,0.00,'2026-07-02 00:51:20','2026-07-02 00:51:20'),(33,35,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(34,36,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(35,37,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(36,38,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(37,39,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(38,40,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(39,41,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(40,42,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(41,43,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(42,44,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(43,45,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(44,46,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(45,47,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(46,48,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(47,49,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(48,50,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(49,51,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(50,52,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(51,53,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(52,54,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(53,55,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(54,56,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(55,57,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(56,58,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(57,59,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(58,60,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(59,61,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(60,62,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(61,63,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(62,64,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(63,65,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(64,66,-10.00,'GBP','2026-07-07',0.00,10.00,'2026-07-02 01:29:10','2026-07-07 13:12:10'),(65,67,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(66,68,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(67,69,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(68,70,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(69,71,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(70,72,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(71,73,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(72,74,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(73,75,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(74,76,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(75,77,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(76,78,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(77,79,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 01:29:10','2026-07-02 01:29:10'),(96,98,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 11:43:24','2026-07-02 11:43:24'),(97,99,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 11:43:24','2026-07-02 11:43:24'),(98,100,0.00,'GBP','2026-07-02',0.00,0.00,'2026-07-02 11:43:24','2026-07-02 11:43:24'),(99,101,0.00,'GBP','2026-07-19',0.00,0.00,'2026-07-19 21:51:15','2026-07-19 21:51:15');
/*!40000 ALTER TABLE `account_balance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_status`
--

DROP TABLE IF EXISTS `account_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_status` (
  `status_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(10) unsigned NOT NULL,
  `status` varchar(50) NOT NULL,
  `status_date` datetime NOT NULL,
  `changed_by` int(10) unsigned DEFAULT NULL,
  `time_created` datetime NOT NULL DEFAULT current_timestamp(),
  `current_time` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`status_id`),
  KEY `fk_accstatus_account` (`account_id`),
  KEY `fk_accstatus_employee` (`changed_by`),
  CONSTRAINT `fk_accstatus_account` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`),
  CONSTRAINT `fk_accstatus_employee` FOREIGN KEY (`changed_by`) REFERENCES `employee` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_status`
--

LOCK TABLES `account_status` WRITE;
/*!40000 ALTER TABLE `account_status` DISABLE KEYS */;
INSERT INTO `account_status` VALUES (1,1,'ACTIVE','2020-01-15 09:00:00',1,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(2,2,'ACTIVE','2020-01-15 09:05:00',1,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(3,3,'ACTIVE','2019-06-10 10:00:00',3,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(4,4,'ACTIVE','2018-03-22 11:00:00',5,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(5,5,'ACTIVE','2021-09-05 09:30:00',7,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(6,6,'ACTIVE','2017-11-30 10:00:00',1,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(7,7,'ACTIVE','2022-04-18 09:00:00',3,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(8,8,'ACTIVE','2023-01-09 10:00:00',9,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(9,9,'ACTIVE','2016-07-14 11:00:00',5,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(10,10,'ACTIVE','2015-02-28 09:00:00',7,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(11,11,'ACTIVE','2023-08-20 10:00:00',11,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(12,12,'ACTIVE','2010-05-01 09:00:00',12,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(13,13,'SUSPENDED','2024-01-10 14:00:00',4,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(14,14,'ACTIVE','2019-04-03 09:00:00',13,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(15,15,'ACTIVE','2021-07-25 10:00:00',14,'2026-06-29 12:34:00','2026-06-29 12:34:00'),(16,16,'ACTIVE','2026-06-30 12:19:47',NULL,'2026-06-30 12:19:47','2026-06-30 12:19:47'),(17,17,'ACTIVE','2026-07-02 00:51:20',NULL,'2026-07-02 00:51:20','2026-07-02 00:51:20'),(33,35,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(34,36,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(35,37,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(36,38,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(37,39,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(38,40,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(39,41,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(40,42,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(41,43,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(42,44,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(43,45,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(44,46,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(45,47,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(46,48,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(47,49,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(48,50,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(49,51,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(50,52,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(51,53,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(52,54,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(53,55,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(54,56,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(55,57,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(56,58,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(57,59,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(58,60,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(59,61,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(60,62,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(61,63,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(62,64,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(63,65,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(64,66,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(65,67,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(66,68,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(67,69,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(68,70,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(69,71,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(70,72,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(71,73,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(72,74,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(73,75,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(74,76,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(75,77,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(76,78,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(77,79,'ACTIVE','2026-07-02 01:29:30',NULL,'2026-07-02 01:29:30','2026-07-02 01:29:30'),(96,98,'ACTIVE','2026-07-02 11:43:24',NULL,'2026-07-02 11:43:24','2026-07-02 11:43:24'),(97,99,'ACTIVE','2026-07-02 11:43:24',NULL,'2026-07-02 11:43:24','2026-07-02 11:43:24'),(98,100,'ACTIVE','2026-07-02 11:43:24',NULL,'2026-07-02 11:43:24','2026-07-02 11:43:24'),(99,101,'ACTIVE','2026-07-19 21:51:15',NULL,'2026-07-19 21:51:15','2026-07-19 21:51:15');
/*!40000 ALTER TABLE `account_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branch`
--

DROP TABLE IF EXISTS `branch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `branch` (
  `branch_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `branch_name` varchar(100) NOT NULL,
  `branch_code` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'ACTIVE',
  `time_created` datetime NOT NULL DEFAULT current_timestamp(),
  `current_time` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`branch_id`),
  UNIQUE KEY `branch_code` (`branch_code`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branch`
--

LOCK TABLES `branch` WRITE;
/*!40000 ALTER TABLE `branch` DISABLE KEYS */;
INSERT INTO `branch` VALUES (1,'London Central','NEO-LDN-001','ACTIVE','2026-06-29 12:34:00','2026-06-29 12:34:00'),(2,'Manchester Piccadilly','NEO-MAN-002','ACTIVE','2026-06-29 12:34:00','2026-06-29 12:34:00'),(3,'Birmingham City','NEO-BHM-003','ACTIVE','2026-06-29 12:34:00','2026-06-29 12:34:00'),(4,'Leeds Metropolitan','NEO-LDS-004','ACTIVE','2026-06-29 12:34:00','2026-06-29 12:34:00'),(5,'Glasgow West End','NEO-GLA-005','ACTIVE','2026-06-29 12:34:00','2026-06-29 12:34:00'),(6,'Edinburgh Royal Mile','NEO-EDI-006','ACTIVE','2026-06-29 12:34:00','2026-06-29 12:34:00'),(7,'Bristol Harbourside','NEO-BST-007','ACTIVE','2026-06-29 12:34:00','2026-06-29 12:34:00'),(8,'Liverpool One','NEO-LVP-008','ACTIVE','2026-06-29 12:34:00','2026-06-29 12:34:00'),(9,'Sheffield Central','NEO-SHF-009','ACTIVE','2026-06-29 12:34:00','2026-06-29 12:34:00'),(10,'Cardiff Bay','NEO-CDF-010','ACTIVE','2026-06-29 12:34:00','2026-06-29 12:34:00'),(11,'Nottingham Castle','NEO-NGM-011','ACTIVE','2026-06-29 12:34:00','2026-06-29 12:34:00'),(12,'Leicester Square','NEO-LCE-012','ACTIVE','2026-06-29 12:34:00','2026-06-29 12:34:00'),(13,'Newcastle Quayside','NEO-NCL-013','ACTIVE','2026-06-29 12:34:00','2026-06-29 12:34:00'),(14,'Southampton Docks','NEO-STH-014','ACTIVE','2026-06-29 12:34:00','2026-06-29 12:34:00'),(15,'Reading Thames','NEO-RDG-015','ACTIVE','2026-06-29 12:34:00','2026-06-29 12:34:00'),(16,'NeoBank Head Office','NEO-HQ-000','ACTIVE','2026-07-02 11:42:43','2026-07-02 11:42:43'),(17,'Cumbernauld','NEO-CUM-001','ACTIVE','2026-07-07 13:51:11','2026-07-08 12:00:56');
/*!40000 ALTER TABLE `branch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact`
--

DROP TABLE IF EXISTS `contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact` (
  `contact_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `branch_id` int(10) unsigned DEFAULT NULL,
  `employee_id` int(10) unsigned DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `mobile` varchar(30) DEFAULT NULL,
  `postcode` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`contact_id`),
  KEY `fk_contact_customer` (`customer_id`),
  KEY `fk_contact_branch` (`branch_id`),
  KEY `fk_contact_employee` (`employee_id`),
  CONSTRAINT `fk_contact_branch` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`branch_id`),
  CONSTRAINT `fk_contact_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`),
  CONSTRAINT `fk_contact_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact`
--

LOCK TABLES `contact` WRITE;
/*!40000 ALTER TABLE `contact` DISABLE KEYS */;
INSERT INTO `contact` VALUES (1,1,NULL,NULL,'14 Baker Street, London','james.okafor@email.com','02071234567','07911123456','W1U 3BW','United Kingdom'),(2,2,NULL,NULL,'22 Rose Avenue, Manchester','amelia.thornton@email.com','01612345678','07922234567','M1 4BT','United Kingdom'),(3,3,NULL,NULL,'5 Crescent Road, Birmingham','mo.alrashid@business.com','01219876543','07933345678','B1 1AA','United Kingdom'),(4,4,NULL,NULL,'88 Maple Close, Leeds','priya.sharma@email.com','01138765432','07944456789','LS1 2AB','United Kingdom'),(5,5,NULL,NULL,'3 Oak Lane, Bristol','david.williams@email.com','01177654321','07955567890','BS1 3CD','United Kingdom'),(6,NULL,1,NULL,'1 Canary Wharf, London','london.central@neobank.co.uk','02079001000',NULL,'E14 5AB','United Kingdom'),(7,NULL,2,NULL,'10 Piccadilly Gardens, Manchester','manchester@neobank.co.uk','01619001000',NULL,'M1 1RG','United Kingdom'),(8,NULL,3,NULL,'20 Broad Street, Birmingham','birmingham@neobank.co.uk','01219001000',NULL,'B1 2EA','United Kingdom'),(9,NULL,4,NULL,'5 The Headrow, Leeds','leeds@neobank.co.uk','01139001000',NULL,'LS1 6PU','United Kingdom'),(10,NULL,5,NULL,'30 Sauchiehall Street, Glasgow','glasgow@neobank.co.uk','01419001000',NULL,'G2 3AH','United Kingdom'),(11,NULL,NULL,1,'9 Elm Drive, London','sandra.obi@neobank.co.uk','02071119000','07800111001','W1A 1AA','United Kingdom'),(12,NULL,NULL,2,'17 Pine Road, London','kevin.marsh@neobank.co.uk','02071119001','07800111002','W1B 2BB','United Kingdom'),(13,NULL,NULL,3,'4 Birch Lane, Manchester','diane.fletcher@neobank.co.uk','01619001001','07800111003','M2 1CC','United Kingdom'),(14,NULL,NULL,4,'6 Willow Way, Manchester','omar.hassan@neobank.co.uk','01619001002','07800111004','M2 2DD','United Kingdom'),(15,NULL,NULL,5,'11 Ash Grove, Birmingham','claire.jennings@neobank.co.uk','01219001001','07800111005','B2 3EE','United Kingdom'),(16,6,NULL,NULL,'','fatimanwosu@rocketmail.comm','+2348030789904','','',''),(17,16,NULL,NULL,'21 Aliu Street, Ketu, Mile 12, Lagos, Nigeria','yewande.oduntan@moniepoint.com','+2349034237515','','23401','Nigeria'),(18,17,NULL,NULL,'90 Milcroft road, Cumbernauld','seqeenahomoh@icloud.com','+447823725881','','G672QH','United Kingdom'),(19,NULL,NULL,17,'128 MILCROFT ROAD, CUMBERNAULD, GLASGOW','babajdie.anibaba@neobank.co.uk','+2347063424714','','G672QH','United Kingdom'),(20,NULL,17,NULL,'1, MILCROFT ROAD, CUMBERNAULD, SCOTLAND','cumbernauld@neobank.com','07063424714',NULL,'G671QH','United Kingdom'),(21,18,NULL,NULL,'3/3 6 Dyke street, baillieston Glasgow','queennze124@gmail.com','+447867062585','07867062585','G69 6DU','United kingdom');
/*!40000 ALTER TABLE `contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer`
--

DROP TABLE IF EXISTS `customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer` (
  `customer_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(150) NOT NULL,
  `date_of_birth` date NOT NULL,
  `customer_type` varchar(50) NOT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `id_type` varchar(50) NOT NULL,
  `id_number` varchar(100) NOT NULL,
  `time_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer`
--

LOCK TABLES `customer` WRITE;
/*!40000 ALTER TABLE `customer` DISABLE KEYS */;
INSERT INTO `customer` VALUES (1,'James Okafor','1985-03-12','Personal','Male','British','Software Engineer','Passport','GB123456A','2026-06-29 12:34:00'),(2,'Amelia Thornton','1990-07-24','Personal','Female','British','Doctor','Driving Licence','THOR990724','2026-06-29 12:34:00'),(3,'Mohammed Al-Rashid','1978-11-05','Business','Male','Saudi','Business Owner','Passport','SA987654B','2026-06-29 12:34:00'),(4,'Priya Sharma','1993-01-30','Personal','Female','Indian','Accountant','Passport','IN456789C','2026-06-29 12:34:00'),(5,'David Williams','1965-09-18','Personal','Male','British','Retired','Driving Licence','WILL650918','2026-06-29 12:34:00'),(6,'Fatima Nwosu','1988-04-02','Personal','Female','Nigerian','Nurse','Passport','NG321654D','2026-06-29 12:34:00'),(7,'Chen Wei','1995-12-15','Personal','Male','Chinese','Student','Passport','CN654321E','2026-06-29 12:34:00'),(8,'Sarah Mitchell','1982-06-28','Business','Female','British','Consultant','Driving Licence','MITC820628','2026-06-29 12:34:00'),(9,'Kwame Asante','1975-08-10','Business','Male','Ghanaian','Entrepreneur','Passport','GH147258F','2026-06-29 12:34:00'),(10,'Elena Petrova','1999-02-20','Personal','Female','Romanian','Graphic Designer','Passport','RO258369G','2026-06-29 12:34:00'),(11,'Thomas Hughes','1960-05-14','Personal','Male','British','Teacher','Driving Licence','HUGH600514','2026-06-29 12:34:00'),(12,'Aisha Kamara','1997-10-08','Personal','Female','Sierra Leonean','Marketing Executive','Passport','SL369147H','2026-06-29 12:34:00'),(13,'Luca Rossi','1986-03-25','Business','Male','Italian','Restaurant Owner','Passport','IT741852I','2026-06-29 12:34:00'),(14,'Hannah Osei','1992-07-11','Personal','Female','Ghanaian','Pharmacist','Passport','GH852963J','2026-06-29 12:34:00'),(15,'Robert Clarke','1970-12-03','Personal','Male','British','Civil Servant','Driving Licence','CLAR701203','2026-06-29 12:34:00'),(16,'Yewande Oduntan','1993-07-24','Personal','Female','Nigerian','Banker','Passport','BN123456','2026-06-30 12:08:54'),(17,'Sekinat Lateef','2001-07-07','Personal','Female','Nigerian','Student','Passport','BN71272','2026-07-02 00:49:25'),(18,'Queen Nneoma Nze','1999-04-29','Personal','Female','Nigeria','Student','Passport','B03525992','2026-07-19 21:49:53');
/*!40000 ALTER TABLE `customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee`
--

DROP TABLE IF EXISTS `employee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee` (
  `employee_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(10) unsigned NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `job_title` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'ACTIVE',
  `hire_date` date NOT NULL,
  `time_created` datetime NOT NULL DEFAULT current_timestamp(),
  `current_time` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`employee_id`),
  KEY `fk_employee_branch` (`branch_id`),
  CONSTRAINT `fk_employee_branch` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`branch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee`
--

LOCK TABLES `employee` WRITE;
/*!40000 ALTER TABLE `employee` DISABLE KEYS */;
INSERT INTO `employee` VALUES (1,1,'Sandra Obi','Branch Manager','ACTIVE','2015-04-01','2026-06-29 12:34:00','2026-06-29 12:34:00'),(2,1,'Kevin Marsh','Customer Advisor','ACTIVE','2018-09-15','2026-06-29 12:34:00','2026-06-29 12:34:00'),(3,2,'Diane Fletcher','Branch Manager','ACTIVE','2013-06-10','2026-06-29 12:34:00','2026-06-29 12:34:00'),(4,2,'Omar Hassan','Loans Officer','ACTIVE','2020-01-20','2026-06-29 12:34:00','2026-06-29 12:34:00'),(5,3,'Claire Jennings','Branch Manager','ACTIVE','2016-03-07','2026-06-29 12:34:00','2026-06-29 12:34:00'),(6,3,'Paul Adeyemi','Customer Advisor','ACTIVE','2021-05-12','2026-06-29 12:34:00','2026-06-29 12:34:00'),(7,4,'Rachel Tong','Branch Manager','ACTIVE','2014-11-25','2026-06-29 12:34:00','2026-06-29 12:34:00'),(8,4,'James Boateng','Compliance Officer','ACTIVE','2019-08-03','2026-06-29 12:34:00','2026-06-29 12:34:00'),(9,5,'Fiona MacLeod','Branch Manager','ACTIVE','2017-02-14','2026-06-29 12:34:00','2026-06-29 12:34:00'),(10,5,'Stuart Campbell','Customer Advisor','ACTIVE','2022-03-01','2026-06-29 12:34:00','2026-06-29 12:34:00'),(11,6,'Niall Ferguson','Branch Manager','ACTIVE','2012-07-19','2026-06-29 12:34:00','2026-06-29 12:34:00'),(12,7,'Laura Simmons','Branch Manager','ACTIVE','2018-10-22','2026-06-29 12:34:00','2026-06-29 12:34:00'),(13,8,'Tony Mwangi','Loans Officer','ACTIVE','2020-06-30','2026-06-29 12:34:00','2026-06-29 12:34:00'),(14,9,'Gemma Harrison','Branch Manager','ACTIVE','2015-09-09','2026-06-29 12:34:00','2026-06-29 12:34:00'),(15,10,'Dylan Price','Customer Advisor','ACTIVE','2023-01-16','2026-06-29 12:34:00','2026-06-29 12:34:00'),(16,16,'System Administrator','Admin','ACTIVE','2026-07-02','2026-07-02 11:44:14','2026-07-02 11:44:14'),(17,17,'Babajide Anibaba','Branch Manager','ACTIVE','2026-07-07','2026-07-07 14:01:32','2026-07-07 14:01:32');
/*!40000 ALTER TABLE `employee` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login`
--

DROP TABLE IF EXISTS `login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login` (
  `attempt_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `attempted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`attempt_id`),
  KEY `fk_login_user` (`user_id`),
  CONSTRAINT `fk_login_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login`
--

LOCK TABLES `login` WRITE;
/*!40000 ALTER TABLE `login` DISABLE KEYS */;
INSERT INTO `login` VALUES (1,'admin','127.0.0.1',NULL,'2026-07-08 12:59:51',1),(2,'admin','127.0.0.1',NULL,'2026-07-08 13:23:13',1),(3,'admin','127.0.0.1',NULL,'2026-07-08 13:25:52',0),(4,'admindd','127.0.0.1',NULL,'2026-07-08 13:26:01',0),(5,'admin','127.0.0.1',NULL,'2026-07-08 13:26:05',1),(6,'admin','127.0.0.1',NULL,'2026-07-09 15:34:49',1),(7,'admin','127.0.0.1',NULL,'2026-07-18 22:53:06',1),(8,'admin','127.0.0.1',1,'2026-07-19 21:36:58',1);
/*!40000 ALTER TABLE `login` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modification_audit`
--

DROP TABLE IF EXISTS `modification_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modification_audit` (
  `audit_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `table_affected` varchar(100) NOT NULL,
  `record_id` int(10) unsigned NOT NULL,
  `employee_id` int(10) unsigned DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `time_created` datetime NOT NULL DEFAULT current_timestamp(),
  `current_time` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`audit_id`),
  KEY `fk_audit_employee` (`employee_id`),
  CONSTRAINT `fk_audit_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modification_audit`
--

LOCK TABLES `modification_audit` WRITE;
/*!40000 ALTER TABLE `modification_audit` DISABLE KEYS */;
INSERT INTO `modification_audit` VALUES (1,'CUSTOMER',1,2,'UPDATE','occupation: Developer','occupation: Software Engineer','2026-06-29 12:34:00','2026-06-29 12:34:00'),(2,'ACCOUNT',3,3,'UPDATE','account_name: Amelia Thornton','account_name: Amelia Thornton Current','2026-06-29 12:34:00','2026-06-29 12:34:00'),(3,'ACCOUNT_STATUS',13,4,'UPDATE','status: ACTIVE','status: SUSPENDED','2026-06-29 12:34:00','2026-06-29 12:34:00'),(4,'CUSTOMER',5,1,'UPDATE','nationality: UK','nationality: British','2026-06-29 12:34:00','2026-06-29 12:34:00'),(5,'ACCOUNT_BALANCE',4,5,'UPDATE','balance: 82000.00','balance: 87500.00','2026-06-29 12:34:00','2026-06-29 12:34:00'),(6,'EMPLOYEE',6,1,'UPDATE','role: Advisor','role: Customer Advisor','2026-06-29 12:34:00','2026-06-29 12:34:00'),(7,'ACCOUNT',9,7,'UPDATE','account_type: Current','account_type: Business','2026-06-29 12:34:00','2026-06-29 12:34:00'),(8,'CUSTOMER',8,3,'UPDATE','occupation: Self Employed','occupation: Consultant','2026-06-29 12:34:00','2026-06-29 12:34:00'),(9,'TRANSACTION_HISTORY',15,8,'UPDATE','status: PENDING','status: COMPLETED','2026-06-29 12:34:00','2026-06-29 12:34:00'),(10,'BRANCH',6,11,'UPDATE','branch_name: Edinburgh','branch_name: Edinburgh Royal Mile','2026-06-29 12:34:00','2026-06-29 12:34:00'),(11,'CUSTOMER',10,2,'UPDATE','nationality: Romanian','nationality: Romanian','2026-06-29 12:34:00','2026-06-29 12:34:00'),(12,'ACCOUNT_BALANCE',1,1,'UPDATE','balance: 4000.00','balance: 4500.00','2026-06-29 12:34:00','2026-06-29 12:34:00'),(13,'EMPLOYEE',14,9,'UPDATE','role: Advisor','role: Branch Manager','2026-06-29 12:34:00','2026-06-29 12:34:00'),(14,'ACCOUNT',14,13,'UPDATE','account_name: Rossi Account','account_name: Rossi Restaurant Account','2026-06-29 12:34:00','2026-06-29 12:34:00'),(15,'CUSTOMER',15,14,'UPDATE','occupation: Government Worker','occupation: Civil Servant','2026-06-29 12:34:00','2026-06-29 12:34:00'),(16,'CUSTOMER',18,16,'INSERT',NULL,'{\"customer_name\":\"Queen Nneoma Nze\",\"date_of_birth\":\"1999-04-29\",\"customer_type\":\"Personal\",\"gender\":\"Female\",\"nationality\":\"Nigeria\",\"occupation\":\"Student\",\"id_type\":\"Passport\",\"id_number\":\"B03525992\"}','2026-07-19 21:49:53','2026-07-19 21:49:53'),(17,'ACCOUNT',101,16,'INSERT',NULL,'{\"account_number\":\"NEO-01400002\",\"account_type\":\"Current\",\"account_name\":\"Queen Nneoma Nze\",\"opening_balance\":\"0.00\"}','2026-07-19 21:51:15','2026-07-19 21:51:15');
/*!40000 ALTER TABLE `modification_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_history`
--

DROP TABLE IF EXISTS `transaction_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaction_history` (
  `transaction_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(10) unsigned NOT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `transaction_date` datetime NOT NULL,
  `reference_number` varchar(100) NOT NULL,
  `transaction_category` varchar(100) DEFAULT NULL,
  `transaction_narration` varchar(255) DEFAULT NULL,
  `counterparty_name` varchar(150) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'PENDING',
  `initiated_by` int(10) unsigned DEFAULT NULL,
  `authorised_by` int(10) unsigned DEFAULT NULL,
  `time_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`transaction_id`),
  KEY `fk_transaction_account` (`account_id`),
  KEY `idx_reference_number` (`reference_number`),
  KEY `fk_txn_initiated` (`initiated_by`),
  KEY `fk_txn_authorised` (`authorised_by`),
  CONSTRAINT `fk_transaction_account` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`),
  CONSTRAINT `fk_txn_authorised` FOREIGN KEY (`authorised_by`) REFERENCES `user` (`user_id`),
  CONSTRAINT `fk_txn_initiated` FOREIGN KEY (`initiated_by`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_history`
--

LOCK TABLES `transaction_history` WRITE;
/*!40000 ALTER TABLE `transaction_history` DISABLE KEYS */;
INSERT INTO `transaction_history` VALUES (1,1,'Credit',3000.00,'2026-06-01 09:15:00','TXN-20260601-0001','Salary','Monthly salary payment',NULL,'COMPLETED',NULL,NULL,'2026-06-29 12:34:00'),(2,1,'Debit',500.00,'2026-06-03 14:22:00','TXN-20260603-0002','Utilities','Electricity bill payment',NULL,'COMPLETED',NULL,NULL,'2026-06-29 12:34:00'),(3,2,'Credit',1000.00,'2026-06-05 10:00:00','TXN-20260605-0003','Transfer','Transfer to savings',NULL,'COMPLETED',NULL,NULL,'2026-06-29 12:34:00'),(4,3,'Debit',200.00,'2026-06-07 16:45:00','TXN-20260607-0004','Groceries','Supermarket purchase',NULL,'COMPLETED',NULL,NULL,'2026-06-29 12:34:00'),(5,4,'Credit',50000.00,'2026-06-08 08:30:00','TXN-20260608-0005','Business','Client invoice payment received',NULL,'COMPLETED',NULL,NULL,'2026-06-29 12:34:00'),(6,5,'Debit',1200.00,'2026-06-10 11:00:00','TXN-20260610-0006','Rent','Monthly rent payment',NULL,'COMPLETED',NULL,NULL,'2026-06-29 12:34:00'),(7,6,'Credit',2500.00,'2026-06-12 09:00:00','TXN-20260612-0007','Salary','Monthly salary payment',NULL,'COMPLETED',NULL,NULL,'2026-06-29 12:34:00'),(8,7,'Debit',75.00,'2026-06-13 13:30:00','TXN-20260613-0008','Subscription','Streaming service subscription',NULL,'COMPLETED',NULL,NULL,'2026-06-29 12:34:00'),(9,8,'Credit',300.00,'2026-06-14 15:00:00','TXN-20260614-0009','Transfer','Internal transfer received',NULL,'COMPLETED',NULL,NULL,'2026-06-29 12:34:00'),(10,9,'Credit',15000.00,'2026-06-15 10:45:00','TXN-20260615-0010','Business','Wholesale supplier payment',NULL,'COMPLETED',NULL,NULL,'2026-06-29 12:34:00'),(11,10,'Debit',2000.00,'2026-06-17 12:00:00','TXN-20260617-0011','Transfer','Overseas transfer',NULL,'COMPLETED',NULL,NULL,'2026-06-29 12:34:00'),(12,11,'Credit',500.00,'2026-06-18 09:30:00','TXN-20260618-0012','Salary','Monthly salary payment',NULL,'COMPLETED',NULL,NULL,'2026-06-29 12:34:00'),(13,12,'Debit',150.00,'2026-06-20 17:00:00','TXN-20260620-0013','Utilities','Gas bill payment',NULL,'COMPLETED',NULL,NULL,'2026-06-29 12:34:00'),(14,13,'Credit',3000.00,'2026-06-22 08:00:00','TXN-20260622-0014','Business','Restaurant daily takings',NULL,'COMPLETED',NULL,NULL,'2026-06-29 12:34:00'),(15,14,'Debit',400.00,'2026-06-25 14:15:00','TXN-20260625-0015','Shopping','Online retail purchase',NULL,'COMPLETED',NULL,1,'2026-06-29 12:34:00'),(16,16,'Credit',50.00,'2026-07-02 01:14:13','TXN-20260702-0001','Salary','June Salary',NULL,'COMPLETED',NULL,NULL,'2026-07-02 01:14:13'),(21,1,'Debit',500.00,'2026-07-02 01:42:34','TXN-20260702-1.844674407371E+19','Other','Internal transfer out - gift',NULL,'COMPLETED',NULL,NULL,'2026-07-02 01:42:34'),(22,16,'Credit',500.00,'2026-07-02 01:42:34','TXN-20260702-1.844674407371E+19','Other','Internal transfer in - gift',NULL,'COMPLETED',NULL,NULL,'2026-07-02 01:42:34'),(23,1,'Debit',630.00,'2026-07-02 01:57:09','TXN-20260702-5BAAFA','Business','Internal transfer out - sales',NULL,'COMPLETED',NULL,NULL,'2026-07-02 01:57:09'),(24,16,'Credit',630.00,'2026-07-02 01:57:09','TXN-20260702-5BAAFA','Business','Internal transfer in - sales',NULL,'COMPLETED',NULL,NULL,'2026-07-02 01:57:09'),(25,66,'Debit',10.00,'2026-07-07 12:30:30','TXN-20260707-6A3AA0','Transfer','Inward transfer - gift',NULL,'COMPLETED',3,1,'2026-07-07 12:30:30'),(26,16,'Credit',10.00,'2026-07-07 12:30:30','TXN-20260707-6A3AA0','Transfer','Inward transfer - gift',NULL,'COMPLETED',3,1,'2026-07-07 12:30:30'),(27,16,'Debit',10.00,'2026-07-07 12:44:54','TXN-20260707-643810','Rent','Internal transfer out - 350',NULL,'COMPLETED',3,1,'2026-07-07 12:44:54'),(28,5,'Credit',10.00,'2026-07-07 12:44:54','TXN-20260707-643810','Rent','Internal transfer in - 350',NULL,'COMPLETED',3,1,'2026-07-07 12:44:54'),(29,7,'Debit',350.00,'2026-07-07 12:45:58','TXN-20260707-6E23DF','Rent','Internal transfer out - rent',NULL,'COMPLETED',3,1,'2026-07-07 12:45:58'),(30,8,'Credit',350.00,'2026-07-07 12:45:58','TXN-20260707-6E23DF','Rent','Internal transfer in - rent',NULL,'COMPLETED',3,1,'2026-07-07 12:45:58'),(31,3,'Debit',50.00,'2026-07-09 15:35:57','TXN-20260709-D86793','Salary','Internal transfer out - gift',NULL,'REJECTED',1,1,'2026-07-09 15:35:57'),(32,8,'Credit',50.00,'2026-07-09 15:35:57','TXN-20260709-D86793','Salary','Internal transfer in - gift',NULL,'REJECTED',1,1,'2026-07-09 15:35:57');
/*!40000 ALTER TABLE `transaction_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `time_created` datetime NOT NULL DEFAULT current_timestamp(),
  `current_time` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `employee_id` (`employee_id`),
  UNIQUE KEY `username` (`username`),
  CONSTRAINT `fk_user_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,16,'admin','$2y$10$drGvqJeg7ZkIeVUIY0kdku8GpqYwY/1szxi6K4bQaJ.qbXKCH.b32','Admin',1,'2026-07-02 11:52:05','2026-07-02 11:52:05'),(2,1,'manager1','$2y$10$2P.AdNm7uFAeEzeGswz8Xev.1WOvcAqBhtJAq40ZssN9tO78O3eM.','Branch Manager',1,'2026-07-02 11:52:05','2026-07-02 11:52:05'),(3,2,'advisor1','$2y$10$uBgB6mSLYRJyWvj0RtuBWeuK0QlW8Gmg33DuO4ypLGswWbHDmhVqm','Customer Advisor',1,'2026-07-02 11:52:06','2026-07-02 11:52:06'),(4,4,'loans1','$2y$10$vKyRNbr8Xj/R8SAZMi5xd.k9E5OqcWn5aS4S/wJ3aluAZOx8Bae6a','Loans Officer',1,'2026-07-02 11:52:06','2026-07-02 11:52:06'),(5,8,'compliance1','$2y$10$dv4DWWcQ71gw/Hkov9cmfOcagxeW1TFfQY3BX.c5jldoTn1e/6ihy','Compliance Officer',1,'2026-07-02 11:52:06','2026-07-02 11:52:06');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-20 12:36:44
