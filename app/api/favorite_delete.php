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
        "message" => "Please login first."
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Gunakan metode POST."
    ]);
    exit;
}

$raw  = file_get_contents("php://input");
$data = json_decode($raw, true);

$uri = trim($data["uri"] ?? "");
if ($uri === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "URI Invalid."
    ]);
    exit;
}


$db = (new Database())->connect();
$uri = substr($uri, 0, 255);

$sql = "DELETE FROM favorites WHERE user_id = ? AND recipe_uri = ?";
$stmt = $db->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare query."
    ]);
    exit;
}

$uid = (int)$user["id"];
$stmt->bind_param("is", $uid, $uri);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to delete favorite."
    ]);
    $stmt->close();
    exit;
}

$stmt->close();

echo json_encode([
    "success" => true,
    "message" => "Favorites deleted."
]);
