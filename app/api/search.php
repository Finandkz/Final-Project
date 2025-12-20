<?php
use App\Config\Database;
use App\Helpers\Env;
use App\Classes\ApiClientEdamam;

require_once __DIR__ . '/../../vendor/autoload.php';

header("Content-Type: application/json; charset=utf-8");
Env::load();
$client = new ApiClientEdamam();
$keyword = $_GET['keyword'] ?? "";
$diet    = $_GET['diet'] ?? "";
$health  = $_GET['health'] ?? "";
if ($keyword === "") {
    echo json_encode(["hits" => []]);
    exit;
}
$params = [
    "q"      => $keyword,
    "diet"   => $diet,
    "health" => $health
];
$response = $client->fetch($params);
if (!isset($response['hits'])) {
    $response['hits'] = [];
}
echo json_encode($response);
