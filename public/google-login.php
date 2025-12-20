<?php
use App\Config\Database;
use App\Controllers\GoogleController;

require_once __DIR__ . "/../vendor/autoload.php";

$db = (new Database())->connect();
$g = new GoogleController($db);
header("Location: " . $g->getAuthUrl());
exit;
