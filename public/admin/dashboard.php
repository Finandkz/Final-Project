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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Mealify</title>
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <header class="admin-header">
            <h1 class="page-title">Dashboard Admin</h1>
            <p class="subtitle">Monitor nutritional intake & streak user activity.</p>
        </header>

        <section class="controls-row">
            <label>User:
                <select id="user_id">
                    <option value="">-- Select User --</option>
                    <?php
                    $res = $conn->query("SELECT id, name FROM users ORDER BY name");
                    while ($u = $res->fetch_assoc()) {
                        echo "<option value='{$u['id']}'>{$u['name']}</option>";
                    }
                    ?>
                </select>
            </label>

            <label>From: <input type="date" id="from" value="<?= date('Y-m-d') ?>"></label>
            <label>To: <input type="date" id="to" value="<?= date('Y-m-d', strtotime('+2 days')) ?>"></label>
            <button id="loadBtn" class="btn-primary">Show</button>
        </section>

        <div class="charts-grid">
            <section class="chart-box">
                <header class="chart-header">
                    <h2>Daily Nutrition</h2>
                </header>
                <div class="canvas-wrapper">
                    <canvas id="nutriBarChart"></canvas>
                </div>
            </section>

            <section class="chart-box">
                <header class="chart-header">
                    <h2>Activity Streak</h2>
                </header>
                <div class="canvas-wrapper">
                    <canvas id="activityLineChart"></canvas>
                </div>
            </section>
        </div>
    </main>
</div>

<div id="warningModal" class="modal-backdrop">
    <div class="modal-box">
        <div class="modal-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <h3 class="modal-title" id="modalTitle">Attention</h3>
        <p class="modal-message" id="modalMessage">Please select a user first.</p>
        <button class="modal-btn" onclick="closeModal()">OK, Understood</button>
    </div>
</div>

<script src="../assets/js/chart_admin.js"></script>
</body>
</html>
