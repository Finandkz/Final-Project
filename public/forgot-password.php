<?php
use App\Config\Database;
use App\Controllers\PasswordController;
use App\Helpers\Session;

require_once __DIR__ . "/../vendor/autoload.php";

Session::start();
$db = (new Database())->connect();
$pc = new PasswordController($db);

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::validateCsrfToken($_POST['_csrf'] ?? '')) {
        $err = "Invalid CSRF token. Please refresh the page.";
    } else {
        $email = trim($_POST['email'] ?? '');
        if (!$email) {
            $err = "Please enter your email.";
        } else {
            $result = $pc->sendResetLink($email);

            if ($result['ok']) {
                $msg = "Password reset link sent to email.";
            } else {
                switch ($result['reason']) {
                    case 'GOOGLE_ONLY':
                        $err = "This account uses Google login. "
                             . "Password cannot be reset from the application. "
                             . "Please manage security via your Google account.";
                        break;
                    case 'NOT_FOUND':
                        $err = "Email not registered.";
                        break;
                    case 'MAIL_ERROR':
                    default:
                        $err = "Failed to send password reset link. Try again later.";
                        break;
                }
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Forgot Password</title>
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
      <h1>Forgot Password</h1>
      <p class="muted">Enter your email to receive a reset link</p>
      <?php if($err): ?><div class="alert"><?= htmlspecialchars($err) ?></div><?php endif; ?>
      <?php if($msg): ?><div class="success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
      
      <form method="post">
        <input type="hidden" name="_csrf" value="<?= Session::generateCsrfToken() ?>">
        
        <div class="input-group">
          <label>Email Address</label>
          <input type="email" name="email" required placeholder="name@example.com">
        </div>
        
        <button class="btn" type="submit">Send Reset Link</button>
      </form>
      
      <p class="small"><a href="login.php" class="link">Back to Login</a></p>
    </div>
  </div>
</div>
<script src="assets/js/auth.js"></script>
</body>
</html>
