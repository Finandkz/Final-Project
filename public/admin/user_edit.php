<?php
use App\Controllers\AdminUsersController;
use App\Helpers\Session;

require_once __DIR__ . '/../../vendor/autoload.php';

$ctrl = new AdminUsersController();
$errors = [];

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: users.php');
    exit;
}

$user = $ctrl->get($id);
if (!$user || ($user['role'] ?? '') !== 'admin') {
    header('Location: users.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::validateCsrfToken($_POST['_csrf'] ?? '')) {
        $errors[] = "Invalid CSRF token. Please refresh the page.";
    } else {
        $res = $ctrl->update($id, $_POST);
        if ($res['ok']) {
            header('Location: users.php');
            exit;
        }
        $errors = $res['errors'];
        $user['name']  = $_POST['name']  ?? $user['name'];
        $user['email'] = $_POST['email'] ?? $user['email'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Edit</title>
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
</head>
<body>
<div class="admin-wrapper">
    <aside class="admin-sidebar">
        <h2>DASHBOARD ADMIN</h2>
        <div class="menu-item"><a href="dashboard.php">📊 Dashboard</a></div>
        <div class="menu-item"><a href="analytics.php">📈 Analytics</a></div>
        <div class="menu-item"><a href="users.php">👥 Manage Users</a></div>
        <div class="menu-item"><a href="notifications.php">🔔 Notifications</a></div>
        <div class="menu-item"><a href="../logout.php">🚪 Logout</a></div>
    </aside>

    <main class="admin-content">
        <h1>Edit Admin Account</h1>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e): ?>
                    <p><?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" class="admin-form">
            <input type="hidden" name="_csrf" value="<?= Session::generateCsrfToken() ?>">
            <label>Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <label>Role</label>
            <input type="text" value="Admin" disabled>

            <label>Password (Leave it blank if you don't want to change it.)</label>
            <input type="password" name="password" autocomplete="new-password">

            <div style="margin-top:12px;">
                <button class="btn-green" type="submit">Save</button>
                <a class="btn-gray" href="users.php">Back</a>
            </div>
        </form>
    </main>
</div>
</body>
</html>
