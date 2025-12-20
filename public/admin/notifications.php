<?php
use App\Helpers\Session;
use App\Config\Database;

require_once __DIR__ . '/../../vendor/autoload.php';

Session::start();
$admin = Session::get('user');

if (!$admin || ($admin['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$db = new Database();
$conn = $db->connect();

$errors = [];
$messages = [];

$editId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$editData = null;

if ($editId) {
    $stmt = $conn->prepare("
        SELECT id, name, title, body, send_time, active
        FROM admin_notifications
        WHERE id = ? LIMIT 1
    ");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $res = $stmt->get_result();
    $editData = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if (!$editData) {
        $messages[] = "Template not found.";
        $editId = null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::validateCsrfToken($_POST['_csrf'] ?? '')) {
        $errors[] = "Invalid CSRF token. Please refresh the page.";
    } elseif (($_POST['action'] ?? '') === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM admin_notifications WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $messages[] = "Template deleted successfully.";
                if ($editId === $id) { $editId = null; $editData = null; }
            } else {
                $errors[] = "Failed to delete template: " . $conn->error;
            }
            $stmt->close();
        } else {
            $errors[] = "Invalid template ID.";
        }
    } elseif (($_POST['action'] ?? '') === 'create') {
        $name  = trim($_POST['name'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $body  = trim($_POST['body'] ?? '');
        $time  = trim($_POST['send_time'] ?? '');
        $active = isset($_POST['active']) ? 1 : 0;

        if ($name === '')  $errors[] = "Name is required.";
        if ($title === '') $errors[] = "Title is required.";
        if ($body === '')  $errors[] = "The message content is mandatory.";
        if ($time === '')  $errors[] = "Hours are required.";

        if (!$errors) {
            $tObj = DateTime::createFromFormat('H:i', $time) ?: DateTime::createFromFormat('H:i:s', $time);
            if (!$tObj) $errors[] = "Invalid clock format.";
            else $time = $tObj->format("H:i:s");
        }

        if (!$errors) {
            $stmt = $conn->prepare("
                INSERT INTO admin_notifications (name, title, body, type, send_time, active, created_at)
                VALUES (?, ?, ?, 'reminder_mealplanner', ?, ?, NOW())
            ");
            $stmt->bind_param("ssssi", $name, $title, $body, $time, $active);
            if ($stmt->execute()) {
                $messages[] = "Template successfully created.";
            } else {
                $errors[] = "Failed to create template: " . $conn->error;
            }
            $stmt->close();
        }
    } elseif (($_POST['action'] ?? '') === 'update') {
        $id    = (int)($_POST['id'] ?? 0);
        $name  = trim($_POST['name'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $body  = trim($_POST['body'] ?? '');
        $time  = trim($_POST['send_time'] ?? '');
        $active = isset($_POST['active']) ? 1 : 0;

        if ($id <= 0) $errors[] = "Invalid ID.";
        if ($name === '') $errors[] = "Name is required.";
        if ($title === '') $errors[] = "Title is required.";
        if ($body === '') $errors[] = "Message content is required.";
        if ($time === '') $errors[] = "Time is required.";

        if (!$errors) {
            $tObj = DateTime::createFromFormat('H:i', $time) ?: DateTime::createFromFormat('H:i:s', $time);
            if (!$tObj) $errors[] = "Invalid time format.";
            else $time = $tObj->format("H:i:s");
        }

        if (!$errors) {
            $stmt = $conn->prepare("
                UPDATE admin_notifications
                SET name = ?, title = ?, body = ?, send_time = ?, active = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("ssssii", $name, $title, $body, $time, $active, $id);

            if ($stmt->execute()) {
                $messages[] = "Template updated successfully.";
                $editData = [
                    'id' => $id,
                    'name' => $name,
                    'title' => $title,
                    'body' => $body,
                    'send_time' => $time,
                    'active' => $active
                ];
            } else {
                $errors[] = "Failed to update: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

$stmt = $conn->prepare("
    SELECT id, name, title, body, send_time, active, created_at
    FROM admin_notifications
    ORDER BY created_at DESC
");
$stmt->execute();
$res = $stmt->get_result();
$templates = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Notifikasi - Admin</title>
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
        <h1>Manage Notifications</h1>
        <p class="small-muted">Set up automatic email reminder templates for users.</p>

        <?php if ($messages): ?>
            <div class="alert alert-success">
                <?php foreach ($messages as $m) echo "<p>".htmlspecialchars($m)."</p>"; ?>
            </div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e) echo "<p>".htmlspecialchars($e)."</p>"; ?>
            </div>
        <?php endif; ?>

        <div class="notifications-layout">
            <div class="notif-left">
                <h2>Notification Templates</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Title</th>
                            <th>Time</th>
                            <th>Active</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($templates)): ?>
                            <tr><td colspan="5" class="center text-muted">There are no templates yet</td></tr>
                        <?php else: foreach ($templates as $t): ?>
                            <tr>
                                <td><?= htmlspecialchars($t['name']) ?></td>
                                <td><?= htmlspecialchars($t['title']) ?></td>
                                <td><?= substr($t['send_time'], 0, 5) ?></td>
                                <td><?= $t['active'] ? 'Yes' : 'No' ?></td>
                                <td>
                                    <a class="btn-green btn-small" href="notifications.php?id=<?= $t['id'] ?>">Edit</a>
                                   <form method="post" class="inline delete-form">
                                        <input type="hidden" name="_csrf" value="<?= Session::generateCsrfToken() ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                        <button type="button"
                                                class="btn-red btn-small btn-delete"
                                                data-name="<?= htmlspecialchars($t['name']) ?>"
                                                data-type="template">
                                            Delete
                                        </button>
                                    </form>

                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="notif-right">
                <h2><?= $editData ? "Edit Template" : "Create New Template" ?></h2>

                <form method="post" class="admin-form">
                    <input type="hidden" name="_csrf" value="<?= Session::generateCsrfToken() ?>">
                    
                    <?php if ($editData): ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                    <?php else: ?>
                        <input type="hidden" name="action" value="create">
                    <?php endif; ?>

                    <label>Template Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($editData['name'] ?? '') ?>" required>

                    <label>Email Title</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($editData['title'] ?? '') ?>" required>

                    <label>Message Content</label>
                    <textarea name="body" rows="8" required><?= htmlspecialchars($editData['body'] ?? '') ?></textarea>

                    <label>Delivery Hours</label>
                    <input type="time" name="send_time"
                           value="<?= isset($editData['send_time']) ? substr($editData['send_time'], 0, 5) : '' ?>" required>

                    <label class="inline">
                        <input type="checkbox" name="active" <?= !isset($editData['active']) || $editData['active'] ? 'checked' : '' ?>>
                        Active
                    </label>

                    <div style="margin-top:14px; display:flex; gap:10px;">
                        <button class="btn-green" type="submit">
                            <?= $editData ? "Update" : "Save" ?>
                        </button>

                        <?php if ($editData): ?>
                            <a href="notifications.php" class="btn-gray">Cancel</a>
                        <?php else: ?>
                            <button type="reset" class="btn-gray">Reset</button>
                        <?php endif; ?>
                    </div>

                </form>
            </div>

        </div>
    </main>
</div>
<div id="deleteModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <h2>Are you sure?</h2>
        <p id="deleteModalText">
            Do you want to delete this data?<br>
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
