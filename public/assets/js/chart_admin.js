
async function fetchJson(url) {
    const res = await fetch(url, { credentials: "same-origin" });
    if (!res.ok) throw new Error("HTTP " + res.status);
    return res.json();
}

let nutriChart = null;
let activityChart = null;

function ensureCanvasContext(id) {
    const el = document.getElementById(id);
    if (!el) throw new Error("Canvas not found: " + id);
    return el.getContext("2d");
}

function padDates(from, to) {
    const dates = [];
    let cur = new Date(from);
    const end = new Date(to);

    while (cur <= end) {
        const y = cur.getFullYear();
        const m = String(cur.getMonth() + 1).padStart(2, "0");
        const d = String(cur.getDate()).padStart(2, "0");
        dates.push(`${y}-${m}-${d}`);
        cur.setDate(cur.getDate() + 1);
    }
    return dates;
}

function onShowButtonClick() {
    loadNutritionStats();
    loadActivityStreak();
}

async function loadNutritionStats() {
    const from = document.getElementById("from").value;
    const to = document.getElementById("to").value;
    const userId = document.getElementById("user_id").value;

    if (!userId) {
        return; 
    }
    
    if(!from || !to) {
        alert("Select date range");
        return;
    }

    const nutriUrl =
        `../../app/api/nutrition_stats.php?from=${encodeURIComponent(from)}` +
        `&to=${encodeURIComponent(to)}` +
        `&user_id=${encodeURIComponent(userId)}`;

    try {
        const nutriJson = await fetchJson(nutriUrl);
        if (!nutriJson.success) throw new Error(nutriJson.error || "nutrition_stats failed");

        const labels = padDates(from, to);
        const nutriMap = {};
        nutriJson.data.forEach(r => (nutriMap[r.tanggal] = r));

        const calories = [];
        const protein = [];
        const carbs = [];
        const fat = [];

        labels.forEach(d => {
            const r = nutriMap[d] || {};
            calories.push(+r.calories || 0);
            protein.push(+r.protein || 0);
            carbs.push(+r.carbs || 0);
            fat.push(+r.fat || 0);
        });

        renderNutriGroupedBar(labels, calories, protein, carbs, fat);
    } catch (err) {
        console.error("Nutrition Chart Error:", err);
        alert("Failed to load nutrition chart: " + err.message);
    }
}


async function loadActivityStreak() {
    const from = document.getElementById("from").value;
    const to = document.getElementById("to").value;

    if(!from || !to) return;

    const actUrl =
        `../../app/api/activity_stats.php?from=${encodeURIComponent(from)}` +
        `&to=${encodeURIComponent(to)}`;

    try {
        const actJson = await fetchJson(actUrl);
        if (!actJson.success) throw new Error(actJson.error || "activity_stats failed");

        const labels = padDates(from, to);
        const actMap = {};
        actJson.data.forEach(r => (actMap[r.tanggal] = r.user_count));

        const activityCounts = labels.map(d => +actMap[d] || 0);

        renderActivityLine(labels, activityCounts);
    } catch (err) {
        console.error("Activity Chart Error:", err);
    }
}


function renderNutriGroupedBar(labels, calories, protein, carbs, fat) {
    const ctx = ensureCanvasContext("nutriBarChart");

    const colors = {
        calories: { bg: 'rgba(255, 159, 64, 0.85)', border: 'rgba(255, 159, 64, 1)' },
        protein: { bg: 'rgba(54, 162, 235, 0.85)', border: 'rgba(54, 162, 235, 1)' },
        carbs: { bg: 'rgba(75, 192, 192, 0.85)', border: 'rgba(75, 192, 192, 1)' },
        fat: { bg: 'rgba(255, 99, 132, 0.85)', border: 'rgba(255, 99, 132, 1)' }
    };

    const datasets = [
        {
            label: "Kalori (kcal)",
            data: calories,
            backgroundColor: colors.calories.bg,
            borderColor: colors.calories.border,
            borderWidth: 1,
            borderRadius: 6,
            barPercentage: 0.7,
            categoryPercentage: 0.8
        },
        {
            label: "Protein (g)",
            data: protein,
            backgroundColor: colors.protein.bg,
            borderColor: colors.protein.border,
            borderWidth: 1,
            borderRadius: 6,
            barPercentage: 0.7,
            categoryPercentage: 0.8
        },
        {
            label: "Karbohidrat (g)",
            data: carbs,
            backgroundColor: colors.carbs.bg,
            borderColor: colors.carbs.border,
            borderWidth: 1,
            borderRadius: 6,
            barPercentage: 0.7,
            categoryPercentage: 0.8
        },
        {
            label: "Lemak (g)",
            data: fat,
            backgroundColor: colors.fat.bg,
            borderColor: colors.fat.border,
            borderWidth: 1,
            borderRadius: 6,
            barPercentage: 0.7,
            categoryPercentage: 0.8
        },
    ];

    if (!nutriChart) {
        nutriChart = new Chart(ctx, {
            type: "bar",
            data: {
                labels: [], 
                datasets: []
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                interaction: { mode: "index", intersect: false },
                plugins: {
                    legend: { 
                        position: "top",
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            font: { size: 12, family: "'Segoe UI', sans-serif" }
                        }
                    },
                    tooltip: { 
                        mode: "index", 
                        intersect: false,
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#333',
                        bodyColor: '#555',
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 12 },
                        borderColor: '#ddd',
                        borderWidth: 1,
                        padding: 10,
                        boxPadding: 4
                    },
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: "Jumlah" },
                        grid: {
                            color: '#f3f4f6',
                            borderDash: [5, 5]
                        }
                    },
                },
            },
        });
    }

    nutriChart.data.labels = labels;
    nutriChart.data.datasets = datasets;
    nutriChart.update();
}

function renderActivityLine(labels, activityCounts) {
    const ctx = ensureCanvasContext("activityLineChart");

    const animationOptions = {
        duration: 2000,
        easing: 'easeOutQuart',
    };

    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.5)');
    gradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

    if (!activityChart) {
        activityChart = new Chart(ctx, {
            type: "line",
            data: {
                labels: [],
                datasets: [{
                    label: "User Aktif (Streak)",
                    data: [],
                    borderColor: "#10b981",
                    backgroundColor: gradient,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#10b981',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                    pointHoverBorderWidth: 3
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                interaction: {
                    mode: 'nearest',
                    intersect: false,
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        title: { display: true, text: "Jumlah User" },
                        grid: {
                            color: '#f3f4f6',
                            borderDash: [5, 5]
                        }
                    },
                },
                animation: animationOptions,
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#333',
                        bodyColor: '#555',
                        borderColor: '#ddd',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return ` ${context.parsed.y} Users Active`;
                            }
                        }
                    }
                },
            },
        });
    }

    activityChart.data.labels = labels;
    activityChart.data.datasets[0].data = activityCounts;
    
    activityChart.options.scales.y.suggestedMax = Math.max(...activityCounts, 1) + 1;
    
    activityChart.update();
}

document
    .getElementById("loadBtn")
    .addEventListener("click", () => {
        const userId = document.getElementById("user_id").value;
        if (!userId) {
            showModal("Attention", "Please select a user first to view nutrition charts.");
        }
        onShowButtonClick();
    });

document.addEventListener("DOMContentLoaded", () => {
    loadActivityStreak();
});

function showModal(title, message) {
    const modal = document.getElementById("warningModal");
    const modalTitle = document.getElementById("modalTitle");
    const modalMessage = document.getElementById("modalMessage");

    if (modal && modalTitle && modalMessage) {
        modalTitle.innerText = title;
        modalMessage.innerText = message;
        modal.classList.add("active");
    } else {
        alert(title + ": " + message);
    }
}

function closeModal() {
    const modal = document.getElementById("warningModal");
    if (modal) {
        modal.classList.remove("active");
    }
}

document.addEventListener("click", (e) => {
    const modal = document.getElementById("warningModal");
    if (modal && e.target === modal) {
        closeModal();
    }
});
