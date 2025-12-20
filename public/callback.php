<?php
use App\Config\Database;
use App\Controllers\GoogleController;
use App\Helpers\Session;

require_once __DIR__ . "/../vendor/autoload.php";

Session::start();
$db = (new Database())->connect();
$google = new GoogleController($db);

if(!isset($_GET['code'])) {
    header("Location: login.php");
    exit;
}

$user = $google->handleCallback($_GET['code']);
if(!$user) {
    echo "Google login failed.";
    exit;
}

Session::set('user', $user);
header("Location: /mealify/public/mahasiswa/mhs_dashboard.php");
exit;
