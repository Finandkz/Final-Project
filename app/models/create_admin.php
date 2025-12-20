<?php
use App\Config\Database;
use App\Models\User;

require_once __DIR__ . "/../../vendor/autoload.php";

$db = (new Database())->connect();

$adminName = "Admin Mealify";
$adminEmail = "mymealifyhealthyfood@gmail.com";
$adminPassword = "Healthy food"; // kamu boleh ganti ini

$user = new User($db);

$existing = $user->getByEmail($adminEmail);
if ($existing) {
    echo "Admin sudah ada:<br>";
    echo "Email: $adminEmail<br>";
    echo "Password: (password yang kamu gunakan waktu membuat admin)";
    exit;
}

$hash = password_hash($adminPassword, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (name, email, password, role, is_verified, created_at)
        VALUES (?, ?, ?, 'admin', 1, NOW())";

$stmt = $db->prepare($sql);
$stmt->bind_param("sss", $adminName, $adminEmail, $hash);

if ($stmt->execute()) {
    echo "Admin berhasil dibuat!<br>";
    echo "Email: $adminEmail<br>";
    echo "Password: $adminPassword<br><br>";
    echo "<b>Segera hapus file create_admin.php demi keamanan!</b>";
} else {
    echo "Gagal membuat admin: " . $stmt->error;
}
