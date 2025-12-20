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

    if (deleteBtn) {
        const backdrop = document.createElement('div');
        backdrop.className = 'account-modal-backdrop';
        backdrop.id = 'account-delete-modal';

        backdrop.innerHTML = `
            <div class="account-modal">
                <h2>Are you sure?</h2>
                <p>Do you want to delete this account? This action cannot be undone.</p>
                <div class="account-modal-actions">
                    <button class="account-modal-btn account-modal-btn--danger" id="delete-confirm">Delete</button>
                    <button class="account-modal-btn account-modal-btn--secondary" id="delete-cancel">Cancel</button>
                </div>
            </div>
        `;

        document.body.appendChild(backdrop);

        const showModal   = () => backdrop.classList.add('show');
        const hideModal   = () => backdrop.classList.remove('show');
        const cancelBtn   = backdrop.querySelector('#delete-cancel');
        const confirmBtn  = backdrop.querySelector('#delete-confirm');

        deleteBtn.addEventListener('click', (e) => {
            e.preventDefault();
            showModal();
        });

        cancelBtn.addEventListener('click', (e) => {
            e.preventDefault();
            hideModal();
        });

        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) hideModal();
        });

        confirmBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const form = document.getElementById('delete-account-form');
            if (form) form.submit();
        });
    }
});
