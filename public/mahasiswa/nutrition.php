<?php
use App\Controllers\NutritionController;
use App\Helpers\Session;

require_once __DIR__ . '/../../vendor/autoload.php';

$controller = new NutritionController();
$data = $controller->handle();
extract($data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analyzer Nutrition - Mealify</title>
    <link rel="stylesheet" href="../assets/css/nutrition.css?v=<?= time() ?>">
</head>
<body>
<div class="bg-animation"></div>
<div class="main-wrapper">
    <header class="header">
        <div class="header-left">
            <img src="../assets/img/logo.jpg" class="logo">
            <h1 class="brand">MEALIFY</h1>
        </div>
        <div class="header-center">
            <div class="search-box search-box--disabled">
                <input type="text" disabled value="Analisis Nutrisi" />
            </div>
        </div>
            <a href="mhs_dashboard.php" class="back-dashboard">← Back</a>
    </header>
    <div class="nutri-page <?= $hasResult ? 'nutri-page--has-result' : '' ?>">
        <main class="nutri-main">
            <h1 class="nutri-title">Calculate Nutrition</h1>
            <p class="nutri-subtitle">
            Enter an ingredient list list for what you are cooking, 
            <span class="nutri-example">"1 cup rice, 10 oz chickpeas"</span>, etc.
            Enter each ingredient on a new line.
            </p>
            <?php if (!empty($errors)): ?>
                <div class="nutri-errors">
                    <?php foreach ($errors as $err): ?>
                        <p><?= htmlspecialchars($err) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="post" action="">
                <input type="hidden" name="_csrf" value="<?= Session::generateCsrfToken() ?>">
                <div class="nutri-layout">
                    <section class="nutri-left">
                        <div class="nutri-textarea-wrapper">
                            <textarea
                                name="ingredients"
                                class="nutri-textarea"
                                placeholder="1 cup rice&#10;10 oz chickpeas&#10;1 tbsp olive oil"
                            ><?= htmlspecialchars($ingredientsText ?? '') ?></textarea>
                        </div>
                        <div class="nutri-actions">
                            <button type="submit" class="nutri-btn nutri-btn--primary">
                                Analyze
                            </button>
                            <?php if ($hasResult): ?>
                                <button type="button"
                                        id="btn-new-recipe"
                                        class="nutri-btn nutri-btn--secondary">
                                    New Recipe
                                </button>
                            <?php endif; ?>
                        </div>
                    </section>
                    <?php if ($hasResult): ?>
                        <aside class="nutri-right">
                            <h2 class="nutri-info-title">Nutrition Facts</h2>
                            <div class="nutri-info-item">
                                <span class="label">Total Calories</span>
                                <span class="value"><?= round($summary['calories']) ?> kcal</span>
                            </div>
                            <div class="nutri-info-item">
                                <span class="label">Total weight</span>
                                <span class="value"><?= round($summary['totalWeight'], 1) ?> g</span>
                            </div>
                            <div class="nutri-info-divider"></div>
                            <h3 class="nutri-info-subtitle">Makronutrien</h3>
                            <ul class="nutri-macro-list">
                                <li>
                                    <span>Protein</span>
                                    <span><?= round($summary['protein'], 1) ?> g</span>
                                </li>
                                <li>
                                    <span>Fat</span>
                                    <span><?= round($summary['fat'], 1) ?> g</span>
                                </li>
                                <li>
                                    <span>Carbohydrate</span>
                                    <span><?= round($summary['carbs'], 1) ?> g</span>
                                </li>
                            </ul>
                        </aside>
                    <?php endif; ?>
                </div>
                <?php if ($hasResult && !empty($result['ingredients'])): ?>
                    <section class="nutri-table-wrap" id="nutri-table-wrap">
                        <table class="nutri-table">
                            <thead>
                            <tr>
                                <th>qty</th>
                                <th>Unit</th>
                                <th>Food</th>
                                <th>Calories</th>
                                <th>Weight (g)</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($result['ingredients'] as $ing):
                                $parsed = $ing['parsed'][0] ?? null;
                                if (!$parsed) continue;

                                $calPerIng = '-';
                                if (isset($parsed['nutrients']['ENERC_KCAL']['quantity'])) {
                                    $calPerIng = round($parsed['nutrients']['ENERC_KCAL']['quantity']);
                                }
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($parsed['quantity'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($parsed['measure'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($parsed['food'] ?? '-') ?></td>
                                    <td><?= $calPerIng ?></td>
                                    <td><?= round($parsed['weight'] ?? 0, 1) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </section>
                <?php endif; ?>
            </form>
        </main>
        <div class="nutri-wave"></div>
    </div>
    <footer class="footer">
        © 2025 Mealify — Healthy Living Starts with Healthy Food
    </footer>
</div>
<script src="../assets/js/nutrition.js"></script>
</body>
</html>
