document.addEventListener("DOMContentLoaded", function () {

    const sidebar = document.getElementById("sidebar");
    const openSidebarBtn = document.getElementById("openSidebar");
    const closeSidebarBtn = document.getElementById("closeSidebar");

    if (openSidebarBtn && sidebar) {
        openSidebarBtn.onclick = () => sidebar.classList.add("show");
    }
    if (closeSidebarBtn && sidebar) {
        closeSidebarBtn.onclick = () => sidebar.classList.remove("show");
    }

    document.querySelectorAll(".fav-card").forEach(card => {
        card.addEventListener("click", () => {
            const uri = card.dataset.uri;
            if (!uri) return;

            const data = localStorage.getItem("mealify_recipe_" + uri);
            if (!data) {
                alert("Recipe details are not available yet.\nOpen the recipe from search first.");
                return;
            }

            sessionStorage.setItem("mealify_selected_recipe", data);
            window.location.href = "detail.php";
        });
    });

});

feather.replace();