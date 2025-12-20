<?php
use App\Helpers\Session;
require_once __DIR__ . "/../vendor/autoload.php";
Session::start();
$user = Session::get('user');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mealify - Healthy Living</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>

<div class="decor-shape shape-1"></div>
<div class="decor-shape shape-2"></div>
<div class="decor-shape shape-3"></div>
<div class="decor-star star-1">✦</div>
<div class="decor-star star-2">✦</div>
<div class="decor-star star-3">✦</div>

<nav class="navbar">
    <div class="brand">
        <img src="assets/img/logo.jpg" alt="Logo" class="nav-logo">
        MEALIFY
    </div>
    <div class="nav-links">
        <?php if($user): ?>
            <span class="user-greeting">Hi, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>!</span>
            <a href="mahasiswa/mhs_dashboard.php" class="btn-primary">Dashboard</a>
        <?php else: ?>
            <a href="login.php" class="nav-link">Login</a>
            <a href="register.php" class="btn-primary">Sign Up</a>
        <?php endif; ?>
    </div>
</nav>

<header class="hero">
    <div class="hero-content">
        <div class="hero-badge">✨ Smart Meal Planning</div>
        <h1>Eat Smart.<br>Live <span class="highlight">Happy!</span></h1>
        <p>Mealify makes healthy eating fun. Plan your meals, track your nutrients, and build streaks without the stress.</p>
        
        <div class="hero-actions">
            <?php if($user): ?>
                <a href="mahasiswa/mhs_dashboard.php" class="btn-hero">Go to Dashboard</a>
            <?php else: ?>
                <a href="register.php" class="btn-hero">Get Started</a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="hero-visual">
        <div class="image-wrapper">
            <img src="assets/img/Banner.jpg" alt="Healthy Food" class="main-img">
            <div class="float-card card-1">
                <span class="icon">🔥</span>
                <span>Streak</span>
            </div>
            <div class="float-card card-2">
                <span class="icon">🥗</span>
                <span>Nutrition</span>
            </div>
        </div>
    </div>
</header>

<section class="features">
    <div class="feature-card">
        <div class="card-icon" style="background: #FFF0F0; color: #FF6B6B;">📅</div>
        <h3>Plan It</h3>
        <p>Your weekly meals, organized.</p>
    </div>
    <div class="feature-card">
        <div class="card-icon" style="background: #F0F4FF; color: #4D96FF;">📊</div>
        <h3>Analyze</h3>
        <p>Know your nutrients instantly.</p>
    </div>
    <div class="feature-card">
        <div class="card-icon" style="background: #F0FFF4; color: #6BCB77;">🔥</div>
        <h3>Streak</h3>
        <p>Build healthy habits daily.</p>
    </div>
</section>

<footer>
    <p> © 2025 Mealify — Healthy Living Starts with Healthy Food.</p>
</footer>
</body>
</html>
