const sidebar = document.getElementById("sidebar");

document.getElementById("openSidebar").onclick = () =>
    sidebar.classList.add("show");

document.getElementById("closeSidebar").onclick = () =>
    sidebar.classList.remove("show");


const filterBox = document.getElementById("filterBox");

document.getElementById("openFilter").onclick = () =>
    filterBox.classList.add("show");

document.getElementById("closeFilter").onclick = () =>
    filterBox.classList.remove("show");


const searchInput = document.getElementById("searchKeyword");
const results = document.getElementById("results");

let lastHitsRaw = [];
let displayedHits = [];

let lastFilter = {
    calFrom: null,
    calTo: null,
    diet: "",
    health: ""
};

async function searchRecipe() {
    const keyword = searchInput.value.trim();
    if (keyword === "") {
        results.innerHTML = `
            <div class="search-state-container search-empty-state">
                <i data-feather="search" class="search-state-icon"></i>
                <div class="search-state-text">Start searching for content</div>
                <div class="search-state-subtext">Enter a food name or keyword above</div>
            </div>
        `;
        feather.replace();
        return;
    }

    results.innerHTML = `
        <div class="search-state-container">
            <div class="search-spinner"></div>
            <div class="search-state-text">Searching for delicious recipes...</div>
        </div>
    `;
    
    const yOffset = -200;
    const y = results.getBoundingClientRect().top + window.pageYOffset + yOffset;
    window.scrollTo({ top: y, behavior: 'smooth' });

    const calFromStr = document.getElementById("calFrom").value;
    const calToStr   = document.getElementById("calTo").value;

    lastFilter.calFrom = calFromStr ? parseInt(calFromStr) : null;
    lastFilter.calTo   = calToStr   ? parseInt(calToStr)   : null;
    lastFilter.diet    = document.getElementById("diet").value;
    lastFilter.health  = document.getElementById("health").value;

    const params = new URLSearchParams();
    params.set("keyword", keyword);
    if (lastFilter.diet)   params.set("diet", lastFilter.diet);
    if (lastFilter.health) params.set("health", lastFilter.health);

    try {
        const res = await fetch("../../app/api/search.php?" + params.toString());
        const data = await res.json();

        lastHitsRaw = data.hits || [];
        renderResults();

    } catch (e) {
        results.innerHTML = `
            <div class="search-state-container search-error-state">
                <i data-feather="alert-circle" class="search-state-icon"></i>
                <div class="search-state-text">Oops! Something went wrong</div>
                <div class="search-state-subtext">Please check your connection and try again</div>
            </div>
        `;
        feather.replace();
    }
}

function renderResults() {
    let hits = [...lastHitsRaw];

    if (lastFilter.calFrom !== null && lastFilter.calTo !== null) {
        hits = hits.filter(h => {
            const r = h.recipe;
            const total = r.calories || 0;
            const servings = r.yield || 1;
            const perServing = total / servings;

            return perServing >= lastFilter.calFrom &&
                   perServing <= lastFilter.calTo;
        });
    }

    displayedHits = hits;

    if (!hits.length) {
        results.innerHTML = `
            <div class="search-state-container">
                <i data-feather="monitor" class="search-state-icon"></i> <!-- Using monitor as generic empty icon, or user can suggest otherwise -->
                <div class="search-state-text">No results found</div>
                <div class="search-state-subtext">Try adjusting your keywords or filters</div>
            </div>
        `;
        feather.replace();
        return;
    }

    let html = `<div class="grid">`;

    hits.forEach((h, i) => {
        const r = h.recipe;
        const kcal = Math.round((r.calories || 0) / (r.yield || 1));

        html += `
            <div class="card" onclick="viewDetail(${i})">
                <img src="${r.image}" class="card-img">
                <div class="card-body">
                    <div class="card-title">${r.label}</div>
                    <div class="card-cal">🔥 ${kcal} Kcal / Serving</div>
                </div>
            </div>
        `;
    });

    html += `</div>`;
    results.innerHTML = html;
}

searchInput.addEventListener("keydown", e => {
    if (e.key === "Enter") {
        e.preventDefault();
        searchRecipe();
    }
});

document.getElementById("applyFilter").onclick = () => {
    filterBox.classList.remove("show");
    searchRecipe();
};

function viewDetail(index) {
    sessionStorage.setItem(
        "mealify_selected_recipe",
        JSON.stringify(displayedHits[index].recipe)
    );
    window.location.href = "detail.php";
}

feather.replace();
