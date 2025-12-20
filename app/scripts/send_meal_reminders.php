<?php
use App\Helpers\Env;
use App\Config\Database;
use App\Helpers\Mailer;

require_once __DIR__ . '/../../vendor/autoload.php';

Env::load();

$db = new Database();
$conn = $db->connect();

date_default_timezone_set(Env::get('APP_TIMEZONE', 'Asia/Jakarta'));

$logFile = __DIR__ . '/notification_cron_log.txt';

function cron_log(string $message): void
{
    global $logFile;
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    echo $line;
    file_put_contents($logFile, $line, FILE_APPEND);
}

cron_log("=== Notification Sender Cron Start ===");

$now = new DateTime('now');
$nowTime = $now->format("H:i:s");
$today = $now->format("Y-m-d");

$senderEmail = Env::get('MAIL_FROM', 'no-reply@example.com');
$senderName  = Env::get('MAIL_FROM_NAME', 'Mealify');

$stmt = $conn->prepare("
    SELECT id, name, title, body, send_time, updated_at
    FROM admin_notifications
    WHERE active = 1
      AND send_time <= ?
    ORDER BY send_time ASC
");
if (!$stmt) {
    cron_log("Prepare failed: " . $conn->error);
    exit(1);
}
$stmt->bind_param("s", $nowTime);
$stmt->execute();
$templates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($templates)) {
    cron_log("Tidak ada template yang perlu diproses (active=1 AND send_time <= {$nowTime}).");
    cron_log("=== Notification Sender Cron End ===");
    $conn->close();
    exit(0);
}

$usersQ = "
    SELECT id, name, email
    FROM users
    WHERE (role IS NULL OR role <> 'admin')
      AND (is_active = 1 OR is_active IS NULL)
";
$userRes = $conn->query($usersQ);
if (!$userRes) {
    cron_log("Gagal ambil user: " . $conn->error);
    $conn->close();
    exit(1);
}
$users = $userRes->fetch_all(MYSQLI_ASSOC);

if (empty($users)) {
    cron_log("Tidak ada user penerima (non-admin aktif) untuk dikirimi template ini.");
    cron_log("=== Notification Sender Cron End ===");
    $conn->close();
    exit(0);
}

foreach ($templates as $tpl) {
    $tplId = (int)$tpl['id'];
    $tplName = $tpl['name'];
    $tplSendTime = $tpl['send_time'];
    $tplUpdatedAt = $tpl['updated_at'] ?? null;
    cron_log("--- Processing Template: {$tplName} (id={$tplId}) send_time={$tplSendTime} ---");

    $sentCount = 0;
    $skippedCount = 0;

    foreach ($users as $u) {
        $userId = (int)$u['id'];
        $userEmail = $u['email'];

        $chk = $conn->prepare("
            SELECT sent_at
            FROM notification_logs
            WHERE notification_id = ? AND user_id = ?
            ORDER BY sent_at DESC
            LIMIT 1
        ");
        $chk->bind_param("ii", $tplId, $userId);
        $chk->execute();
        $res = $chk->get_result()->fetch_assoc();
        $chk->close();

        $sentAt = $res['sent_at'] ?? null;
        $alreadyToday = false;

        if ($sentAt) {
            $sentDate = (new DateTime($sentAt))->format('Y-m-d');
            if ($sentDate === $today) {
                if (!empty($tplUpdatedAt)) {
                    $sentTs = strtotime($sentAt);
                    $tplUpdatedTs = strtotime($tplUpdatedAt);
                    if ($sentTs >= $tplUpdatedTs) {
                        $alreadyToday = true;
                    } else {
                        $alreadyToday = false;
                    }
                } else {
                    $alreadyToday = true;
                }
            }
        }

        if ($alreadyToday) {
            $skippedCount++;
            continue;
        }

        $subject = $tpl['title'] ?: 'Pengingat dari Mealify';
        $bodyText = str_replace('{name}', $u['name'], $tpl['body']);

        $bodyHtml = "<div style='font-family:Arial,sans-serif;line-height:1.5;color:#222'>";
        $bodyHtml .= "<p><strong>From:</strong> " . htmlspecialchars($senderName) . "</p>";
        $bodyHtml .= "<hr style='border:none;border-top:1px solid #eee;margin:8px 0 12px 0'>";
        $bodyHtml .= "<div style='white-space:pre-wrap;'>" . nl2br(htmlspecialchars($bodyText)) . "</div>";
        $bodyHtml .= "<hr style='border:none;border-top:1px solid #eee;margin:12px 0 8px 0'>";
        $bodyHtml .= "<p style='font-size:12px;color:#666'>Mealify Notification System</p>";
        $bodyHtml .= "</div>";

        try {
            $result = Mailer::send($userEmail, $subject, $bodyHtml);
            if ($result === true) {
                $status = 'sent';
                $error = null;
            } else {
                $status = 'failed';
                $error  = is_string($result) ? $result : 'sending_failed';
            }
        } catch (Throwable $e) {
            $status = 'failed';
            $error  = $e->getMessage();
        }

        $ins = $conn->prepare("
            INSERT INTO notification_logs (notification_id, user_id, sent_at, status, error)
            VALUES (?, ?, NOW(), ?, ?)
        ");
        $ins->bind_param("iiss", $tplId, $userId, $status, $error);
        $ins->execute();
        $ins->close();

        if ($status === 'sent') {
            cron_log("-> SENT to {$userEmail}");
            $sentCount++;
        } else {
            cron_log("-> FAILED to {$userEmail} Error: {$error}");
        }

    } 

    cron_log("Template id={$tplId} done: sent={$sentCount}, skipped={$skippedCount}");
}

cron_log("=== Notification Sender Cron End ===");
$conn->close();
