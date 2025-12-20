<?php
use App\Config\Database;
use App\Controllers\PasswordController;
use App\Helpers\Session;

require_once __DIR__ . "/../vendor/autoload.php";

Session::start();
$db = (new Database())->connect();
$pc = new PasswordController($db);

$token = $_GET['token'] ?? null;
$valid = $token ? $pc->validateToken($token) : false;
$err = $msg = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::validateCsrfToken($_POST['_csrf'] ?? '')) {
        $err = "Invalid CSRF token. Please refresh the page.";
    } else {
        $token = $_POST['token'] ?? '';
        $pass = $_POST['password'] ?? '';
        $pass2 = $_POST['password_confirm'] ?? '';
        if(!$pass || !$pass2) $err = "Isi semua field.";
        elseif($pass !== $pass2) $err = "Password tidak cocok.";
        elseif(strlen($pass) < 8 || !preg_match('/[A-Z]/', $pass)) {
            $err = "Passwords must be at least 8 characters long and contain at least 1 capital letter.";
        }
        else {
            $ok = $pc->resetPassword($token, $pass);
            if($ok) {
                $msg = "Password successfully changed. Please log in.";
                header("Refresh: 1 ; login.php");
            } else {
                $err = "Invalid token, expired, or account cannot be reset.";
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Password</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-left"><img src="assets/img/logo.jpg" alt="logo"></div>
  <div class="auth-right">
    <div class="card">
      <h1>Reset Password</h1>
      <p class="muted">Create a new secure password</p>
      <?php if($err): ?><div class="alert"><?= htmlspecialchars($err) ?></div><?php endif; ?>
      <?php if($msg): ?><div class="success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
      <?php if($valid): ?>

        <form method="post">
          <input type="hidden" name="_csrf" value="<?= \App\Helpers\Session::generateCsrfToken() ?>">
          <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
          
          <div class="input-group">
              <label>New Password</label>
              <div class="password-wrapper">
                <input type="password" name="password" id="new-password" required placeholder="New password">
                <span class="toggle-password" data-target="new-password">👁</span>
              </div>
          </div>
          
          <div class="input-group">
              <label>Confirm Password</label>
              <div class="password-wrapper">
                <input type="password" name="password_confirm" id="confirm-password" required placeholder="Confirm password">
                <span class="toggle-password" data-target="confirm-password">👁</span>
              </div>
          </div>
          
          <button class="btn" type="submit">Reset Password</button>
        </form>
        
      <?php else: ?>
        <p class="alert">Invalid or expired Token.</p>
      <?php endif; ?>
      <p class="small"><a href="login.php" class="link">Back to Login</a></p>
    </div>
  </div>
</div>
<script src="assets/js/auth.js"></script>
</body>
</html>
        