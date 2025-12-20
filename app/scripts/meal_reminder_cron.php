<?php
use App\Helpers\Env;
use App\Config\Database;
use App\Helpers\Mailer;

require_once __DIR__ . '/../../vendor/autoload.php';

Env::load();

date_default_timezone_set(Env::get('APP_TIMEZONE', 'Asia/Jakarta'));

$db   = new Database();
$conn = $db->connect();

$logFile = __DIR__ . '/meal_cron_log.txt';

function cron_log(string $message): void
{
    global $logFile;
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    file_put_contents($logFile, $line, FILE_APPEND);
}

cron_log('=== Cron start ===');

$now = new DateTime('now');
$nowStr = $now->format('Y-m-d H:i:s');
cron_log("Current PHP time: {$nowStr}");

$sql = "
    SELECT mp.*, u.email
    FROM meal_plans mp
    JOIN users u ON u.id = mp.user_id
    WHERE mp.is_notified = 0
      AND mp.meal_time <= ?
    ORDER BY mp.meal_time ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $nowStr);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    cron_log('Query error: ' . $conn->error);
    echo "Query error: " . $conn->error . PHP_EOL;
    exit(1);
}

$processed = 0;

while ($row = $result->fetch_assoc()) {
    $processed++;

    $planId   = (int)$row['id'];
    $userId   = (int)$row['user_id'];
    $email    = $row['email'] ?? '';
    $foodName = $row['food_name'];
    $mealType = $row['meal_type'];
    $notes    = $row['notes'];

    if (empty($email)) {
        cron_log("Skip plan #{$planId} (user_id={$userId}) - email kosong");
        continue;
    }

    $mealTime = new DateTime($row['meal_time']);

    $subject = '[Mealify] Pengingat ' . $mealType . ' - ' . $foodName;
    
    $bodyText  = "Haloooo,\n\n";
    $bodyText .= "Ini pengingat rencana makan kamu:\n";
    $bodyText .= "Makanan : {$foodName}\n";
    $bodyText .= "Jenis   : {$mealType}\n";
    $bodyText .= "Waktu   : " . $mealTime->format('d-m-Y H:i') . "\n";
    $bodyText .= "Catatan : {$notes}\n\n";
    $bodyText .= "Healthy Living Starts with Healthy Food,\nMealify";
 
    $bodyHtml = nl2br($bodyText);

    $sent = Mailer::send($email, $subject, $bodyHtml);

    if ($sent === true) {
        $upd = $conn->prepare("UPDATE meal_plans SET is_notified = 1 WHERE id = ?");
        $upd->bind_param("i", $planId);
        if ($upd->execute()) {
            cron_log("SUCCESS: Email sent to {$email} and is_notified set to 1 for plan #{$planId}");
        } else {
            cron_log("WARNING: Email sent to {$email} but FAILED to update is_notified for plan #{$planId}. DB Error: " . $conn->error);
        }
        $upd->close();
    } else {
        $errMsg = is_string($sent) ? $sent : 'Unknown error';
        cron_log("FAILED: Could not send email to {$email} for plan #{$planId}. Error: {$errMsg}");
    }
}

cron_log("Cron selesai. Jumlah plan yang diproses: {$processed}");
cron_log('=== Cron end ===');

echo "Cron selesai. Processed rows: {$processed}" . PHP_EOL;

$stmt->close();
$conn->close();
