<?php
namespace App\Controllers;
use App\Helpers\Session;
use App\Classes\ApiClientEdamamNutrition;
use App\Config\Database;
use App\Helpers\Env;
use Throwable;

class NutritionController{
    public function handle(): array{
        Session::start();
        $user = Session::get("user");
        if (!$user) {
            header("Location: ../public/login.php");
            exit;
        }
        $errors = [];
        $result = null;
        $ingredientsText = '';
        $success = null;

        Env::load();
        $db = new Database();
        $conn = $db->connect();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::validateCsrfToken($_POST['_csrf'] ?? '')) {
                $errors[] = "Invalid CSRF token. Please refresh the page.";
                return [
                    'errors'          => $errors,
                    'result'          => null,
                    'ingredientsText' => trim($_POST['ingredients'] ?? $_POST['ingredients_text'] ?? ''),
                    'summary'         => ['calories'=>0,'totalWeight'=>0,'protein'=>0,'fat'=>0,'carbs'=>0],
                    'hasResult'       => false,
                    'success'         => null,
                ];
            }

            if (isset($_POST['action']) && $_POST['action'] === 'save_meal') {
                $meal_name = trim($_POST['meal_name'] ?? '');
                $meal_type = trim($_POST['meal_type'] ?? ''); 
                $log_date  = trim($_POST['log_date'] ?? date('Y-m-d'));
                $calories = isset($_POST['calories']) ? (float)$_POST['calories'] : null;
                $protein  = isset($_POST['protein'])  ? (float)$_POST['protein']  : null;
                $fat      = isset($_POST['fat'])      ? (float)$_POST['fat']      : null;
                $carbs    = isset($_POST['carbs'])    ? (float)$_POST['carbs']    : null;

                if ($meal_name === '') $errors[] = 'Food name is empty, cannot be saved.';
                if ($meal_type === '') $errors[] = 'Select the type of meal time to save.';

                $canonical = $this->normalizeMealType($meal_type);
                if (!$canonical) {
                    $errors[] = 'Invalid meal time type.';
                }

                if (empty($errors)) {
                    $stmt = $conn->prepare(
                        "INSERT INTO meal_logs (user_id, meal_type, meal_name, log_date, logged_at, calories, protein, carbs, fat)
                         VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?)"
                    );
                    $calParam = $calories !== null ? $calories : 0.0;
                    $protParam = $protein !== null ? $protein : 0.0;
                    $carbParam = $carbs !== null ? $carbParam : 0.0;
                    $fatParam  = $fat !== null ? $fat : 0.0;

                    $stmt->bind_param(
                        "isssdddd",
                        $user['id'],
                        $canonical,
                        $meal_name,
                        $log_date,
                        $calParam,
                        $protParam,
                        $carbParam,
                        $fatParam
                    );
                    $ok = $stmt->execute();
                    $stmt->close();

                    if ($ok) {
                        $stmt2 = $conn->prepare(
                            "SELECT COUNT(DISTINCT meal_type) AS cnt
                             FROM meal_logs
                             WHERE user_id = ? AND log_date = ? AND meal_type IN ('breakfast','lunch','dinner')"
                        );
                        $stmt2->bind_param("is", $user['id'], $log_date);
                        $stmt2->execute();
                        $res2 = $stmt2->get_result();
                        $row2 = $res2->fetch_assoc();
                        $stmt2->close();

                        if ((int)($row2['cnt'] ?? 0) === 3) {
                            $stmt3 = $conn->prepare(
                                "INSERT IGNORE INTO user_activity (user_id, activity_date) VALUES (?, ?)"
                            );
                            $stmt3->bind_param("is", $user['id'], $log_date);
                            $stmt3->execute();
                            $stmt3->close();
                        }

                        $success = 'The nutritional results are successfully stored as meal.';
                    } else {
                        $errors[] = 'Failed to save meal to database.';
                    }
                }
                $result = [
                    'ingredients' => [],
                ];
                $ingredientsText = trim($_POST['ingredients_text'] ?? '');
            } else {
                $ingredientsText = trim($_POST['ingredients'] ?? '');

                if ($ingredientsText === '') {
                    $errors[] = 'The list of ingredients is mandatory.';
                } else {
                    try {
                        $client = new ApiClientEdamamNutrition();
                        $result = $client->analyzeFromText($ingredientsText);
                    } catch (Throwable $e) {
                        $errors[] = 'There is an error: ' . $e->getMessage();
                    }
                }
            }
        }

        $hasResult = !empty($result);
        $summary = [
            'calories'    => 0,
            'totalWeight' => 0,
            'protein'     => 0,
            'fat'         => 0,
            'carbs'       => 0,
        ];

        if ($hasResult && !empty($result['ingredients']) && is_array($result['ingredients'])) {
            foreach ($result['ingredients'] as $ing) {
                $parsed = $ing['parsed'][0] ?? null;
                if (!$parsed) {
                    continue;
                }

                if (isset($parsed['weight'])) {
                    $summary['totalWeight'] += (float) $parsed['weight'];
                }

                if (isset($parsed['nutrients']['ENERC_KCAL']['quantity'])) {
                    $summary['calories'] += (float) $parsed['nutrients']['ENERC_KCAL']['quantity'];
                }

                if (isset($parsed['nutrients']['PROCNT']['quantity'])) {
                    $summary['protein'] += (float) $parsed['nutrients']['PROCNT']['quantity'];
                }
                if (isset($parsed['nutrients']['FAT']['quantity'])) {
                    $summary['fat'] += (float) $parsed['nutrients']['FAT']['quantity'];
                }
                if (isset($parsed['nutrients']['CHOCDF']['quantity'])) {
                    $summary['carbs'] += (float) $parsed['nutrients']['CHOCDF']['quantity'];
                }
            }
        }

        return [
            'errors'          => $errors,
            'result'          => $result,
            'ingredientsText' => $ingredientsText,
            'summary'         => $summary,
            'hasResult'       => $hasResult,
            'success'         => $success,
        ];
    }

    private function normalizeMealType(string $input): ?string{
        $input = strtolower($input);
        switch ($input) {
            case 'breakfast':
            case 'sarapan':
                return 'breakfast';
            case 'lunch':
            case 'makan_siang':
            case 'makan siang':
                return 'lunch';
            case 'dinner':
            case 'makan_malam':
            case 'makan malam':
                return 'dinner';
            default:
                return null;
        }
    }
}
