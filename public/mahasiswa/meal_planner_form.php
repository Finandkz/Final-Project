<?php
use App\Controllers\MealPlannerController;
use App\Helpers\Session;

require_once __DIR__ . '/../../vendor/autoload.php';

$controller = new MealPlannerController();
$data = $controller->form();

extract($data);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Create Meal Planner - Mealify</title>
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
            <a href="meal_planner.php" class="back-dashboard">← Back</a>
    </header>
    <div class="mp-page">
        <main class="mp-main mp-main--form">
            <h1 class="mp-title-center">
                <?= $isEdit ? 'EDIT MEAL PLANNER' : 'MAKE MEAL PLANNER' ?>
            </h1>
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
            <form method="post" action="" class="mp-form">
                <input type="hidden" name="_csrf" value="<?= Session::generateCsrfToken() ?>">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= (int)$id ?>">
                <?php endif; ?>
                <div class="mp-field">
                    <label>Food Name</label>
                    <input type="text" name="food_name"
                           value="<?= htmlspecialchars($food_name) ?>" required>
                </div>
                <div class="mp-field">
                    <label>Meal Times</label>
                    <input type="time" name="meal_time"
                           value="<?= htmlspecialchars($time_only) ?>" required>
                </div>
                <div class="mp-field">
                    <label>Types of Meal Times</label>
                    <select name="meal_type" required>
                        <option value="">-- Choose --</option>
                        <option value="sarapan" <?= $meal_type === 'sarapan' ? 'selected' : '' ?>>Breakfast</option>
                        <option value="makan siang" <?= $meal_type === 'makan siang' ? 'selected' : '' ?>>Lunch</option>
                        <option value="makan malam" <?= $meal_type === 'makan malam' ? 'selected' : '' ?>>Dinner</option>
                        <option value="snack" <?= $meal_type === 'snack' ? 'selected' : '' ?>>Snack</option>
                    </select>
                </div>
                <div class="mp-field">
                    <label>Notes</label>
                    <textarea name="notes" rows="4"><?= htmlspecialchars($notes) ?></textarea>
                </div>
                <div class="mp-actions-center">
                    <button type="submit" class="mp-btn-save">
                        Save
                    </button>
                </div>
            </form>
        </main>
        <div class="mp-wave"></div>
    </div>
    <footer class="footer">
        © 2025 Mealify — Healthy Living Starts with Healthy Food
    </footer>
</div>
</body>
</html>
