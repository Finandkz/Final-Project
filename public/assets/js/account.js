document.addEventListener('DOMContentLoaded', () => {
    const avatarTrigger = document.getElementById('avatar-upload-trigger');
    const avatarInput   = document.getElementById('avatarInput');

    if (avatarTrigger && avatarInput) {
        avatarTrigger.addEventListener('click', (e) => {
            e.preventDefault();
            avatarInput.click();
        });

        avatarInput.addEventListener('change', () => {
            if (avatarInput.files.length > 0) {
                document.getElementById('avatarForm').submit();
            }
        });
    }

    const deleteBtn = document.getElementById('btn-delete-account');
    const backdrop = document.getElementById('account-delete-modal');

    if (deleteBtn && backdrop) {
        const showModal   = () => backdrop.classList.add('show');
        const hideModal   = () => backdrop.classList.remove('show');
        const cancelBtn   = document.getElementById('delete-cancel');
        const confirmBtn  = document.getElementById('delete-confirm');

        deleteBtn.addEventListener('click', (e) => {
            e.preventDefault();
            showModal();
        });

        if (cancelBtn) {
            cancelBtn.addEventListener('click', (e) => {
                e.preventDefault();
                hideModal();
            });
        }

        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) hideModal();
        });

        if (confirmBtn) {
            confirmBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const form = document.getElementById('delete-account-form');
                if (form) form.submit();
            });
        }
    }

    const sidebar = document.getElementById("sidebar");
    const openSidebarBtn = document.getElementById("openSidebar");
    const closeSidebarBtn = document.getElementById("closeSidebar");

    if (openSidebarBtn && sidebar) {
        openSidebarBtn.onclick = () => sidebar.classList.add("show");
    }
    if (closeSidebarBtn && sidebar) {
        closeSidebarBtn.onclick = () => sidebar.classList.remove("show");
    }
    

    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
