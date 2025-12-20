<?php
use App\Helpers\Session;
use App\Config\Database;

require_once __DIR__ . '/../../vendor/autoload.php';

Session::start();
$user = Session::get('user');

if (!$user || ($user['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$db = new Database();
$conn = $db->connect();

$from   = $_GET['from'] ?? date('Y-m-d', strtotime('-3 days'));
$to     = $_GET['to']   ?? date('Y-m-d');
$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'user_id is required']);
    exit;
}

$fd = DateTime::createFromFormat('Y-m-d', $from);
$td = DateTime::createFromFormat('Y-m-d', $to);
if (!$fd || !$td) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Invalid date format']);
    exit;
}

$sql = "
    SELECT 
        log_date AS tanggal,
        SUM(COALESCE(calories,0)) AS calories,
        SUM(COALESCE(protein,0)) AS protein,
        SUM(COALESCE(carbs,0)) AS carbs,
        SUM(COALESCE(fat,0)) AS fat
    FROM meal_logs
    WHERE user_id = ?
      AND log_date BETWEEN ? AND ?
    GROUP BY log_date
    ORDER BY log_date ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('iss', $userId, $from, $to);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while ($r = $res->fetch_assoc()) {
    $data[] = [
        'tanggal'  => $r['tanggal'],
        'calories' => (float)$r['calories'],
        'protein'  => (float)$r['protein'],
        'carbs'    => (float)$r['carbs'],
        'fat'      => (float)$r['fat'],
    ];
}
$stmt->close();

header('Content-Type: application/json');
echo json_encode(['success'=>true,'data'=>$data]);
