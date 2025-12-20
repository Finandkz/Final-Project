<?php
use App\Controllers\MealPlannerController;

require_once __DIR__ . '/../../vendor/autoload.php';

$controller = new MealPlannerController();
$data = $controller->index();

extract($data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Meal Planner - Mealify</title>
    <link rel="stylesheet" href="../assets/css/mealplanner.css">
</head>
<body>
<div class="main-wrapper">
    <header class="header">
        <div class="header-left">
            <img src="../assets/img/logo.jpg" class="logo">
            <h1 class="brand">MEALIFY</h1>
        </div>
        <div class="header-center">
            <div class="search-box search-box--disabled">
                <input type="text" disabled value="Meal Planner" />
            </div>
        </div>
            <a href="mhs_dashboard.php" class="back-dashboard">← Back</a>
    </header>
    <div class="mp-page">
        <main class="mp-main">
            <h1 class="mp-title">MEAL<br>PLANNER</h1>
            <?php if (!empty($success)): ?>
                <div class="mp-alert mp-alert--success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="mp-alert mp-alert--error">
                    <?php foreach ($errors as $err): ?>
                        <p><?= htmlspecialchars($err) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="mp-topbar">
                <a href="meal_planner_form.php" class="mp-btn-add">
                    + Make a meal plan
                </a>
            </div>
            <section class="mp-table-wrapper">
                <table class="mp-table">
                    <thead>
                    <tr>
                        <th>Food name</th>
                        <th>Type</th>
                        <th>Meal times</th>
                        <th>Notes</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($plans)): ?>
                        <tr>
                            <td colspan="5" class="mp-empty">
                               There are no meal plans yet. Click "Create a meal plan" to add one.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($plans as $plan): ?>
                            <tr>
                                <td><?= htmlspecialchars($plan['food_name']) ?></td>
                                <td><?= htmlspecialchars($plan['meal_type']) ?></td>
                                <td><?= (new DateTime($plan['meal_time']))->format('H:i') ?></td>
                                <td><?= nl2br(htmlspecialchars($plan['notes'])) ?></td>
                                <td class="mp-actions">
                                    <a href="meal_planner_form.php?id=<?= $plan['id'] ?>"
                                       class="mp-pill mp-pill--green">Edit</a>
                                    <a href="meal_planner.php?action=delete&id=<?= $plan['id'] ?>"
                                       class="mp-pill mp-pill--red mp-delete-btn">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
        <div class="mp-wave"></div>
    </div>
    <footer class="footer">
        © 2025 Mealify — Healthy Living Starts with Healthy Food
    </footer>
</div>
<script src="../assets/js/mealplanner.js"></script>
</body>
</html>
