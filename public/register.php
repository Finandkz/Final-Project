<?php
ob_start();
use App\Config\Database;
use App\Controllers\AuthController;
use App\Helpers\Session;

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
$errors = [];
$success = null;

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::validateCsrfToken($_POST['_csrf'] ?? '')) {
        $errors[] = "Invalid CSRF token. Please refresh the page.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if(!$name || !$email || !$password) {
            $errors[] = "All fields are required.";
        } else {
            $res = $auth->registerAndSendOTP($name, $email, $password);
            if($res['status'] === 'ok') {
                Session::set('pending_email', $email);
                session_write_close();
                header("Location: otp-verify.php");
                exit;
            } else {
                $errors[] = $res['message'];
            }
        }
    }
}
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register — Mealify</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="auth-wrap">
    <div class="auth-left">
      <img src="assets/img/logo.jpg" alt="logo">
    </div>
    <div class="auth-right">
      <div class="card">
        <h1>Create Account</h1>
        <p class="muted">Join us and start your healthy journey</p>
        <?php if($errors): ?>
          <div class="alert">
            <?= implode("<br>", array_map('htmlspecialchars',$errors)) ?>
          </div>
        <?php endif; ?>
        <?php if($success): ?>
          <div class="success">
            <?= implode("<br>", array_map('htmlspecialchars',$success)) ?>
          </div>
        <?php endif; ?>

        <form method="post">
          <input type="hidden" name="_csrf" value="<?= Session::generateCsrfToken() ?>">
          
          <div class="input-group">
            <label>Full Name</label>
            <input type="text" name="name" required placeholder="Gibran Rakabuming">
          </div>
          
          <div class="input-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="name@example.com">
          </div>
          
          <div class="input-group">
            <label>Password</label>
            <div class="password-wrapper">
              <input type="password" name="password" id="register-password" required placeholder="Create a password">
            <span class="toggle-password" data-target="register-password">👁</span>
            </div>
          </div>
          
          <button class="btn" type="submit">Register</button>
        </form>
        <p class="small">Already have an account? <a href="login.php" class="link">Login</a></p>
      </div>
    </div>
  </div>
<script src="assets/js/auth.js"></script>
</body>
</html>
