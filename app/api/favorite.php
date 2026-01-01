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

$uri      = trim($data["uri"] ?? "");
$label    = trim($data["label"] ?? "");
$image    = trim($data["image"] ?? "");
$source   = trim($data["source"] ?? "");
$url      = trim($data["url"] ?? "");
$calories = isset($data["calories"]) ? (int)$data["calories"] : null;

if ($uri === "" || $label === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare query."
    ]);
    exit;
}


$db = (new Database())->connect();

$uri = substr($uri, 0, 255);

$sql = "INSERT INTO favorites (user_id, recipe_uri, label, image, source, url, calories)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            label    = VALUES(label),
            image    = VALUES(image),
            source   = VALUES(source),
            url      = VALUES(url),
            calories = VALUES(calories)";

$stmt = $db->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare query."
    ]);
    exit;
}

$uid        = (int)$user["id"];
$caloriesDb = $calories ?? 0;

$stmt->bind_param(
    "isssssi",
    $uid,
    $uri,
    $label,
    $image,
    $source,
    $url,
    $caloriesDb
);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to save favorite."
    ]);
    $stmt->close();
    exit;
}

$stmt->close();

echo json_encode([
    "success" => true,
    "message" => "Recipe saved to favorites."
]);
