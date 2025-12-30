<?php
namespace App\Controllers;
use App\Helpers\Session;
use App\Classes\ApiClientEdamamNutrition;
use App\Config\Database;
use App\Helpers\Env;
use Throwable;

class NutritionController {
    public function handle(): array {
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
}
