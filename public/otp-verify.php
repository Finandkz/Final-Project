<?php
ob_start();
use App\Config\Database;
use App\Controllers\AuthController;
use App\Controllers\OTPController;
use App\Helpers\Session;
use App\Models\User;

require_once __DIR__ . "/../vendor/autoload.php";

Session::start();
if (Session::get('user')) {
    $u = Session::get('user');
    session_write_close();
    if ($u['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: mahasiswa/mhs_dashboard.php");
    }
    exit;
}
$db = (new Database())->connect();

$auth = new AuthController($db);
$otpCtrl = new OTPController($db);
$userModel = new User($db);

$pendingEmail = Session::get('pending_email');
if (!$pendingEmail) {
    header("Location: register.php");
    exit;
}

$errors = [];
$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::validateCsrfToken($_POST['_csrf'] ?? '')) {
        $errors[] = "Invalid CSRF token. Please refresh the page.";
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'verify') {
            $code = trim($_POST['otp'] ?? '');

            if (!$code) {
                $errors[] = "Enter the OTP code.";
            } else {
                $ok = $auth->verifyOTP($pendingEmail, $code);

                if ($ok) {
                    Session::remove('pending_email');
                    Session::set('login_success', 'Your account has been verified! Please login.');
                    session_write_close();
                    header("Location: login.php");
                    exit;
                } else {
                    $errors[] = "OTP is invalid or has expired.";
                }
            }
        }
        elseif ($action === 'resend') {
            $sent = $otpCtrl->resend($pendingEmail);
            $msg = ($sent === true) ? "New OTP has been send." : "Failed to send OTP: " . $sent;
        }
    }
}
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Verify OTP</title>
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
        <h1>Enter OTP</h1>
        <p class="muted">Code sent to <b><?= htmlspecialchars($pendingEmail) ?></b></p>
        <?php if ($errors): ?>
            <div class="alert"><?= implode("<br>", array_map('htmlspecialchars', $errors)) ?></div>
        <?php endif; ?>
        <?php if ($msg): ?>
            <div class="success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="post">
          <input type="hidden" name="_csrf" value="<?= Session::generateCsrfToken() ?>">
          <input type="hidden" name="action" value="verify">
          
          <div class="input-group">
            <label>OTP Code</label>
            <input type="text" name="otp" placeholder="Enter 6-digit code" autocomplete="one-time-code" style="letter-spacing: 4px; font-weight: bold; text-align: center;">
          </div>
          
          <button class="btn" type="submit" name="verify">Verify Account</button>
        </form>

        <form method="post" style="margin-top:20px; text-align:center;">
          <input type="hidden" name="_csrf" value="<?= Session::generateCsrfToken() ?>">
          <input type="hidden" name="action" value="resend">
          <p class="small" style="margin-bottom:10px;">Didn't receive code?</p>
          <button class="btn-link" type="submit" name="resend" id="resendBtn">Resend OTP</button>
        </form>
        
        <p class="small" style="margin-top:20px"><a href="login.php" class="link">Back to Login</a></p>

        <script src="assets/js/auth.js"></script>
        <script src="assets/js/otp-verify.js"></script>

      </div>
    </div>
  </div>
</body>
</html>