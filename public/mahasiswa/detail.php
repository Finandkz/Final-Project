<?php
use App\Helpers\Session;
use App\Helpers\Env;

require_once __DIR__ . "/../../vendor/autoload.php";

Session::start();
$user = Session::get("user");
if (!$user) {
    header("Location: ../login.php");
    exit;
}

Env::load();
$appId  = Env::get("EDAMAM_APP_ID");
$appKey = Env::get("EDAMAM_APP_KEY");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Detail Recipe - Mealify</title>
    <link rel="stylesheet" href="../assets/css/detail.css">
</head>
<body class="page-root">

<div class="page-bg"></div>

<div class="page-content">
    <div class="detail-page">
        <div class="detail-wrapper" id="detailWrapper"></div>
    </div>
</div>

<footer class="footer">
    © 2025 Mealify — Healthy Living Starts with Healthy Food
</footer>

<script>
    window.EDAMAM_APP_ID  = <?= json_encode($appId) ?>;
    window.EDAMAM_APP_KEY = <?= json_encode($appKey) ?>;
</script>
<script src="../assets/js/detail.js"></script>
</body>
</html>
