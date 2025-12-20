/* ===============================
   HELPER FETCH
================================ */
async function fetchJson(url) {
    const res = await fetch(url, { credentials: "same-origin" });
    if (!res.ok) throw new Error("HTTP " + res.status);
    return res.json();
}

let nutriChart = null;
let currentData = null;

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

async function loadAndRender() {
    const from = document.getElementById("from").value;
    const to = document.getElementById("to").value;
    const userId = document.getElementById("user_id").value;
    const loadBtn = document.getElementById("loadBtn");
    const exportBtn = document.getElementById("exportCsvBtn");
    const tableBox = document.getElementById("tableBox");

    if (!from || !to || !userId) {
        showModal("Attention", "Please select a user and date range first");
        return;
    }

    const originalBtnText = loadBtn.innerText;
    loadBtn.innerText = "Processing...";
    loadBtn.disabled = true;
    exportBtn.style.display = "none";
    tableBox.style.display = "none";

    const nutriUrl =
        `../../app/api/nutrition_stats.php?from=${encodeURIComponent(from)}` +
        `&to=${encodeURIComponent(to)}` +
        `&user_id=${encodeURIComponent(userId)}`;

    try {
        const json = await fetchJson(nutriUrl);

        if (!json.success)
            throw new Error(json.error || "nutrition_stats failed");

        const labels = padDates(from, to);
        const nutriMap = {};
        json.data.forEach(r => (nutriMap[r.tanggal] = r));

        const dataRows = [];
        const calories = [];
        const protein = [];
        const carbs = [];
        const fat = [];

        labels.forEach(d => {
            const r = nutriMap[d] || { tanggal: d, calories: 0, protein: 0, carbs: 0, fat: 0 };
            calories.push(+r.calories || 0);
            protein.push(+r.protein || 0);
            carbs.push(+r.carbs || 0);
            fat.push(+r.fat || 0);
            dataRows.push(r);
        });

        currentData = dataRows;
        renderNutriGroupedBar(labels, calories, protein, carbs, fat);
        renderTable(dataRows);
        
        exportBtn.style.display = "flex";
        tableBox.style.display = "block";

    } catch (err) {
        console.error(err);
        alert("Failed to load data. Check browser console.");
    } finally {
        loadBtn.innerText = originalBtnText;
        loadBtn.disabled = false;
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

function renderTable(rows) {
    const tbody = document.getElementById("tableBody");
    const template = document.getElementById("analytics-row-template");
    
    tbody.innerHTML = "";
    
    rows.forEach(r => {
        const clone = template.content.cloneNode(true);
        
        clone.querySelector(".col-date").textContent = r.tanggal;
        clone.querySelector(".col-cal").textContent = r.calories.toFixed(1);
        clone.querySelector(".col-prot").textContent = r.protein.toFixed(1);
        clone.querySelector(".col-carbs").textContent = r.carbs.toFixed(1);
        clone.querySelector(".col-fat").textContent = r.fat.toFixed(1);
        
        tbody.appendChild(clone);
    });
}


function exportCSV() {
    if (!currentData || currentData.length === 0) return;

    const headers = ["Date", "Calories (kcal)", "Protein (g)", "Carbs (g)", "Fat (g)"];
    
    let csvContent = "sep=,\n";
    csvContent += headers.join(",") + "\n";

    currentData.forEach(r => {
        const row = [
            `"${r.tanggal}"`,
            r.calories.toFixed(1),
            r.protein.toFixed(1),
            r.carbs.toFixed(1),
            r.fat.toFixed(1)
        ];
        csvContent += row.join(",") + "\n";
    });

    const blob = new Blob(["\uFEFF" + csvContent], { type: "text/csv;charset=utf-8;" });
    const url = URL.createObjectURL(blob);
    
    const link = document.createElement("a");
    const selectEl = document.getElementById("user_id");
    const userName = selectEl.options[selectEl.selectedIndex].text;
    const filename = `Nutrition_Report_${userName.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.csv`;
    
    link.setAttribute("href", url);
    link.setAttribute("download", filename);
    link.style.visibility = "hidden";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

document.getElementById("loadBtn").addEventListener("click", loadAndRender);
document.getElementById("exportCsvBtn").addEventListener("click", exportCSV);

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
