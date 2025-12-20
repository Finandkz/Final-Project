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

    const favCountEl = document.querySelector(".fav-count-number");

    function updateCount(delta) {
        if (!favCountEl) return;
        let current = parseInt(favCountEl.textContent || "0", 10);
        if (isNaN(current)) current = 0;
        favCountEl.textContent = Math.max(0, current + delta);
    }

    const modalOverlay = document.getElementById("favDeleteModal");
    const modalConfirmBtn = document.getElementById("favModalConfirm");
    const modalCancelBtn = document.getElementById("favModalCancel");

    let cardToDelete = null;

    function openModal(card) {
        cardToDelete = card;
        modalOverlay?.classList.add("show");
        document.body.style.overflow = "hidden";
    }

    function closeModal() {
        cardToDelete = null;
        modalOverlay?.classList.remove("show");
        document.body.style.overflow = "";
    }

    modalCancelBtn?.addEventListener("click", closeModal);

    modalOverlay?.addEventListener("click", e => {
        if (e.target === modalOverlay) closeModal();
    });

    modalConfirmBtn?.addEventListener("click", () => {
        if (!cardToDelete) return closeModal();

        const uri = cardToDelete.dataset.uri;
        if (!uri) return closeModal();

        fetch("../../app/api/favorite_delete.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify({ uri })
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message || "Favorite deleted");

            if (data.success) {
                cardToDelete.remove();
                updateCount(-1);

                try {
                    localStorage.removeItem("mealify_recipe_" + uri);
                } catch {}

                if (!document.querySelector(".fav-card")) {
                    document.querySelector(".fav-content")?.insertAdjacentHTML(
                        "beforeend",
                        `<div class="fav-empty">
                           💚<br>There are no favorite recipes yet.<br>
                             Add one from the recipe details page.
                         </div>`
                    );
                }
            }
        })
        .finally(closeModal);
    });

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