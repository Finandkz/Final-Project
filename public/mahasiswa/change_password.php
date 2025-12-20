<?php
use App\Controllers\ChangePasswordController;
use App\Helpers\Session;

require_once __DIR__ . '/../../vendor/autoload.php';

$controller = new ChangePasswordController();
$data       = $controller->handle();

$errors  = $data['errors']  ?? [];
$success = $data['success'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password - Mealify</title>
    <script src="https://unpkg.com/feather-icons"></script>
    <link rel="stylesheet" href="../assets/css/change_password.css">
</head>
<body>
<div class="main-wrapper">
    <header class="header">
        <div class="header-left">
            <img src="../assets/img/logo.jpg" class="logo">
            <h1 class="brand">MEALIFY</h1>
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
            <li><a href="favorites.php"><i data-feather="star" class="simbol"></i> Favorite</a></li>
            <li><a href="../logout.php"><i data-feather="log-out" class="simbol"></i>Logout</a></li>
        </ul>
    </div>
    
    <div class="pw-page">
        <main class="pw-card">

            <h1 class="pw-title">Change Password</h1>
            <p class="pw-subtitle">
                Please enter your current password and the new password you wish to use.
            </p>
            <?php if (!empty($success)): ?>
                <div class="pw-alert pw-alert--success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="pw-alert pw-alert--error">
                    <?php foreach ($errors as $err): ?>
                        <p><?= htmlspecialchars($err) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="" class="pw-form">
                <input type="hidden" name="_csrf" value="<?= Session::generateCsrfToken() ?>">
                <div class="pw-field">
                    <label for="current_password">Current Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="current_password" id="current_password" required>
                        <span class="toggle-password" data-target="current_password">👁</span>
                    </div>
                </div>

                <div class="pw-field">
                    <label for="new_password">New Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="new_password" id="new_password" required>
                        <span class="toggle-password" data-target="new_password">👁</span>
                    </div>
                </div>

                <div class="pw-field">
                    <label for="new_password_confirmation">Confirm New Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" required>
                        <span class="toggle-password" data-target="new_password_confirmation">👁</span>
                    </div>
                </div>

                <div class="pw-actions">
                    <button type="submit" class="pw-btn-submit">    
                    Update password
                    </button>
                </div>

                <div class="pw-footer-links">
                    <a href="../forgot-password.php" class="pw-forgot-link">
                        Forgot Password?
                    </a>
                </div>
            </form>
        </main>

        <div class="pw-wave"></div>
    </div>

    <footer class="footer">
        © 2025 Mealify — Healthy Living Starts with Healthy Food
    </footer>
</div>
<script src="../assets/js/auth.js"></script>
</body>
</html>
