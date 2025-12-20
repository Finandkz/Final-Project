<?php
use App\Controllers\AccountController;
use App\Helpers\Session;

require_once __DIR__ . '/../../vendor/autoload.php';

$controller = new AccountController();
$data = $controller->handle();

$userRow = $data['userRow'] ?? null;
$errors  = $data['errors'] ?? [];
$success = $data['success'] ?? null;

$name        = $userRow['name']              ?? '';
$email       = $userRow['email']             ?? '';
$weight_kg   = $userRow['weight_kg']         ?? '';
$goal_diet   = (int)($userRow['goal_diet']   ?? 0);
$goal_bulk   = (int)($userRow['goal_bulking'] ?? 0);
$avatarFile  = $userRow['avatar']            ?? null; 
$googleAvatar = $userRow['google_avatar_url'] ?? null; 

$avatarUrl = '../assets/img/default-avatar.png';

if ($avatarFile) {
    $avatarUrl = '../uploads/avatars/' . htmlspecialchars($avatarFile);
} elseif ($googleAvatar) {
    $avatarUrl = htmlspecialchars($googleAvatar);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Information - Mealify</title>
    <script src="https://unpkg.com/feather-icons"></script>
    <link rel="stylesheet" href="../assets/css/account.css">
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
            <li><a href="change_password.php"><i data-feather="edit" class="simbol"></i> Change Password</a></li>
            <li><a href="favorites.php"><i data-feather="star" class="simbol"></i> Favorite</a></li>
            <li><a href="../logout.php"><i data-feather="log-out" class="simbol"></i>Logout</a></li>
        </ul>
    </div>
    
    <div class="account-page">
        <main class="account-card">

            <?php if (!empty($success)): ?>
                <div class="acc-alert acc-alert--success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="acc-alert acc-alert--error">
                    <?php foreach ($errors as $err): ?>
                        <p><?= htmlspecialchars($err) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <section class="account-avatar-section">
                <form id="avatarForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf" value="<?= Session::generateCsrfToken() ?>">
                    <input type="hidden" name="action" value="upload-avatar">
                    <div class="account-avatar-wrapper">
                        <img src="<?= $avatarUrl ?>" alt="Avatar" class="account-avatar-img" onerror="this.onerror=null; this.src='../assets/img/default-avatar.png';">
                        <button type="button" id="avatar-upload-trigger" >
                            <span class="account-avatar-edit-icon">✎</span>
                        </button>
                        
                        <input type="file" name="avatar" id="avatarInput" accept="image/*" hidden>
                    </div>
                </form>
            </section>

            <hr class="account-divider">

            <form method="post" action="" class="account-form">
                <input type="hidden" name="_csrf" value="<?= Session::generateCsrfToken() ?>">
                <input type="hidden" name="action" value="update-profile">

                <div class="account-field-row">
                    <div class="account-field-label">Name</div>
                    <div class="account-field-value">
                        <input type="text" name="name" value="<?= htmlspecialchars($name) ?>">
                        <span class="field-edit-icon">✎</span>
                    </div>
                </div>

                <div class="account-field-row">
                    <div class="account-field-label">Weight</div>
                    <div class="account-field-value">
                        <input type="number" name="weight_kg"
                               value="<?= htmlspecialchars($weight_kg) ?>"
                               placeholder="example: 70">
                        <span class="account-field-suffix">kg</span>
                        <span class="field-edit-icon">✎</span>
                    </div>
                </div>

                <div class="account-field-row">
                    <div class="account-field-label">Goal</div>
                    <div class="account-field-value account-goal-selection-cards">
                        
                        <label class="goal-card-option">
                            <input type="radio" name="goal_selection" value="diet" <?= $goal_diet ? 'checked' : '' ?>>
                            <div class="goal-card-content">
                                <span class="goal-card-icon">🥗</span>
                                <span class="goal-card-title">Diet</span>
                                <span class="goal-card-desc">Lose weight & stay fit</span>
                            </div>
                        </label>

                        <label class="goal-card-option">
                            <input type="radio" name="goal_selection" value="bulking" <?= $goal_bulk ? 'checked' : '' ?>>
                            <div class="goal-card-content">
                                <span class="goal-card-icon">💪</span>
                                <span class="goal-card-title">Bulking</span>
                                <span class="goal-card-desc">Build muscle & strength</span>
                            </div>
                        </label>

                    </div>
                </div>

                <div class="account-field-row">
                    <div class="account-field-label">Email</div>
                    <div class="account-field-value">
                        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">
                        <span class="field-edit-icon">✎</span>
                    </div>
                </div>

                <div class="account-save-row">
                    <button type="submit" class="account-save-btn">Save Changes</button>
                </div>
            </form>

            <hr class="account-divider">

            <section class="account-delete-section">
                <form id="delete-account-form" method="post" action="">
                    <input type="hidden" name="_csrf" value="<?= Session::generateCsrfToken() ?>">
                    <input type="hidden" name="action" value="delete-account">
                    <button type="button" id="btn-delete-account" class="account-delete-btn">
                        <span class="delete-icon">🚫</span>
                        Delete Account
                    </button>
                </form>
            </section>

        </main>

        <div class="account-wave"></div>
    </div>

    <div id="account-delete-modal" class="account-modal-backdrop">
        <div class="account-modal">
            <h2>Delete Account?</h2>
            <p>This action is permanent and cannot be undone. All your data including meal plans and stats will be lost.</p>
            <div class="account-modal-actions">
                <button type="button" id="delete-cancel" class="account-modal-btn account-modal-btn--secondary">Cancel</button>
                <button type="button" id="delete-confirm" class="account-modal-btn account-modal-btn--danger">Delete Anyway</button>
            </div>
        </div>
    </div>

    <footer class="footer">
        © 2025 Mealify — Healthy Living Starts with Healthy Food
    </footer>
</div>

<script src="../assets/js/account.js"></script>
</body>
</html>
