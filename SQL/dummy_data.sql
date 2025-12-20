-- Dummy Data for Mealify
-- Generated on 2025-12-22
-- Password for all users: Mealify123!
-- Hash: $2y$10$HRzrTBekHalq40Fh34knx.qFrFSqL65wmrm07hudNtLniJP6y.YM2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- 1. Insert Users
INSERT INTO `users` (`name`, `email`, `password`, `role`, `is_verified`, `is_active`, `weight_kg`, `goal_diet`, `goal_bulking`, `streak_freeze`, `created_at`) VALUES
('Budi Santoso', 'budi@example.com', '$2y$10$HRzrTBekHalq40Fh34knx.qFrFSqL65wmrm07hudNtLniJP6y.YM2', 'mahasiswa', 1, 1, 70, 0, 1, 5, NOW()),
('Siti Aminah', 'siti@example.com', '$2y$10$HRzrTBekHalq40Fh34knx.qFrFSqL65wmrm07hudNtLniJP6y.YM2', 'mahasiswa', 1, 1, 55, 1, 0, 5, NOW()),
('Rina Hartati', 'rina@example.com', '$2y$10$HRzrTBekHalq40Fh34knx.qFrFSqL65wmrm07hudNtLniJP6y.YM2', 'mahasiswa', 1, 1, 60, 1, 0, 4, NOW()),
('Agus Setiawan', 'agus@example.com', '$2y$10$HRzrTBekHalq40Fh34knx.qFrFSqL65wmrm07hudNtLniJP6y.YM2', 'mahasiswa', 1, 1, 75, 0, 1, 5, NOW()),
('Dewi Lestari', 'dewi@example.com', '$2y$10$HRzrTBekHalq40Fh34knx.qFrFSqL65wmrm07hudNtLniJP6y.YM2', 'mahasiswa', 1, 1, 50, 1, 0, 5, NOW()),
('Eko Prasetyo', 'eko@example.com', '$2y$10$HRzrTBekHalq40Fh34knx.qFrFSqL65wmrm07hudNtLniJP6y.YM2', 'mahasiswa', 1, 1, 80, 0, 1, 3, NOW()),
('Sri Wahyuni', 'sri@example.com', '$2y$10$HRzrTBekHalq40Fh34knx.qFrFSqL65wmrm07hudNtLniJP6y.YM2', 'mahasiswa', 1, 1, 65, 0, 0, 5, NOW()),
('Joko Widodo', 'joko@example.com', '$2y$10$HRzrTBekHalq40Fh34knx.qFrFSqL65wmrm07hudNtLniJP6y.YM2', 'mahasiswa', 1, 1, 72, 0, 1, 5, NOW()),
('Megawati Putri', 'mega@example.com', '$2y$10$HRzrTBekHalq40Fh34knx.qFrFSqL65wmrm07hudNtLniJP6y.YM2', 'mahasiswa', 1, 1, 68, 1, 0, 5, NOW()),
('Tono Sudarsono', 'tono@example.com', '$2y$10$HRzrTBekHalq40Fh34knx.qFrFSqL65wmrm07hudNtLniJP6y.YM2', 'mahasiswa', 1, 1, 78, 0, 1, 5, NOW());

-- 2. Insert Meal Logs (Sample for last 3 days for all new users)
-- Helper procedure-like block logic is hard in standard SQL script without stored procs.
-- We will use INSERT INTO ... SELECT pattern with hardcoded dates relative to NOW() or fixed dates.
-- Using fixed dates for simplicity or relative to CURRENT_DATE.

INSERT INTO `meal_logs` (`user_id`, `meal_type`, `meal_name`, `log_date`, `calories`, `protein`, `carbs`, `fat`, `logged_at`)
SELECT id, 'breakfast', 'Nasi Uduk', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 450, 10, 60, 15, CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 07:00:00') FROM users WHERE email IN ('budi@example.com', 'siti@example.com', 'rina@example.com', 'agus@example.com', 'dewi@example.com', 'eko@example.com', 'sri@example.com', 'joko@example.com', 'mega@example.com', 'tono@example.com');

INSERT INTO `meal_logs` (`user_id`, `meal_type`, `meal_name`, `log_date`, `calories`, `protein`, `carbs`, `fat`, `logged_at`)
SELECT id, 'lunch', 'Ayam Bakar', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 600, 30, 40, 20, CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 12:30:00') FROM users WHERE email IN ('budi@example.com', 'siti@example.com', 'rina@example.com', 'agus@example.com', 'dewi@example.com', 'eko@example.com', 'sri@example.com', 'joko@example.com', 'mega@example.com', 'tono@example.com');

INSERT INTO `meal_logs` (`user_id`, `meal_type`, `meal_name`, `log_date`, `calories`, `protein`, `carbs`, `fat`, `logged_at`)
SELECT id, 'dinner', 'Mie Goreng', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 500, 12, 70, 18, CONCAT(DATE_SUB(CURDATE(), INTERVAL 2 DAY), ' 19:00:00') FROM users WHERE email IN ('budi@example.com', 'siti@example.com', 'rina@example.com', 'agus@example.com', 'dewi@example.com', 'eko@example.com', 'sri@example.com', 'joko@example.com', 'mega@example.com', 'tono@example.com');

-- Repeat for yesterday
INSERT INTO `meal_logs` (`user_id`, `meal_type`, `meal_name`, `log_date`, `calories`, `protein`, `carbs`, `fat`, `logged_at`)
SELECT id, 'breakfast', 'Bubur Ayam', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 350, 15, 45, 10, CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 07:15:00') FROM users WHERE email IN ('budi@example.com', 'siti@example.com', 'rina@example.com', 'agus@example.com', 'dewi@example.com', 'eko@example.com', 'sri@example.com', 'joko@example.com', 'mega@example.com', 'tono@example.com');

INSERT INTO `meal_logs` (`user_id`, `meal_type`, `meal_name`, `log_date`, `calories`, `protein`, `carbs`, `fat`, `logged_at`)
SELECT id, 'lunch', 'Soto Ayam', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 450, 20, 30, 15, CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 13:00:00') FROM users WHERE email IN ('budi@example.com', 'siti@example.com', 'rina@example.com', 'agus@example.com', 'dewi@example.com', 'eko@example.com', 'sri@example.com', 'joko@example.com', 'mega@example.com', 'tono@example.com');

INSERT INTO `meal_logs` (`user_id`, `meal_type`, `meal_name`, `log_date`, `calories`, `protein`, `carbs`, `fat`, `logged_at`)
SELECT id, 'dinner', 'Capcay', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 300, 5, 20, 10, CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY), ' 19:30:00') FROM users WHERE email IN ('budi@example.com', 'siti@example.com', 'rina@example.com', 'agus@example.com', 'dewi@example.com', 'eko@example.com', 'sri@example.com', 'joko@example.com', 'mega@example.com', 'tono@example.com');

-- Repeat for today (Partial: Breakfast only for some)
INSERT INTO `meal_logs` (`user_id`, `meal_type`, `meal_name`, `log_date`, `calories`, `protein`, `carbs`, `fat`, `logged_at`)
SELECT id, 'breakfast', 'Roti Bakar', CURDATE(), 250, 5, 40, 8, CONCAT(CURDATE(), ' 08:00:00') FROM users WHERE email IN ('budi@example.com', 'siti@example.com', 'agus@example.com', 'dewi@example.com', 'sri@example.com');

-- 3. Insert User Activity (Consistent with logs)
-- 2 days ago (Complete)
INSERT IGNORE INTO `user_activity` (`user_id`, `activity_date`, `is_freeze`, `created_at`)
SELECT id, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 0, NOW() FROM users WHERE email IN ('budi@example.com', 'siti@example.com', 'rina@example.com', 'agus@example.com', 'dewi@example.com', 'eko@example.com', 'sri@example.com', 'joko@example.com', 'mega@example.com', 'tono@example.com');

-- Yesterday (Complete)
INSERT IGNORE INTO `user_activity` (`user_id`, `activity_date`, `is_freeze`, `created_at`)
SELECT id, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 0, NOW() FROM users WHERE email IN ('budi@example.com', 'siti@example.com', 'rina@example.com', 'agus@example.com', 'dewi@example.com', 'eko@example.com', 'sri@example.com', 'joko@example.com', 'mega@example.com', 'tono@example.com');

-- 4. Insert Meal Plans
INSERT INTO `meal_plans` (`user_id`, `food_name`, `meal_type`, `meal_time`, `notes`, `is_notified`)
SELECT id, 'Salad Buah', 'breakfast', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'Diet sehat', 0 FROM users WHERE email IN ('siti@example.com', 'dewi@example.com', 'mega@example.com');

INSERT INTO `meal_plans` (`user_id`, `food_name`, `meal_type`, `meal_time`, `notes`, `is_notified`)
SELECT id, 'Steak Sapi', 'dinner', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'Cheat day', 0 FROM users WHERE email IN ('budi@example.com', 'agus@example.com', 'eko@example.com', 'tono@example.com');

-- 5. Insert Favorites
INSERT INTO `favorites` (`user_id`, `recipe_uri`, `label`, `image`, `source`, `url`, `calories`, `created_at`)
SELECT id, 'http://www.edamam.com/ontologies/edamam.owl#recipe_123', 'Nasi Goreng Spesial', 'https://example.com/nasigoreng.jpg', 'Mealify Kitchen', 'https://example.com/recipe/1', 600, NOW() FROM users WHERE email IN ('budi@example.com', 'rina@example.com', 'joko@example.com');

INSERT INTO `favorites` (`user_id`, `recipe_uri`, `label`, `image`, `source`, `url`, `calories`, `created_at`)
SELECT id, 'http://www.edamam.com/ontologies/edamam.owl#recipe_456', 'Smoothie Bowl', 'https://example.com/smoothie.jpg', 'Healthy Life', 'https://example.com/recipe/2', 300, NOW() FROM users WHERE email IN ('siti@example.com', 'dewi@example.com', 'mega@example.com');

COMMIT;
