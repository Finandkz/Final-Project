const sidebar = document.getElementById("sidebar");
const filterBox = document.getElementById("filterBox");

document.getElementById("openSidebar").onclick = () => {
    sidebar.classList.add("show");
    // Close filter if open
    if (filterBox) filterBox.classList.remove("show");
};

document.getElementById("closeSidebar").onclick = () =>
    sidebar.classList.remove("show");


document.getElementById("openFilter").onclick = () => {
    filterBox.classList.add("show");
    // Close sidebar if open
    if (sidebar) sidebar.classList.remove("show");
};

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

    const statusContainer = document.getElementById('status-container');
    const stateEmpty = document.getElementById('state-empty');
    const stateLoading = document.getElementById('state-loading');
    const stateError = document.getElementById('state-error');
    const stateNoResults = document.getElementById('state-no-results');
    const resultsGrid = document.getElementById('results-grid');
    const template = document.getElementById('recipe-card-template');

    function showState(element) {
        stateEmpty.classList.add('d-none');
        stateLoading.classList.add('d-none');
        stateError.classList.add('d-none');
        stateNoResults.classList.add('d-none');
        resultsGrid.innerHTML = ''; // Clear results

        if (element) {
            element.classList.remove('d-none');
        }
        feather.replace();
    }

    async function searchRecipe() {
        const keyword = searchInput.value.trim();
        if (keyword === "") {
            showState(stateEmpty);
            return;
        }

        showState(stateLoading);
        
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
            showState(stateError);
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
            showState(stateNoResults);
            return;
        }

        // Clear states and show results
        showState(null); 
        
        hits.forEach((h, i) => {
            const r = h.recipe;
            const kcal = Math.round((r.calories || 0) / (r.yield || 1));

            const clone = template.content.cloneNode(true);
            const card = clone.querySelector('.card');
            const img = clone.querySelector('.card-img');
            const title = clone.querySelector('.card-title');
            const cal = clone.querySelector('.card-cal');

            img.src = r.image;
            img.alt = r.label;
            title.textContent = r.label;
            cal.textContent = `🔥 ${kcal} Kcal / Serving`;
            
            card.onclick = () => viewDetail(i);

            resultsGrid.appendChild(clone);
        });
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
