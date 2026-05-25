-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: localhost    Database: budget_monitoring
-- ------------------------------------------------------
-- Server version	8.4.7

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `account_types`
--

DROP TABLE IF EXISTS `account_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'wallet',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_types`
--

LOCK TABLES `account_types` WRITE;
/*!40000 ALTER TABLE `account_types` DISABLE KEYS */;
INSERT INTO `account_types` VALUES (1,'Cash','banknotes','2026-05-24 17:03:46','2026-05-24 17:03:46'),(2,'Bank Account','building-library','2026-05-24 17:03:46','2026-05-24 17:03:46'),(3,'E-Wallet','device-phone-mobile','2026-05-24 17:03:46','2026-05-24 17:03:46'),(4,'Credit Card','credit-card','2026-05-24 17:03:46','2026-05-24 17:03:46'),(5,'Savings Account','currency-dollar','2026-05-24 17:03:46','2026-05-24 17:03:46');
/*!40000 ALTER TABLE `account_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `account_type_id` bigint unsigned NOT NULL,
  `person_id` bigint unsigned DEFAULT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `initial_balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `current_balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#1E40AF',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accounts_account_type_id_index` (`account_type_id`),
  KEY `accounts_is_active_index` (`is_active`),
  KEY `accounts_person_id_index` (`person_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts`
--

LOCK TABLES `accounts` WRITE;
/*!40000 ALTER TABLE `accounts` DISABLE KEYS */;
INSERT INTO `accounts` VALUES (1,1,1,'Cash On Hand','Actual cash available',0.00,8762.00,'#1E40AF',1,'2026-05-24 17:09:13','2026-05-24 19:40:42',NULL),(2,2,1,'BPI','For online payments',0.00,2135.95,'#1E40AF',1,'2026-05-24 17:09:42','2026-05-24 18:46:57',NULL),(3,3,1,'G-Cash','Available balance',0.00,344.00,'#1E40AF',1,'2026-05-24 17:10:54','2026-05-24 18:48:51',NULL),(4,3,1,'Maya','Savings',174.50,174.50,'#1E40AF',1,'2026-05-24 17:12:06','2026-05-24 17:12:06',NULL),(5,2,1,'TAGUM ATM Card','TAGUM Pinoy Coop',0.00,719.78,'#1E40AF',1,'2026-05-24 17:13:09','2026-05-24 19:06:41',NULL),(6,2,1,'Land Bank ATM Card','SNSU Payroll',0.00,2544.11,'#1E40AF',1,'2026-05-24 17:15:13','2026-05-24 18:21:56',NULL),(7,3,1,'ShopeePay','SPayLater Payments',38.42,38.42,'#1E40AF',1,'2026-05-24 17:21:34','2026-05-24 17:21:34',NULL),(8,1,2,'Cash On Hand','Actual available cash',0.00,2265.00,'#1E40AF',1,'2026-05-24 17:23:31','2026-05-24 19:32:21',NULL),(9,5,3,'Savings In a Gallon','20 pesos savings',0.00,0.00,'#1E40AF',1,'2026-05-24 17:27:17','2026-05-24 17:27:17',NULL),(10,3,2,'ShoppeePay','SPayLater Payments',0.00,0.00,'#1E40AF',1,'2026-05-24 17:28:00','2026-05-24 17:28:00',NULL);
/*!40000 ALTER TABLE `accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_goals`
--

DROP TABLE IF EXISTS `budget_goals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_goals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `month` tinyint NOT NULL,
  `year` smallint NOT NULL,
  `limit_amount` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `budget_goals_category_id_month_year_unique` (`category_id`,`month`,`year`),
  KEY `budget_goals_category_id_index` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_goals`
--

LOCK TABLES `budget_goals` WRITE;
/*!40000 ALTER TABLE `budget_goals` DISABLE KEYS */;
INSERT INTO `budget_goals` VALUES (1,7,5,2026,2000.00,'2026-05-24 18:24:48','2026-05-24 18:24:48');
/*!40000 ALTER TABLE `budget_goals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('income','expense','both') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'expense',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tag',
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#1E40AF',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categories_type_index` (`type`),
  KEY `categories_is_active_index` (`is_active`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Salary','income','banknotes','#16A34A',1,'2026-05-24 17:03:47','2026-05-24 17:03:47',NULL),(2,'Freelance','income','computer-desktop','#0D9488',1,'2026-05-24 17:03:47','2026-05-24 17:03:47',NULL),(3,'Business Income','income','briefcase','#7C3AED',1,'2026-05-24 17:03:47','2026-05-24 17:03:47',NULL),(4,'Investment Returns','income','chart-bar','#2563EB',1,'2026-05-24 17:03:47','2026-05-24 17:03:47',NULL),(5,'Other Income','income','plus-circle','#64748B',1,'2026-05-24 17:03:47','2026-05-24 17:03:47',NULL),(6,'Food & Dining','expense','cake','#F97316',1,'2026-05-24 17:03:47','2026-05-24 17:03:47',NULL),(7,'Transportation','expense','truck','#EAB308',1,'2026-05-24 17:03:47','2026-05-24 17:03:47',NULL),(8,'Utilities','expense','bolt','#EF4444',1,'2026-05-24 17:03:47','2026-05-24 17:03:47',NULL),(9,'Rent','expense','home','#8B5CF6',1,'2026-05-24 17:03:47','2026-05-24 17:03:47',NULL),(10,'Shopping','expense','shopping-bag','#EC4899',1,'2026-05-24 17:03:47','2026-05-24 17:03:47',NULL),(11,'Health & Medical','expense','heart','#EF4444',1,'2026-05-24 17:03:47','2026-05-24 17:03:47',NULL),(12,'Entertainment','expense','film','#A855F7',1,'2026-05-24 17:03:47','2026-05-24 17:03:47',NULL),(13,'Education','expense','academic-cap','#3B82F6',1,'2026-05-24 17:03:47','2026-05-24 17:03:47',NULL),(14,'Groceries','expense','shopping-cart','#22C55E',1,'2026-05-24 17:03:47','2026-05-24 17:03:47',NULL),(15,'Personal Care','expense','sparkles','#F472B6',1,'2026-05-24 17:03:47','2026-05-24 17:03:47',NULL),(16,'Savings Transfer','expense','arrow-path','#0EA5E9',1,'2026-05-24 17:03:47','2026-05-24 17:03:47',NULL),(17,'Other Expenses','expense','ellipsis-horizontal','#94A3B8',1,'2026-05-24 17:03:47','2026-05-24 17:03:47',NULL),(18,'Loan','expense','banknotes','#E11D48',1,'2026-05-24 17:42:31','2026-05-24 17:42:31',NULL),(19,'Bank Charge','expense','building-library','#0EA5E9',1,'2026-05-24 17:54:15','2026-05-24 17:54:15',NULL),(20,'Tithes & Love Offering','expense','gift','#0EA5E9',1,'2026-05-24 18:32:39','2026-05-24 19:20:49',NULL),(21,'Clothing','expense','shopping-bag','#0D9488',1,'2026-05-24 19:15:09','2026-05-24 19:15:09',NULL),(22,'House Construction','expense','home','#475569',1,'2026-05-24 19:33:27','2026-05-24 19:41:19',NULL);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2025_01_01_000001_create_account_types_table',1),(5,'2025_01_01_000002_create_accounts_table',1),(6,'2025_01_01_000003_create_categories_table',1),(7,'2025_01_01_000004_create_recurring_transactions_table',1),(8,'2025_01_01_000005_create_transactions_table',1),(9,'2025_01_01_000006_create_budget_goals_table',1),(10,'2025_01_01_000007_create_persons_table',1),(11,'2025_01_01_000008_add_person_id_to_accounts_table',1),(12,'2026_05_22_061304_add_indexes_to_transactions_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `persons`
--

DROP TABLE IF EXISTS `persons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `persons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#6366F1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `persons_is_active_index` (`is_active`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `persons`
--

LOCK TABLES `persons` WRITE;
/*!40000 ALTER TABLE `persons` DISABLE KEYS */;
INSERT INTO `persons` VALUES (1,'Andrew','#10B981',1,'2026-05-24 17:06:29','2026-05-24 17:06:29',NULL),(2,'Nenia','#8B5CF6',1,'2026-05-24 17:06:36','2026-05-24 17:06:36',NULL),(3,'Nyla','#F59E0B',1,'2026-05-24 17:06:49','2026-05-24 17:06:49',NULL);
/*!40000 ALTER TABLE `persons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_transactions`
--

DROP TABLE IF EXISTS `recurring_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recurring_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `account_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `type` enum('income','expense') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'expense',
  `amount` decimal(15,2) NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `frequency` enum('daily','weekly','monthly','yearly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `next_due_date` date NOT NULL,
  `last_generated_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recurring_transactions_account_id_index` (`account_id`),
  KEY `recurring_transactions_category_id_index` (`category_id`),
  KEY `recurring_transactions_next_due_date_index` (`next_due_date`),
  KEY `recurring_transactions_is_active_index` (`is_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_transactions`
--

LOCK TABLES `recurring_transactions` WRITE;
/*!40000 ALTER TABLE `recurring_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `recurring_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('5dGZagdZ0706QZ7ByzSk38XYXN3yOejuvoN1I0FQ',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJoTDVMV3lDWkhWYmZqOGpwZllKTEhLeFFXU3M2NWtuY1RLdFFmdnZmIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9hY2NvdW50cyIsInJvdXRlIjoiYWNjb3VudHMuaW5kZXgifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1779684071),('XtEUmuG08UsWNFG2yu6kD3ZNSjZ9nVpuUeNvfEkl',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.121.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36','eyJfdG9rZW4iOiJaVkk0Y1c0MkZzblNZRXZGaHY3eTZBME02WmVBdnNSM3NmMmpvUGZOIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9kYXNoYm9hcmQiLCJyb3V0ZSI6ImRhc2hib2FyZCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1779682547);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `account_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `type` enum('income','expense','transfer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'expense',
  `amount` decimal(15,2) NOT NULL,
  `transaction_date` date NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `reference_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transfer_to_account_id` bigint unsigned DEFAULT NULL,
  `recurring_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transactions_recurring_id_foreign` (`recurring_id`),
  KEY `transactions_account_id_index` (`account_id`),
  KEY `transactions_category_id_index` (`category_id`),
  KEY `transactions_type_index` (`type`),
  KEY `transactions_transaction_date_index` (`transaction_date`),
  KEY `transactions_transfer_to_account_id_index` (`transfer_to_account_id`)
) ENGINE=MyISAM AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,6,1,'income',10712.11,'2026-05-13','SNSU Basic Pay','Basic Pay',NULL,NULL,NULL,'2026-05-24 17:31:01','2026-05-24 17:35:03',NULL),(2,6,NULL,'transfer',10510.00,'2026-05-13','LandBank to Tagum','SNSU Basic Pay',NULL,5,NULL,'2026-05-24 17:40:13','2026-05-24 17:40:13',NULL),(3,5,18,'expense',4058.01,'2026-05-13','Tagum Cash Loan','Payment',NULL,NULL,NULL,'2026-05-24 17:44:20','2026-05-24 17:44:20',NULL),(4,5,NULL,'transfer',6000.00,'2026-05-13','Withdraw Cash','Remaining SNSU Basic Pay',NULL,1,NULL,'2026-05-24 17:47:40','2026-05-24 17:47:40',NULL),(5,5,5,'income',526.79,'2026-05-13','Previous ATM Balance','From previous balance',NULL,NULL,NULL,'2026-05-24 17:50:32','2026-05-24 17:50:32',NULL),(6,5,19,'expense',10.00,'2026-05-13','LandBank to TAGUM','ATM to ATM',NULL,NULL,NULL,'2026-05-24 17:55:58','2026-05-24 17:55:58',NULL),(7,5,19,'expense',18.00,'2026-05-13','TAGUM ATM to Cash','BPI ATM Machine charge',NULL,NULL,NULL,'2026-05-24 18:00:25','2026-05-24 18:00:25',NULL),(8,6,1,'income',19716.00,'2026-05-15','Bonus SNSU','Midyear Bonus',NULL,NULL,NULL,'2026-05-24 18:03:02','2026-05-24 18:03:02',NULL),(9,6,NULL,'transfer',19810.00,'2026-05-15','LandBank to TAGUM','Midyear Bonus',NULL,5,NULL,'2026-05-24 18:05:17','2026-05-24 18:07:46',NULL),(10,5,19,'expense',11.00,'2026-05-16','Landbank to TAGUM','Midyear transaction',NULL,NULL,NULL,'2026-05-24 18:12:34','2026-05-24 18:12:34',NULL),(11,5,NULL,'transfer',10000.00,'2026-05-16','TAGUM to Cash on hand','For Cash availability',NULL,1,NULL,'2026-05-24 18:14:02','2026-05-24 18:14:02',NULL),(12,5,19,'expense',15.00,'2026-05-25','TAGUM ATM to Cash','East West Bank ATM charge',NULL,NULL,NULL,'2026-05-24 18:15:58','2026-05-24 19:06:29',NULL),(13,5,NULL,'transfer',10000.00,'2026-05-18','TAGUM to Cash on Hand','For Cash availability',NULL,1,NULL,'2026-05-24 18:17:28','2026-05-24 18:17:28',NULL),(14,5,19,'expense',15.00,'2026-05-18','TAGUM to Cash','East West ATM charge',NULL,NULL,NULL,'2026-05-24 18:18:24','2026-05-24 19:06:41',NULL),(15,6,5,'income',2436.00,'2026-05-20','EMDS CM','LandBank Description',NULL,NULL,NULL,'2026-05-24 18:21:56','2026-05-24 18:21:56',NULL),(16,1,7,'expense',400.00,'2026-05-16','Gasoline Nmax','Petron',NULL,NULL,NULL,'2026-05-24 18:23:25','2026-05-24 18:23:25',NULL),(17,1,7,'expense',350.00,'2026-05-23','Gasoline Nmax','Petron',NULL,NULL,NULL,'2026-05-24 18:24:31','2026-05-24 18:24:31',NULL),(18,1,NULL,'transfer',2000.00,'2026-05-15','Online Transaction','Home Credit Payment',NULL,2,NULL,'2026-05-24 18:26:56','2026-05-24 18:26:56',NULL),(19,1,20,'expense',2000.00,'2026-05-17','Basic + Half Midyear','T 1600 - O 400',NULL,NULL,NULL,'2026-05-24 18:34:28','2026-05-24 18:34:28',NULL),(20,1,20,'expense',1000.00,'2026-05-24','Half Midyear','T 1000',NULL,NULL,NULL,'2026-05-24 18:35:12','2026-05-24 18:35:12',NULL),(21,1,NULL,'transfer',2000.00,'2026-05-17','CSC Filing Butuan','Naynay CSC Filing for Exam',NULL,8,NULL,'2026-05-24 18:38:53','2026-05-24 18:38:53',NULL),(22,1,NULL,'transfer',2000.00,'2026-05-18','Siargao Vacation','Naynay-Nyla Siargao Vacation',NULL,8,NULL,'2026-05-24 18:39:50','2026-05-24 18:41:38',NULL),(23,2,5,'income',135.95,'2026-05-25','Previous Balance','Remaining previous balance',NULL,NULL,NULL,'2026-05-24 18:46:57','2026-05-24 18:46:57',NULL),(24,3,5,'income',344.00,'2026-05-25','Previous Balance','Previous balance available',NULL,NULL,NULL,'2026-05-24 18:48:51','2026-05-24 18:48:51',NULL),(25,1,6,'expense',780.00,'2026-05-25','Tuna Tinola','Tuna Republic',NULL,NULL,NULL,'2026-05-24 18:51:05','2026-05-24 18:51:05',NULL),(26,1,6,'expense',500.00,'2026-05-25','Letchon Manok, Water Melon','Dinner sa ila ninong Roy',NULL,NULL,NULL,'2026-05-24 18:52:12','2026-05-24 18:52:12',NULL),(27,1,6,'expense',225.00,'2026-05-25','Egg','1 tray  (Anao-aon)',NULL,NULL,NULL,'2026-05-24 18:53:15','2026-05-24 18:53:15',NULL),(28,1,21,'expense',2600.00,'2026-05-25','SNSU Uniform','Uniform Polo - Pants',NULL,NULL,NULL,'2026-05-24 19:16:02','2026-05-24 19:16:02',NULL),(29,1,6,'expense',232.00,'2026-05-25','Pork Topsilog 4x','KUDUS',NULL,NULL,NULL,'2026-05-24 19:23:06','2026-05-24 19:23:06',NULL),(30,1,6,'expense',200.00,'2026-05-25','ICT Chip-in','Lunch, Snack, Dinner',NULL,NULL,NULL,'2026-05-24 19:24:25','2026-05-24 19:24:25',NULL),(31,1,6,'expense',18.00,'2026-05-25','Snack Nyla','Rebisco Biscuit',NULL,NULL,NULL,'2026-05-24 19:27:51','2026-05-24 19:27:51',NULL),(32,8,17,'expense',1685.00,'2026-05-25','CSC Filing (Butuan)','Mix expenses',NULL,NULL,NULL,'2026-05-24 19:30:23','2026-05-24 19:30:23',NULL),(33,8,6,'expense',50.00,'2026-05-25','Nyla Snack - Food','Sud.an - Biscuit',NULL,NULL,NULL,'2026-05-24 19:32:21','2026-05-24 19:32:21',NULL),(34,1,22,'expense',396.00,'2026-05-25','Ninong Roy Labor - Snack','Labor 3.5 hr - Snack biscuit',NULL,NULL,NULL,'2026-05-24 19:34:51','2026-05-24 19:34:51',NULL),(35,1,22,'expense',700.00,'2026-05-25','Ninong Roy Labor','7 hr.',NULL,NULL,NULL,'2026-05-24 19:35:55','2026-05-24 19:35:55',NULL),(36,1,22,'expense',100.00,'2026-05-25','Ninong Roy Labor','Cash Advance',NULL,NULL,NULL,'2026-05-24 19:36:22','2026-05-24 19:36:22',NULL),(37,1,6,'expense',130.00,'2026-05-25','Pork Liempo','Half Order',NULL,NULL,NULL,'2026-05-24 19:37:24','2026-05-24 19:37:24',NULL),(38,1,14,'expense',1607.00,'2026-05-25','Nyla Wipes, Oil, Alchohol, etc.','Gaisano',NULL,NULL,NULL,'2026-05-24 19:40:42','2026-05-24 19:40:42',NULL);
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
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

-- Dump completed on 2026-05-25 12:49:28
