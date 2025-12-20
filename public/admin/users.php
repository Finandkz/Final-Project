<?php
use App\Controllers\AdminUsersController;
use App\Helpers\Session;

require_once __DIR__ . '/../../vendor/autoload.php';

$ctrl = new AdminUsersController();
$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::validateCsrfToken($_POST['_csrf'] ?? '')) {
        $errors[] = "Invalid CSRF token. Please refresh the page.";
    } elseif (($_POST['action'] ?? '') === 'delete') {
        $id = (int)$_POST['id'];
        if ($ctrl->delete($id)) {
            $messages[] = "User has been successfully deleted.";
        } else {
            $errors[] = "Failed to delete user.";
        }
    }
}

$users = $ctrl->list();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Manage Users</title>
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
        <h1>Manage Users</h1>

        <?php if ($messages): ?>
            <div class="alert alert-success">
                <?php foreach ($messages as $m): ?>
                    <p><?= htmlspecialchars($m) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e): ?>
                    <p><?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= (int)$u['id'] ?></td>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($u['role'])) ?></td>
                    <td><?= htmlspecialchars($u['created_at']) ?></td>
                    <td>
                        <?php if ($u['role'] === 'admin'): ?>
                            <a class="btn-green" href="user_edit.php?id=<?= (int)$u['id'] ?>">
                                Edit
                            </a>
                        <?php endif; ?>

                        <?php if ((int)$u['id'] !== (int)($_SESSION['user']['id'] ?? 0)): ?>
                            <form method="post" class="inline delete-form">
                                <input type="hidden" name="_csrf" value="<?= Session::generateCsrfToken() ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                <button type="button"
                                        class="btn-red btn-delete"
                                        data-name="<?= htmlspecialchars($u['name']) ?>">
                                    Delete
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="badge-me">Your Account</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</div>

<div id="deleteModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <h2>Are you sure?</h2>
        <p>
            Do you want to delete the user?
            <strong id="deleteUserName"></strong>?<br>
            This action cannot be undone.
        </p>

        <div class="modal-actions">
            <button id="confirmDelete" class="btn-red">Delete</button>
            <button id="cancelDelete" class="btn-gray">Cancel</button>
        </div>
    </div>
</div>

<script src="../assets/js/admin_ui.js"></script>

</body>
</html>
