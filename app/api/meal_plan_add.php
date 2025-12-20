<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../../vendor/autoload.php";

use App\Helpers\Session;
use App\Config\Database;
use DateTime;

Session::start();
$user = Session::get("user");

if (!$user) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Invalid request data"]);
    exit;
}

$food_name = trim($data["food_name"] ?? "");
$meal_type = trim($data["meal_type"] ?? "");
$time_only = trim($data["meal_time"] ?? "");
$notes     = trim($data["notes"] ?? "");

if ($food_name === "" || $meal_type === "" || $time_only === "") {
    echo json_encode(["success" => false, "message" => "Required fields missing"]);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    $today = new DateTime("now");
    $date_part = $today->format("Y-m-d");
    $planned_at_obj = DateTime::createFromFormat("Y-m-d H:i", $date_part . " " . $time_only);

    if (!$planned_at_obj) {
        echo json_encode(["success" => false, "message" => "Invalid time format"]);
        exit;
    }

    $planned_at_str = $planned_at_obj->format("Y-m-d H:i:s");
    
    // Check if it should be notified (if time is in the future)
    $now = new DateTime("now");
    $is_notified = ($planned_at_obj > $now) ? 0 : 1;

    $stmt = $conn->prepare(
        "INSERT INTO meal_plans (user_id, food_name, meal_type, meal_time, notes, is_notified) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("issssi", $user["id"], $food_name, $meal_type, $planned_at_str, $notes, $is_notified);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Meal plan added successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to save meal plan."]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
}
