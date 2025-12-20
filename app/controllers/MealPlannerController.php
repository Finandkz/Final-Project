<?php
namespace App\Controllers;

use App\Helpers\Session;
use App\Config\Database;
use App\Helpers\Env;
use DateTime;

class MealPlannerController
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
        $tz = Env::get('APP_TIMEZONE', 'Asia/Jakarta');
        @date_default_timezone_set($tz);

        $db = new Database();
        $this->conn = $db->connect();
    }

    public function index(): array
    {
        $errors = [];
        $success = null;

        if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
            $id = (int) $_GET['id'];

            $stmt = $this->conn->prepare(
                "DELETE FROM meal_plans WHERE id = ? AND user_id = ?"
            );
            $stmt->bind_param("ii", $id, $this->user['id']);

            if ($stmt->execute()) {
                $success = "Meal plan deleted successfully.";
            } else {
                $errors[] = "Failed to delete meal plan.";
            }
            $stmt->close();
        }

        $stmt = $this->conn->prepare(
            "SELECT id, food_name, meal_type, meal_time, notes
             FROM meal_plans
             WHERE user_id = ?
             ORDER BY meal_time ASC"
        );
        $stmt->bind_param("i", $this->user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $plans = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (isset($_GET['saved']) && $_GET['saved'] == 1) {
            $success = "Meal plan successfully saved.";
        }

        return [
            'plans'   => $plans,
            'errors'  => $errors,
            'success' => $success,
        ];
    }

    public function form(): array
    {
        $errors  = [];
        $success = null;

        $id     = isset($_GET['id']) ? (int) $_GET['id'] : null;
        $isEdit = $id !== null;

        $food_name = '';
        $meal_type = '';
        $time_only = '';
        $notes     = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::validateCsrfToken($_POST['_csrf'] ?? '')) {
                $errors[] = "Invalid CSRF token. Please refresh the page.";
                return compact('errors', 'success', 'id', 'isEdit', 'food_name', 'meal_type', 'time_only', 'notes');
            }

            $id     = isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null;
            $isEdit = $id !== null;

            $food_name = trim($_POST['food_name'] ?? '');
            $meal_type = trim($_POST['meal_type'] ?? '');
            $time_only = trim($_POST['meal_time'] ?? '');
            $notes     = trim($_POST['notes'] ?? '');

            if ($food_name === '') $errors[] = 'Name of food must be filled in.';
            if ($time_only === '') $errors[] = 'Meal times must be filled in.';
            if ($meal_type === '') $errors[] = 'The meal time type must be selected.';

            $planned_at_str = null;
            $planned_at_obj = null;

            if (!$errors) {
                $today     = new DateTime('now');
                $date_part = $today->format('Y-m-d');
                $planned_at_obj = DateTime::createFromFormat('Y-m-d H:i', $date_part . ' ' . $time_only);

                if (!$planned_at_obj) {
                    $errors[] = 'Invalid meal time format.';
                } else {
                    $planned_at_str = $planned_at_obj->format('Y-m-d H:i:s');
                }
            }

            if (!$errors && $planned_at_str) {
                $now = new DateTime('now');
                $isNotified = ($planned_at_obj > $now) ? 0 : 1;

                if ($isEdit) {
                    $stmt = $this->conn->prepare(
                        "UPDATE meal_plans
                         SET food_name = ?, meal_type = ?, meal_time = ?, notes = ?, is_notified = ?
                         WHERE id = ? AND user_id = ?"
                    );
                    $stmt->bind_param(
                        "ssssiii",
                        $food_name,
                        $meal_type,
                        $planned_at_str,
                        $notes,
                        $isNotified,
                        $id,
                        $this->user['id']
                    );
                    if ($stmt->execute()) {
                        $success = 'The meal plan has been successfully updated.';
                    } else {
                        $errors[] = 'Failed to update meal plan.';
                    }
                    $stmt->close();
                } else {
                    $stmt = $this->conn->prepare(
                        "INSERT INTO meal_plans (user_id, food_name, meal_type, meal_time, notes, is_notified)
                         VALUES (?, ?, ?, ?, ?, 0)"
                    );
                    $stmt->bind_param(
                        "issss",
                        $this->user['id'],
                        $food_name,
                        $meal_type,
                        $planned_at_str,
                        $notes
                    );
                    if ($stmt->execute()) {
                        header("Location: meal_planner.php?saved=1");
                        exit;
                    } else {
                        $errors[] = 'Failed to save meal plan.';
                    }
                    $stmt->close();
                }
            }
        }
        elseif ($isEdit) {
            $stmt = $this->conn->prepare(
                "SELECT food_name, meal_type, meal_time, notes
                 FROM meal_plans
                 WHERE id = ? AND user_id = ?"
            );
            $stmt->bind_param("ii", $id, $this->user['id']);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($row = $res->fetch_assoc()) {
                $food_name = $row['food_name'];
                $meal_type = $row['meal_type'];
                $notes     = $row['notes'];
                $time_only = (new DateTime($row['meal_time']))->format('H:i');
            } else {
                $errors[] = 'Meal planner data not found.';
                $id = null;
                $isEdit = false;
            }
            $stmt->close();
        }
        return compact(
            'errors',
            'success',
            'id',
            'isEdit',
            'food_name',
            'meal_type',
            'time_only',
            'notes'
        );
    }
}
