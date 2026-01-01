<?php
use App\Helpers\Session;
use App\Config\Database;
use App\Helpers\Env;

require_once __DIR__ . "/../../vendor/autoload.php";

header("Content-Type: application/json; charset=utf-8");

Session::start();
Env::load();

$user = Session::get("user");
if (!$user) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Please login first.",
        "isFavorite" => false
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Gunakan metode GET.",
        "isFavorite" => false
    ]);
    exit;
}

$uri = isset($_GET["uri"]) ? trim($_GET["uri"]) : "";
if ($uri === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "URI Invalid.",
        "isFavorite" => false
    ]);
    exit;
}


$db = (new Database())->connect();

$uri = substr($uri, 0, 255);

$sql = "SELECT 1 FROM favorites WHERE user_id = ? AND recipe_uri = ? LIMIT 1";
$stmt = $db->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare query.",
        "isFavorite" => false
    ]);
    exit;
}

$uid = (int)$user["id"];
$stmt->bind_param("is", $uid, $uri);
$stmt->execute();
$stmt->store_result();

$isFavorite = $stmt->num_rows > 0;

$stmt->close();

echo json_encode([
    "success" => true,
    "message" => "Favorite status taken.",
    "isFavorite" => $isFavorite
]);
