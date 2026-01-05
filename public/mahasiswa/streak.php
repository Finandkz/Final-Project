<?php
use App\Controllers\StreakController;
use App\Helpers\Session;

require_once __DIR__ . '/../../vendor/autoload.php';

$success = $success ?? (isset($_GET['success']) ? 'Food successfully saved.' : null);
$controller = new StreakController();
$data = $controller->handle();

$errors        = $data['errors'] ?? [];
$success       = $data['success'] ?? null;
$todayLogs     = $data['todayLogs'] ?? [];
$streak        = (int)($data['streak'] ?? 0);
$streakActive  = $data['streakActive'] ?? false;
$freezeLeft    = (int)($data['freezeLeft'] ?? 0);
$year          = (int)($data['year'] ?? date('Y'));
$activeDates   = $data['activeDates'] ?? [];
$currentYear = (int)date('Y');
$editMode = $_GET['edit'] ?? null;

function formatLogTime(?string $dt): string {
    if (!$dt) return '';
    return (new DateTime($dt))->format('H:i');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Streak - Mealify</title>
    <link rel="stylesheet" href="../assets/css/streak.css">
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
            <input type="text" disabled value="Streak Harian">
        </div>
    </div>
        <a href="mhs_dashboard.php" class="back-dashboard">← Back</a>
</header>
    <div class="streak-page">
    <main class="streak-main">
        <?php if ($success): ?>
            <div class="st-alert st-alert--success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="st-alert st-alert--error">
        <?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>



<section class="streak-summary">
        <h1 class="streak-title">YOUR STREAK</h1>
        <p class="streak-count <?= $streakActive ? 'streak-on' : 'streak-off' ?>">
            <?= $streak ?> Day🔥
        </p>

        <p style="margin-top:6px;font-size:14px;color:#555;">
            ❄️ Freeze remaining: <strong><?= $freezeLeft ?></strong>
        </p>

        <p class="streak-desc">
            Streak will increase by 1 day if you complete 
            <strong>the breakfast, lunch and dinner menus </strong>
            on the same day.
        </p>
</section>
<section class="streak-meals">
    <?php
    function mealCard($type, $label, $time, $log, $editMode) {
        $isEdit = ($editMode === $type);
        ?>
        <div class="streak-meal-card">
            <h2><?= $label ?></h2>
            <p class="streak-meal-info">Time: <?= $time ?> WIB</p>
            <?php if ($log && !$isEdit): ?>
                <div class="streak-meal-filled">
                    <p>Food Name: <strong><?= htmlspecialchars($log['meal_name']) ?></strong></p>
                    <p>Time: <?= formatLogTime($log['logged_at']) ?> WIB</p>
                    <a href="?edit=<?= $type ?>" class="edit-meal-btn">✏️ Change</a>
                </div>
            <?php else: ?>
                <form method="post" class="streak-meal-form">
                    <input type="hidden" name="_csrf" value="<?= Session::generateCsrfToken() ?>">
                    <input type="hidden" name="meal_type" value="<?= $type ?>">
                    <input
                        type="text"
                        name="food_name"
                        placeholder="Example: Rice + Chicken"
                        value="<?= htmlspecialchars($log['meal_name'] ?? '') ?>"
                        required>
                    <button type="submit">
                        <?= $log ? 'Update' : 'Save' ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    <?php } ?>

    <?php
    mealCard('breakfast', 'Breakfast', '05:00 – 11:00', $todayLogs['breakfast'] ?? null, $editMode);
    mealCard('lunch', 'Lunch', '11:00 – 17:00', $todayLogs['lunch'] ?? null, $editMode);
    mealCard('dinner', 'Dinner', '17:00 – 23:00', $todayLogs['dinner'] ?? null, $editMode);
    ?>
</section>
<section class="streak-calendar-section">
    <div class="streak-calendar-header">
        <h2 class="streak-calendar-title">Activity Calendar</h2>

        <div class="streak-year-nav">
            <a href="streak.php?year=<?= $year - 1 ?>" class="streak-year-btn">
                ← <?= $year - 1 ?>
            </a>

            <span class="streak-year-label"><?= $year ?></span>

            <?php if ($year < $currentYear): ?>
                <a href="streak.php?year=<?= $year + 1 ?>" class="streak-year-btn">
                    <?= $year + 1 ?> →
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div
        id="streak-calendar"
        data-year="<?= $year ?>"
        data-active='<?= json_encode($activeDates) ?>'
    ></div>

    <div class="streak-legend">
        <span class="legend-box legend-active"></span> Active
        <span class="legend-box legend-empty"></span> Not active
    </div>
</section>
</main>
</div>
<footer class="footer">
    © 2025 Mealify — Healthy Living Starts with Healthy Food
</footer>

<script src="../assets/js/streak.js"></script>
</body>
</html>
