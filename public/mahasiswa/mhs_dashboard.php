<?php
use App\Helpers\Session;
use App\Config\Database;
use App\Helpers\Env;
use App\Classes\ApiClientEdamam;

require_once __DIR__ . "/../../vendor/autoload.php";

Session::start();

$user = Session::get("user");
if (!$user) {
    header("Location: ../login.php");
    exit;
}

Env::load();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Mealify</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/search-states.css">
</head>
<body>
<div class="main-wrapper">
<header class="header">
    <div class="header-left">
        <img src="../assets/img/logo.jpg" class="logo">
        <h1 class="brand">MEALIFY</h1>
    </div>
    <div class="header-center">
        <div class="search-box">
            <input type="text" id="searchKeyword" placeholder="Search for healthy food..." autocomplete="off">
        </div>
        <button class="filter-btn" id="openFilter"><i data-feather="filter"></i></button>
    </div>
    <div class="header-right">
        <button class="menu-btn" id="openSidebar">☰</button>
    </div>
</header>
<div class="sidebar" id="sidebar">
    <button class="close-btn" id="closeSidebar">✕</button>
    <h2>Account</h2>
    <ul>
        <li><a href="account.php"><i data-feather="user" class="simbol"></i> Account Information</a></li>
        <li><a href="change_password.php"><i data-feather="edit" class="simbol"></i> Change Password</a></li>
        <li><a href="favorites.php"><i data-feather="star" class="simbol"></i> Favorite</a></li>
        <li><a href="../logout.php"><i data-feather="log-out" class="simbol"></i>Logout</a></li>
    </ul>
</div>
<div class="filter-panel" id="filterBox">
    <button class="filter-close" id="closeFilter">✕</button>
    <h2>Recipe Filter</h2>
    <label>Calories:</label>
    <div class="filter-row">
        <input type="number" id="calFrom" placeholder="From">
        <input type="number" id="calTo" placeholder="To">
    </div>
    <label>Diet:</label>
<select id="diet">
    <option value="">-- Default --</option>
    <option value="balanced">Balanced</option>
    <option value="high-protein">High Protein</option>
    <option value="high-fiber">High Fiber</option>
    <option value="low-fat">Low Fat</option>
    <option value="low-carb">Low Carb</option>
    <option value="low-sodium">Low Sodium</option>
</select>
<label>Allergies:</label>
<select id="health">
    <option value="">-- Default --</option>
    <option value="vegetarian">Vegetarian</option>
    <option value="vegan">Vegan</option>
    <option value="gluten-free">Gluten Free</option>
    <option value="dairy-free">Dairy Free</option>
    <option value="peanut-free">Peanut Free</option>
</select>
    <button class="apply-btn" id="applyFilter">Apply</button>
</div>
<section class="banner">
    <img src="../assets/img/Banner.jpg">
</section>
<section class="menu-container">
    <a class="menu-box" href="meal_planner.php">
        <div class="icon">📅</div>
        <span>Meal Planner</span>
    </a>
    <a class="menu-box" href="nutrition.php">
        <div class="icon">📋</div>
        <span>Nutrition Analyzer</span>
    </a>
    <a class="menu-box" href="streak.php">
        <div class="icon">📈</div>
        <span>Streak</span>
    </a>
</section>
<section id="results" class="results"></section>
</div>
<footer class="footer">
    © 2025 Mealify — Healthy Living Starts with Healthy Food
</footer>
<script src="../assets/js/dashboard.js"></script>
</body>
</html>
