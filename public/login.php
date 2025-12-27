<?php
ob_start();
use App\Config\Database;
use App\Controllers\AuthController;
use App\Controllers\GoogleController;
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
$google = new GoogleController($db);
$errors = [];

  if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::validateCsrfToken($_POST['_csrf'] ?? '')) {
         $errors[] = "Invalid CSRF token. Please refresh the page.";
    } else {
    $email = trim($_POST['email'] ?? ''); $password = $_POST['password'] ?? ''; 
      if(!$email || !$password) { $errors[] = "Email & password are required."; }
      else { $res = $auth->login($email, $password);

      if($res['status'] === 'ok') { 
        $user = $res['user'];
        Session::set('user', $user);
        session_write_close();
      if ($user['role'] === 'admin') { 
        header("Location: admin/dashboard.php"); 
      } else { 
        header("Location: mahasiswa/mhs_dashboard.php"); 
      } exit; 
    } else { 
      if($res['message'] === 'NOT_VERIFIED') {
        Session::set('pending_email', $email);
        header("Location: otp-verify.php");
        exit;
      } else { 
        $errors[] = "Incorrect email or password."; 
      } 
    } 
  } 
 }
}
$googleUrl = $google->getAuthUrl();
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Mealify</title>
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
        <h1>Welcome Back</h1>
        <p class="muted">Login to continue your healthy journey</p>
        <?php if($errors): ?>
          <div class="alert"><?= implode("<br>", array_map('htmlspecialchars',$errors)) ?></div>
        <?php endif; ?>

        <?php if($success = Session::get('login_success')): ?>
          <div class="success">
            <?= htmlspecialchars($success) ?>
          </div>
          <?php Session::remove('login_success'); ?>
        <?php endif; ?>

        <form method="post">
          <input type="hidden" name="_csrf" value="<?= Session::generateCsrfToken() ?>">
          
          <div class="input-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="name@example.com">
          </div>
          
          <div class="input-group">
            <label>Password</label>
            <div class="password-wrapper">
              <input type="password" name="password" id="login-password" required placeholder="Enter your password">
              <span class="toggle-password" data-target="login-password">👁</span>
            </div>
          </div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
             </div> 
          <div>
             <a href="forgot-password.php" class="link" style="font-size:14px;">Forgot Password?</a>
          </div>

          <button class="btn" type="submit">Login</button>
        </form>

        <div class="separator">OR</div>
        
        <a class="btn-google" href="<?= htmlspecialchars($googleUrl) ?>">
          <img src="https://www.svgrepo.com/show/475656/google-color.svg" width="20" alt="Google">
          Login with Google
        </a>
        
        <p class="small">Don't have an account? <a href="register.php" class="link">Create an account</a></p>
      </div>
    </div>
  </div>
<script src="assets/js/auth.js"></script>
</body>
</html>
