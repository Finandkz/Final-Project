<?php
use App\Helpers\Session;
use App\Config\Database;

require_once __DIR__ . '/../../vendor/autoload.php';

Session::start();
$user = Session::get('user');

if (!$user || ($user['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit;
}

$db = new Database();
$conn = $db->connect();

$from = $_GET['from'] ?? date('Y-m-d', strtotime('-3 days'));
$to   = $_GET['to']   ?? date('Y-m-d');

$fd = DateTime::createFromFormat('Y-m-d', $from);
$td = DateTime::createFromFormat('Y-m-d', $to);
if (!$fd || !$td) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Invalid date format']);
    exit;
}

$sql = "
    SELECT activity_date AS tanggal,
           COUNT(DISTINCT user_id) AS user_count
    FROM user_activity
    WHERE activity_date BETWEEN ? AND ?
    GROUP BY activity_date
    ORDER BY activity_date ASC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'DB prepare failed']);
    exit;
}
$stmt->bind_param('ss', $from, $to);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while ($r = $res->fetch_assoc()) {
    $data[] = [
        'tanggal'    => $r['tanggal'],
        'user_count' => (int)$r['user_count'],
    ];
}
$stmt->close();

header('Content-Type: application/json');
echo json_encode(['success'=>true,'data'=>$data]);
