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
    <title>Nutrition Analytics - Mealify</title>
    <link rel="stylesheet" href="../assets/css/analytics.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="analytics-page">
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
        <header class="page-header">
            <h1 class="page-title">Nutrition Analytics</h1>
            <p class="subtitle">Deep dive into user nutritional intake and export data.</p>
        </header>

        <section class="filter-card">
            <div class="filter-group">
                <label for="user_id">Select User</label>
                <select id="user_id">
                    <option value="">-- Select User --</option>
                    <?php
                    $res = $conn->query("SELECT id, name FROM users ORDER BY name");
                    while ($u = $res->fetch_assoc()) {
                        echo "<option value='{$u['id']}'>{$u['name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="from">Date From</label>
                <input type="date" id="from" value="<?= date('Y-m-d', strtotime('-5 days')) ?>">
            </div>

            <div class="filter-group">
                <label for="to">Date To</label>
                <input type="date" id="to" value="<?= date('Y-m-d') ?>">
            </div>

            <button id="loadBtn" class="btn-primary-modern">View Data</button>
            <button id="exportCsvBtn" class="export-btn" style="display: none;">
                <svg fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
                Export CSV
            </button>
        </section>

        <section class="chart-container-card">
            <h2>📊 Daily Nutrition Breakdown</h2>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="nutriBarChart"></canvas>
            </div>
        </section>
        
        <section id="tableBox" class="chart-container-card" style="display: none;">
            <h2>📋 Detailed Data Preview</h2>
            <div class="data-table-card">
                <table class="admin-table" id="dataTable">
                    <thead>
                        <tr>
                            <th>Log Date</th>
                            <th>Calories (kcal)</th>
                            <th>Protein (g)</th>
                            <th>Carbohydrates (g)</th>
                            <th>Fat (g)</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                </table>
            </div>
        </section>
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

<script src="../assets/js/analytics.js"></script>
</body>
</html>
