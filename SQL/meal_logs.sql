-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 18, 2025 at 01:26 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mealify`
--

-- --------------------------------------------------------

--
-- Table structure for table `meal_logs`
--

CREATE TABLE `meal_logs` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `meal_type` enum('breakfast','lunch','dinner') NOT NULL,
  `meal_name` varchar(255) DEFAULT NULL,
  `log_date` date NOT NULL,
  `logged_at` datetime DEFAULT NULL,
  `calories` double DEFAULT NULL,
  `protein` double DEFAULT NULL,
  `carbs` double DEFAULT NULL,
  `fat` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Triggers `meal_logs`
--
DELIMITER $$
CREATE TRIGGER `trg_meal_logs_after_insert` AFTER INSERT ON `meal_logs` FOR EACH ROW BEGIN
    DECLARE meal_count INT;

    SELECT COUNT(DISTINCT meal_type)
    INTO meal_count
    FROM meal_logs
    WHERE user_id = NEW.user_id
      AND log_date = NEW.log_date
      AND meal_type IN ('breakfast','lunch','dinner');

    IF meal_count = 3 THEN
        INSERT IGNORE INTO user_activity (user_id, activity_date)
        VALUES (NEW.user_id, NEW.log_date);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_meal_logs_after_update` AFTER UPDATE ON `meal_logs` FOR EACH ROW BEGIN
    DECLARE meal_count INT;

    SELECT COUNT(DISTINCT meal_type)
    INTO meal_count
    FROM meal_logs
    WHERE user_id = NEW.user_id
      AND log_date = NEW.log_date
      AND meal_type IN ('breakfast','lunch','dinner');

    IF meal_count = 3 THEN
        INSERT IGNORE INTO user_activity (user_id, activity_date)
        VALUES (NEW.user_id, NEW.log_date);
    END IF;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `meal_logs`
--
ALTER TABLE `meal_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `meal_logs`
--
ALTER TABLE `meal_logs`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `meal_logs`
--
ALTER TABLE `meal_logs`
  ADD CONSTRAINT `fk_meal_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
