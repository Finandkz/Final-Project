<?php
use App\Helpers\Session;
require_once __DIR__ . "/../vendor/autoload.php";
Session::start();

if (isset($_GET['do']) && $_GET['do'] === 'logout') {
    Session::destroy();
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Logout</title>
    <link rel="stylesheet" href="assets/css/logout.css">
</head>
<body>

<div class="logout-wrapper">
    <div class="spinner"></div>
    <h4>Currently logout...</h4>
    <p>See you don't forget to eat healthy foods👋😎</p>
</div>

<script>
    setTimeout(() => {
        window.location.href = "logout.php?do=logout";
    }, 2200);
</script>

</body>
</html>
