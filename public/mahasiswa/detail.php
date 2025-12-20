<?php
use App\Helpers\Session;
use App\Helpers\Env;

require_once __DIR__ . "/../../vendor/autoload.php";

Session::start();
$user = Session::get("user");
if (!$user) {
    header("Location: ../login.php");
    exit;
}

Env::load();
$appId  = Env::get("EDAMAM_APP_ID");
$appKey = Env::get("EDAMAM_APP_KEY");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Detail Recipe - Mealify</title>
    <link rel="stylesheet" href="../assets/css/detail.css">
</head>
<body class="page-root">

<div class="page-bg"></div>

<div class="page-content">
    <div class="detail-page">
        <div class="detail-wrapper" id="detailWrapper">
            <div class="back-nav">
                <a href="mhs_dashboard.php" class="back-link">← Back to dashboard</a>
            </div>

            <p id="no-data-msg" class="d-none" style="text-align:center; padding: 20px; color: #ef4444; font-weight: bold;">
                Recipe data is not available. Please return to the search page.
            </p>

            <div class="detail-layout">
                <div class="detail-left">
                    <div class="detail-img-wrap" id="detailImgWrap">
                       <img src="" alt="Recipe Image" id="recipeImage">
                    </div>
                    <div class="detail-section">
                        <h3 id="ingredientsCount">Ingredients</h3>
                        <ul id="ingredientsList"></ul>
                    </div>
                </div>
                <div class="detail-right">
                    <h1 id="recipeTitle">Loading...</h1>
                    <div class="detail-source-container">
                        <p class="detail-source">Source: <a href="#" target="_blank" id="sourceLink">-</a></p>
                        <p class="detail-source" id="recipeSource" style="display:none"></p>
                    </div>
                    
                    <div class="detail-actions">
                        <button class="fav-btn" id="favBtn">Add to favorite</button>
                        <button class="plan-btn" id="planBtn">Add to Meal Planner</button>
                    </div>

                    <div class="detail-section nut-section">
                        <h3>Nutrition</h3>
                        <div class="nut-summary">
                            <div class="nut-summary-box">
                                <div class="nut-summary-label">Calories / Serving</div>
                                <div class="nut-summary-value"><span id="calPerServing">-</span></div>
                            </div>
                            <div class="nut-summary-box">
                                <div class="nut-summary-label">% Daily Value</div>
                                <div class="nut-summary-value">
                                    <span id="dailyValuePerServing">-</span>
                                    <span class="nut-summary-unit">%</span>
                                </div>
                            </div>
                            <div class="nut-summary-box">
                                <div class="nut-summary-label">Servings</div>
                                <div class="nut-summary-value">
                                    <input id="servingsInput" type="number" min="0.1" step="0.5" class="servings-input">
                                </div>
                            </div>
                        </div>
                        <div class="nut-labels" id="healthLabels">-</div>
                        <div class="nut-bar">
                            <div class="nut-bar-fill" id="nutBarFill"></div>
                        </div>
                        <table class="nut-table">
                            <tr><th>Fat</th><td id="fatQty">-</td><td id="fatPct">-</td></tr>
                            <tr><th>Saturated</th><td id="satQty">-</td><td id="satPct">-</td></tr>
                            <tr><th>Carbs</th><td id="carbsQty">-</td><td id="carbsPct">-</td></tr>
                            <tr><th>Fiber</th><td id="fiberQty">-</td><td id="fiberPct">-</td></tr>
                            <tr><th>Sugars</th><td id="sugarQty">-</td><td id="sugarPct">-</td></tr>
                            <tr><th>Protein</th><td id="protQty">-</td><td id="protPct">-</td></tr>
                            <tr><th>Cholesterol</th><td id="cholQty">-</td><td id="cholPct">-</td></tr>
                            <tr><th>Sodium</th><td id="sodiumQty">-</td><td id="sodiumPct">-</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Meal Planner Modal -->
<div class="mp-modal" id="mpModal">
    <div class="mp-modal-content">
        <div class="mp-modal-header">
            <h2>Add to Meal Planner</h2>
            <button class="mp-modal-close" id="mpModalClose">&times;</button>
        </div>
        <div class="mp-modal-body">
            <div class="mp-field">
                <label>Food Name</label>
                <input type="text" id="mpFoodName" readonly>
            </div>
            <div class="mp-field">
                <label>Meal Time</label>
                <input type="time" id="mpMealTime" required>
            </div>
            <div class="mp-field">
                <label>Meal Type</label>
                <select id="mpMealType" required>
                    <option value="">-- Choose Type --</option>
                    <option value="sarapan">Breakfast</option>
                    <option value="makan siang">Lunch</option>
                    <option value="makan malam">Dinner</option>
                    <option value="snack">Snack</option>
                </select>
            </div>
            <div class="mp-field">
                <label>Notes (Optional)</label>
                <textarea id="mpNotes" rows="3" placeholder="Add some notes..."></textarea>
            </div>
        </div>
        <div class="mp-modal-footer">
            <button class="btn-cancel" id="mpCancel">Cancel</button>
            <button class="btn-save" id="mpSave">Save Plan</button>
        </div>
    </div>
</div>

<footer class="footer">
    © 2025 Mealify — Healthy Living Starts with Healthy Food
</footer>

<script>
    window.EDAMAM_APP_ID  = <?= json_encode($appId) ?>;
    window.EDAMAM_APP_KEY = <?= json_encode($appKey) ?>;
</script>
<script src="../assets/js/detail.js"></script>
</body>
</html>
