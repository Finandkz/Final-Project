<?php
use App\Helpers\Session;
use App\Config\Database;

require_once __DIR__ . "/../../vendor/autoload.php";

Session::start();
$user = Session::get("user");
if (!$user) {
    session_write_close();
    header("Location: ../login.php");
    exit;
}

$db = (new Database())->connect();

$sql  = "SELECT * FROM favorites WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $user["id"]);
$stmt->execute();
$favorites = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$favCount = count($favorites);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Favorit Recipe - Mealify</title>
    <script src="https://unpkg.com/feather-icons"></script>
    <link rel="stylesheet" href="../assets/css/favorites.css">
</head>
<body>
<div class="main-wrapper">
    <header class="header">
        <div class="header-left">
            <img src="../assets/img/logo.jpg" class="logo">
            <span class="brand">MEALIFY</span>
        </div>

        <div class="header-right">
            <button class="menu-btn" id="openSidebar">☰</button>
        </div>
    </header>
    <div class="sidebar" id="sidebar">
        <button class="close-btn" id="closeSidebar">✕</button>
        <h2>Account</h2>
        <ul>
            <li><a href="mhs_dashboard.php"><i data-feather="home" class="simbol"></i> Dashboard</a></li>
            <li><a href="account.php"><i data-feather="user" class="simbol"></i> Account Information</a></li>
            <li><a href="change_password.php"><i data-feather="edit" class="simbol"></i> Change Password</a></li>
            <li><a href="../logout.php"><i data-feather="log-out" class="simbol"></i>Logout</a></li>
        </ul>
    </div>
    <main class="fav-page">
        <section class="fav-header">
            <div>
                <h1 class="fav-title">My Favorite</h1>
                <p class="fav-count">
                    <span class="fav-count-number"><?= $favCount ?></span>
                    saved recipe
                </p>
            </div>
        </section>
        <section class="fav-content">

            <?php if (empty($favorites)): ?>
                <div class="fav-empty">
                    💚<br>There are no favorite recipes yet.<br>
                        Add one from the recipe details page.
                </div>
            <?php else: ?>
                <div class="fav-grid">
                    <?php foreach ($favorites as $fav): ?>
                        <article class="fav-card" data-uri="<?= htmlspecialchars($fav['recipe_uri']) ?>">
                            <?php if ($fav["image"]): ?>
                                <img src="<?= htmlspecialchars($fav["image"]) ?>"
                                     alt="<?= htmlspecialchars($fav["label"]) ?>"
                                     class="fav-card-img">
                            <?php endif; ?>
                            <div class="fav-card-body">
                                <h3 class="fav-card-title">
                                    <?= htmlspecialchars($fav["label"]) ?>
                                </h3>
                                <p class="fav-card-cal">
                                    🔥 <?= (int)$fav["calories"] ?> calories
                                </p>
                                <p class="fav-card-src">
                                    📘 <?= htmlspecialchars($fav["source"]) ?>
                                </p>
                                <div class="fav-card-actions">
                                    <?php if ($fav["url"]): ?>
                                        <a href="<?= htmlspecialchars($fav["url"]) ?>"
                                           target="_blank"
                                           class="fav-view-link">
                                            View original recipe
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>
<footer class="footer">
    © 2025 Mealify — Healthy Living Starts with Healthy Food
</footer>
<script src="../assets/js/favorites.js"></script>
</body>
</html>
