document.addEventListener("DOMContentLoaded", function () {
    const el = document.getElementById("detailWrapper");
    const raw = sessionStorage.getItem("mealify_selected_recipe");

    const noDataMsg = document.getElementById("no-data-msg");
    const detailCard = document.querySelector(".detail-card");

    if (!raw) {
        if (noDataMsg) noDataMsg.classList.remove("d-none");
        if (detailCard) detailCard.style.display = "none";
        return;
    }

    const r = JSON.parse(raw);

function showToast(message, type = "success") {
    if (!toastEl || !toastMsgEl || !toastIconEl) {
        console.info("[Toast]", message);
        return;
    }

    toastMsgEl.textContent = message;

    toastEl.classList.remove("toast--success", "toast--error", "show");
    if (type === "error") {
        toastEl.classList.add("toast--error");
        toastIconEl.textContent = "⚠️";
    } else {
        toastEl.classList.add("toast--success");
        toastIconEl.textContent = "✅";
    }

    void toastEl.offsetWidth;
    toastEl.classList.add("show");

    setTimeout(() => {
        toastEl.classList.remove("show");
    }, 2500);
}

    try {
        if (r.uri) {
            localStorage.setItem("mealify_recipe_" + r.uri, JSON.stringify(r));
        }
    } catch (e) {
        console.warn("Failed to save recipe to localStorage", e);
    }

    const baseServings = r.yield || 1;
    let currentServings = baseServings;

    const totalCal = r.calories || 0;

    const totalDailyCal = r.totalDaily && r.totalDaily.ENERC_KCAL
        ? r.totalDaily.ENERC_KCAL.quantity
        : null;

    const healthLabels = (r.healthLabels || []).join(", ");

    function getNutTotal(tag) {
        const n = r.totalNutrients && r.totalNutrients[tag] ? r.totalNutrients[tag] : null;
        const d = r.totalDaily && r.totalDaily[tag] ? r.totalDaily[tag] : null;
        return {
            totalQty: n ? n.quantity : null,
            unit: n ? n.unit : "",
            totalPct: d ? d.quantity : null
        };
    }

    const totalFat    = getNutTotal("FAT");
    const totalSat    = getNutTotal("FASAT");
    const totalCarbs  = getNutTotal("CHOCDF");
    const totalFiber  = getNutTotal("FIBTG");
    const totalSugar  = getNutTotal("SUGAR");
    const totalProt   = getNutTotal("PROCNT");
    const totalChol   = getNutTotal("CHOLE");
    const totalSodium = getNutTotal("NA");

    const ingredientsHTML = (r.ingredientLines || [])
        .map(line => `<li>${line}</li>`)
        .join("");

    // Inject dynamic data into existing HTML
    document.getElementById("recipeTitle").textContent = r.label;
    
    // Update source link
    const sourceLink = document.getElementById("sourceLink");
    if (r.url) {
        sourceLink.href = r.url;
        sourceLink.textContent = r.source || "Source";
        sourceLink.style.display = "inline"; 
    } else {
        sourceLink.style.display = "none";
        document.getElementById("recipeSource").textContent = `Source: ${r.source || "-"}`;
    }

    // Update image
    const imgEl = document.getElementById("recipeImage");
    if (imgEl) {
        imgEl.src = r.image;
        imgEl.alt = r.label;
    }

    const ingredientsCountEl = document.getElementById("ingredientsCount");
    const ingredientsListEl = document.getElementById("ingredientsList");
    const lines = r.ingredientLines || [];
    ingredientsCountEl.textContent = `${lines.length} Ingredients`;
    ingredientsListEl.innerHTML = lines.map(line => `<li>${line}</li>`).join("");

    const healthLabelsEl = document.getElementById("healthLabels");
    healthLabelsEl.textContent = (r.healthLabels || []).join(", ");

    const mpFoodNameInput = document.getElementById("mpFoodName");
    if (mpFoodNameInput) mpFoodNameInput.value = r.label;

    const favBtn = document.getElementById("favBtn");
    const planBtn = document.getElementById("planBtn");
    const mpModal = document.getElementById("mpModal");
    const mpModalClose = document.getElementById("mpModalClose");
    const mpCancel = document.getElementById("mpCancel");
    const mpSave = document.getElementById("mpSave");
    const servingsInput = document.getElementById("servingsInput");
    const calPerServingEl = document.getElementById("calPerServing");
    const dailyValueEl = document.getElementById("dailyValuePerServing");
    const nutBarFill = document.getElementById("nutBarFill");

    const fatQtyEl    = document.getElementById("fatQty");
    const fatPctEl    = document.getElementById("fatPct");
    const satQtyEl    = document.getElementById("satQty");
    const satPctEl    = document.getElementById("satPct");
    const carbsQtyEl  = document.getElementById("carbsQty");
    const carbsPctEl  = document.getElementById("carbsPct");
    const fiberQtyEl  = document.getElementById("fiberQty");
    const fiberPctEl  = document.getElementById("fiberPct");
    const sugarQtyEl  = document.getElementById("sugarQty");
    const sugarPctEl  = document.getElementById("sugarPct");
    const protQtyEl   = document.getElementById("protQty");
    const protPctEl   = document.getElementById("protPct");
    const cholQtyEl   = document.getElementById("cholQty");
    const cholPctEl   = document.getElementById("cholPct");
    const sodiumQtyEl = document.getElementById("sodiumQty");
    const sodiumPctEl = document.getElementById("sodiumPct");

    function perServing(totalObj, servings) {
        if (!totalObj) return { qty: null, pct: null, unit: "" };
        const qty = totalObj.totalQty != null ? Math.round(totalObj.totalQty / servings) : null;
        const pct = totalObj.totalPct != null ? Math.round(totalObj.totalPct / servings) : null;
        return { qty, pct, unit: totalObj.unit };
    }

    function updateForServings(servings) {
        if (!servings || servings <= 0) return;

        currentServings = servings;

        const calPerServing = Math.round(totalCal / servings);
        const dailyPerServing = totalDailyCal
            ? Math.round(totalDailyCal / servings)
            : null;
        const barWidth = dailyPerServing ? Math.min(dailyPerServing, 120) : 0;

        calPerServingEl.textContent = calPerServing;
        dailyValueEl.textContent = dailyPerServing !== null ? dailyPerServing : "-";
        nutBarFill.style.width = barWidth + "%";

        const fat    = perServing(totalFat, servings);
        const sat    = perServing(totalSat, servings);
        const carbs  = perServing(totalCarbs, servings);
        const fiber  = perServing(totalFiber, servings);
        const sugar  = perServing(totalSugar, servings);
        const prot   = perServing(totalProt, servings);
        const chol   = perServing(totalChol, servings);
        const sodium = perServing(totalSodium, servings);

        fatQtyEl.textContent    = fat.qty   !== null ? fat.qty   + fat.unit   : "-";
        fatPctEl.textContent    = fat.pct   !== null ? fat.pct   + "%"       : "-";
        satQtyEl.textContent    = sat.qty   !== null ? sat.qty   + sat.unit   : "-";
        satPctEl.textContent    = sat.pct   !== null ? sat.pct   + "%"       : "-";
        carbsQtyEl.textContent  = carbs.qty !== null ? carbs.qty + carbs.unit : "-";
        carbsPctEl.textContent  = carbs.pct !== null ? carbs.pct + "%"       : "-";
        fiberQtyEl.textContent  = fiber.qty !== null ? fiber.qty + fiber.unit : "-";
        fiberPctEl.textContent  = fiber.pct !== null ? fiber.pct + "%"       : "-";
        sugarQtyEl.textContent  = sugar.qty !== null ? sugar.qty + sugar.unit : "-";
        sugarPctEl.textContent  = sugar.pct !== null ? sugar.pct + "%"       : "-";
        protQtyEl.textContent   = prot.qty  !== null ? prot.qty  + prot.unit  : "-";
        protPctEl.textContent   = prot.pct  !== null ? prot.pct  + "%"       : "-";
        cholQtyEl.textContent   = chol.qty  !== null ? chol.qty  + chol.unit  : "-";
        cholPctEl.textContent   = chol.pct  !== null ? chol.pct  + "%"       : "-";
        sodiumQtyEl.textContent = sodium.qty!== null ? sodium.qty+ sodium.unit: "-";
        sodiumPctEl.textContent = sodium.pct!== null ? sodium.pct+ "%"       : "-";
    }

    if (favBtn) {
        let isFavorite = false;

        function updateFavButtonUI() {
            if (isFavorite) {
                favBtn.innerHTML = "Delete from favorite";
                favBtn.style.background = "linear-gradient(135deg, #ef4444 0%, #dc2626 100%)"; // MERAH
            } else {
                favBtn.innerHTML = "Add to favorite";
                favBtn.style.background = "linear-gradient(135deg, #6b7280 0%, #4b5563 100%)"; // ABU-ABU
            }
        }

        fetch("../../app/api/check_favorite.php?uri=" + encodeURIComponent(r.uri), {
            method: "GET",
            headers: {
                "Accept": "application/json"
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    isFavorite = !!data.isFavorite;
                }
                updateFavButtonUI();
            })
            .catch(() => {
                updateFavButtonUI();
            });

        favBtn.addEventListener("click", function () {
            const apiUrl = isFavorite
                ? "../../app/api/favorite_delete.php"
                : "../../app/api/favorite.php";

            const payload = {
                uri: r.uri,
                label: r.label,
                image: r.image,
                source: r.source,
                url: r.url,
                calories: Math.round(r.calories || 0)
            };

            fetch(apiUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify(payload)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        isFavorite = !isFavorite;
                        updateFavButtonUI();
                        showToast(data.message || "Success!", "success");
                    } else {
                        showToast(data.message || "Failed to save favorites.", "error");
                    }
                })
                .catch(() => {
                    showToast("An error occurred while saving favorites..", "error");
                });
        });
    }

    if (planBtn) {
        planBtn.addEventListener("click", () => {
            mpModal.classList.add("active");
        });
    }

    if (mpModalClose) {
        mpModalClose.addEventListener("click", () => {
            mpModal.classList.remove("active");
        });
    }

    if (mpCancel) {
        mpCancel.addEventListener("click", () => {
            mpModal.classList.remove("active");
        });
    }

    window.addEventListener("click", (e) => {
        if (e.target === mpModal) {
            mpModal.classList.remove("active");
        }
    });

    if (mpSave) {
        mpSave.addEventListener("click", function() {
            const foodName = document.getElementById("mpFoodName").value;
            const mealTime = document.getElementById("mpMealTime").value;
            const mealType = document.getElementById("mpMealType").value;
            const notes = document.getElementById("mpNotes").value;

            if (!mealTime || !mealType) {
                showToast("Please fill in the meal time and type.", "error");
                return;
            }

            const payload = {
                food_name: foodName,
                meal_time: mealTime,
                meal_type: mealType,
                notes: notes
            };

            mpSave.disabled = true;
            mpSave.textContent = "Saving...";
            
            // Close modal immediately as requested
            mpModal.classList.remove("active");

            fetch("../../app/api/meal_plan_add.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, "success");
                    // Reset fields
                    document.getElementById("mpMealTime").value = "";
                    document.getElementById("mpMealType").value = "";
                    document.getElementById("mpNotes").value = "";
                } else {
                    showToast(data.message || "Failed to add meal plan.", "error");
                    // Re-open if failed so user can see/fix? 
                    // User said "langsung tutup aja", so we keep it closed but maybe reopening on error is better UX?
                    // For now, follow user's direct request to just close it.
                }
            })
            .catch(error => {
                console.error("Error adding meal plan:", error);
                showToast("An error occurred. Please try again.", "error");
            })
            .finally(() => {
                mpSave.disabled = false;
                mpSave.textContent = "Save Plan";
            });
        });
    }

    if (servingsInput) {
        servingsInput.value = baseServings;
        servingsInput.addEventListener("input", function () {
            const valStr = servingsInput.value.trim();
            if (valStr === "") return;

            const val = parseFloat(valStr);
            if (isNaN(val) || val <= 0) return;

            updateForServings(val);
        });

        servingsInput.addEventListener("blur", function () {
            const valStr = servingsInput.value.trim();
            const val = parseFloat(valStr);

            if (valStr === "" || isNaN(val) || val <= 0) {
                servingsInput.value = currentServings;
            }
        });
    }

    updateForServings(baseServings);
});
