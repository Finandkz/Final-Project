<?php
namespace App\Controllers;

use App\Helpers\Session;
use App\Config\Database;
use App\Helpers\Env;
use App\Classes\ApiClientEdamamFood;
use DateTime;
use Throwable;

class StreakController
{
    private $conn;
    private $user;

    public function __construct()
    {
        Session::start();
        $this->user = Session::get('user');

        if (!$this->user) {
            header("Location: ../public/login.php");
            exit;
        }

        Env::load();
        date_default_timezone_set(Env::get('APP_TIMEZONE', 'Asia/Jakarta'));

        $db = new Database();
        $this->conn = $db->connect();
    }

    public function handle(): array
    {
        $errors = [];
        $success = null;

        $userId = (int)$this->user['id'];
        $today  = (new DateTime('today'))->format('Y-m-d');
        
        $this->refillMonthlyFreeze($userId);
        $this->reconcileStreak($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::validateCsrfToken($_POST['_csrf'] ?? '')) {
                $errors[] = "Invalid CSRF token. Please refresh the page.";
                return [
                    'errors' => $errors,
                    'success' => $success,
                    'todayLogs' => $this->getTodayLogs($userId, $today),
                    'streak' => $this->calculateStreak($userId),
                    'streakActive' => $this->isTodayActive($userId, $today),
                    'freezeLeft' => 0, // placeholder or fetch
                    'year' => (int)($_GET['year'] ?? date('Y')),
                    'activeDates' => []
                ];
            }

            $mealType = $_POST['meal_type'] ?? '';
            $mealName = trim($_POST['food_name'] ?? '');

            $canonical = $this->normalizeMealType($mealType);

            if (!$canonical) $errors[] = 'Invalid meal type.';
            if ($mealName === '') $errors[] = 'Food name is required.';
            if (!$errors && !$this->isWithinValidWindow($canonical, new DateTime())) {
                $errors[] = 'Inappropriate meal times.';
            }

            if (!$errors) {
                $stmt = $this->conn->prepare(
                    "SELECT id FROM meal_logs
                     WHERE user_id = ? AND log_date = ? AND meal_type = ?
                     LIMIT 1"
                );
                $stmt->bind_param("iss", $userId, $today, $canonical);
                $stmt->execute();
                $existing = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                $nutri = $this->fetchNutritionFromFoodDb($mealName);

                if ($existing) {
                    $stmt = $this->conn->prepare(
                        "UPDATE meal_logs
                         SET meal_name = ?, calories = ?, protein = ?, carbs = ?, fat = ?, logged_at = NOW()
                         WHERE id = ?"
                    );
                    $stmt->bind_param(
                        "sddddi",
                        $mealName,
                        $nutri['cal'],
                        $nutri['prot'],
                        $nutri['carb'],
                        $nutri['fat'],
                        $existing['id']
                    );
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $stmt = $this->conn->prepare(
                        "INSERT INTO meal_logs
                         (user_id, meal_type, meal_name, log_date, logged_at,
                          calories, protein, carbs, fat)
                         VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?)"
                    );
                    $stmt->bind_param(
                        "isssdddd",
                        $userId,
                        $canonical,
                        $mealName,
                        $today,
                        $nutri['cal'],
                        $nutri['prot'],
                        $nutri['carb'],
                        $nutri['fat']
                    );
                    $stmt->execute();
                    $stmt->close();
                }
                $this->updateDailyActivity($userId, $today);

                header("Location: streak.php?success=1");
                exit;
            }
        }
        $todayLogs   = $this->getTodayLogs($userId, $today);
        $todayActive = $this->isTodayActive($userId, $today);
        $streak = $this->calculateStreak($userId);

        $stmt = $this->conn->prepare("SELECT streak_freeze FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($freezeLeft);
        $stmt->fetch();
        $stmt->close();

        $year = (int)($_GET['year'] ?? date('Y'));
        $activeDates = $this->getActiveDates($userId, $year);

        return [
            'errors' => $errors,
            'success' => $success,
            'todayLogs' => $todayLogs,
            'streak' => $streak,
            'streakActive' => $todayActive,
            'freezeLeft' => $freezeLeft,
            'year' => $year,
            'activeDates' => $activeDates
        ];
    }

    private function refillMonthlyFreeze(int $userId): void
    {
        $stmt = $this->conn->prepare(
            "SELECT last_freeze_refill FROM users WHERE id = ?"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $last = $stmt->get_result()->fetch_assoc()['last_freeze_refill'] ?? null;
        $stmt->close();

        $currentMonth = date('Y-m');

        if ($last && date('Y-m', strtotime($last)) === $currentMonth) {
            return; 
        }

        $stmt = $this->conn->prepare(
            "UPDATE users
            SET streak_freeze = 5,
                last_freeze_refill = ?
            WHERE id = ?"
        );
        $today = date('Y-m-d');
        $stmt->bind_param("si", $today, $userId);
        $stmt->execute();
        $stmt->close();
    }

    private function isTodayActive(int $userId, string $today): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT 1 FROM user_activity
            WHERE user_id = ? AND activity_date = ?
            LIMIT 1"
        );
        $stmt->bind_param("is", $userId, $today);
        $stmt->execute();

        $active = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $active;
    }

    private function normalizeMealType(string $m): ?string
    {
        return match (strtolower($m)) {
            'breakfast','sarapan' => 'breakfast',
            'lunch','makan siang','makan_siang' => 'lunch',
            'dinner','makan malam','makan_malam' => 'dinner',
            default => null
        };
    }

    private function isWithinValidWindow(string $type, DateTime $now): bool
    {
        $t = $now->format('H:i');
        return match ($type) {
            'breakfast' => $t >= '05:00' && $t <= '11:00',
            'lunch' => $t >= '11:00' && $t <= '17:00',
            'dinner' => $t >= '17:00' && $t <= '23:00',
            default => false
        };
    }

    private function updateDailyActivity(int $userId, string $date): void
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(DISTINCT meal_type) cnt
             FROM meal_logs WHERE user_id = ? AND log_date = ?"
        );
        $stmt->bind_param("is", $userId, $date);
        $stmt->execute();
        $cnt = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
        $stmt->close();

        if ($cnt === 3) {
            $stmt = $this->conn->prepare(
                "INSERT IGNORE INTO user_activity (user_id, activity_date)
                 VALUES (?, ?)"
            );
            $stmt->bind_param("is", $userId, $date);
            $stmt->execute();
            $stmt->close();
        }
    }

    private function getTodayLogs(int $userId, string $today): array
    {
        $stmt = $this->conn->prepare(
            "SELECT meal_type, meal_name, logged_at
             FROM meal_logs WHERE user_id = ? AND log_date = ?"
        );
        $stmt->bind_param("is", $userId, $today);
        $stmt->execute();
        $res = $stmt->get_result();

        $logs = ['breakfast'=>null,'lunch'=>null,'dinner'=>null];
        while ($r = $res->fetch_assoc()) {
            $logs[$r['meal_type']] = $r;
        }
        $stmt->close();
        return $logs;
    }

    private function getActiveDates(int $userId, int $year): array
    {
        $stmt = $this->conn->prepare(
            "SELECT activity_date FROM user_activity
             WHERE user_id = ? AND YEAR(activity_date) = ?"
        );
        $stmt->bind_param("ii", $userId, $year);
        $stmt->execute();
        $res = $stmt->get_result();

        $dates = [];
        while ($r = $res->fetch_assoc()) $dates[] = $r['activity_date'];
        $stmt->close();
        return $dates;
    }

    private function calculateStreak(int $userId): int
    {
        $stmt = $this->conn->prepare(
            "SELECT streak_freeze FROM users WHERE id = ?"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $freezeLeft = (int)($stmt->get_result()->fetch_assoc()['streak_freeze'] ?? 0);
        $stmt->close();

        $stmt = $this->conn->prepare(
            "SELECT activity_date, is_freeze
            FROM user_activity
            WHERE user_id = ?
            ORDER BY activity_date DESC"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            $stmt->close();
            return 0;
        }

        $dates = [];
        while ($r = $res->fetch_assoc()) {
            $dates[] = $r;
        }
        $stmt->close();
        $today = new DateTime('today');

        if ($this->isTodayActive($userId, $today->format('Y-m-d'))) {
            $expected = clone $today;
        } else {
            $expected = (clone $today)->modify('-1 day');
        }
        $streak = 0;
        $usedFreeze = 0;

        foreach ($dates as $row) {
            $d = $row['activity_date'];
            $isFreeze = (int)($row['is_freeze'] ?? 0);
            $activityDate = new DateTime($d);

            if ($activityDate->format('Y-m-d') === $expected->format('Y-m-d')) {
                if ($isFreeze === 0) {
                    $streak++;
                }
                $expected->modify('-1 day');
                continue;
            }

            if (
                $freezeLeft > 0 &&
                $activityDate->format('Y-m-d') ===
                (clone $expected)->modify('-1 day')->format('Y-m-d')
            ) {
                $freezeLeft--;
                $usedFreeze++;
                $streak++;
                $expected->modify('-2 day');
                continue;
            }

            break;
        }

        return $streak;
    }

    private function reconcileStreak(int $userId): void
    {
        if (!$this->shouldProcessStreak($userId)) {
            return;
        }

        $stmt = $this->conn->prepare("SELECT last_streak_check, streak_freeze FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) return;

        $lastCheck = $row['last_streak_check'] ?? null;
        $freezeLeft = (int)$row['streak_freeze'];

        if (!$lastCheck) {
            $this->updateLastStreakCheck($userId);
            return;
        }

        $checkDate = new DateTime($lastCheck);
        $yesterday = new DateTime('yesterday');

        while ($checkDate < $yesterday) {
            $checkDate->modify('+1 day');
            $dateStr = $checkDate->format('Y-m-d');

            if (!$this->isTodayActive($userId, $dateStr)) {
                if ($freezeLeft > 0) {
                    $freezeLeft--;
                    
                    $upd = $this->conn->prepare("UPDATE users SET streak_freeze = ? WHERE id = ?");
                    $upd->bind_param("ii", $freezeLeft, $userId);
                    $upd->execute();
                    $upd->close();

                    $ins = $this->conn->prepare("INSERT IGNORE INTO user_activity (user_id, activity_date, is_freeze) VALUES (?, ?, 1)");
                    $ins->bind_param("is", $userId, $dateStr);
                    $ins->execute();
                    $ins->close();
                } else {
                    break;
                }
            }
        }

        $this->updateLastStreakCheck($userId);
    }

    private function shouldProcessStreak(int $userId): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT last_streak_check FROM users WHERE id = ?"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $last = $stmt->get_result()->fetch_assoc()['last_streak_check'] ?? null;
        $stmt->close();

        $yesterday = date('Y-m-d', strtotime('yesterday'));
        return !$last || date('Y-m-d', strtotime($last)) !== $yesterday;
    }

    private function updateLastStreakCheck(int $userId): void
    {
        $stmt = $this->conn->prepare(
            "UPDATE users SET last_streak_check = ? WHERE id = ?"
        );
        $yesterday = date('Y-m-d', strtotime('yesterday'));
        $stmt->bind_param("si", $yesterday, $userId);
        $stmt->execute();
        $stmt->close();
    }

    private function fetchNutritionFromFoodDb(string $foodName): array
    {
        try {
            $api = new ApiClientEdamamFood();
            $best = $api->getNutrientsBest($foodName);
            if (($best['confidence'] ?? 0) < 50) {
                return ['cal'=>null,'prot'=>null,'carb'=>null,'fat'=>null];
            }
            return [
                'cal'=>$best['cal'] ?? null,
                'prot'=>$best['prot'] ?? null,
                'carb'=>$best['carb'] ?? null,
                'fat'=>$best['fat'] ?? null,
            ];
        } catch (Throwable) {
            return ['cal'=>null,'prot'=>null,'carb'=>null,'fat'=>null];
        }
    }
}
