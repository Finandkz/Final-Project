document.addEventListener('DOMContentLoaded', () => {
    const deleteButtons = document.querySelectorAll('.mp-delete-btn');
    if (!deleteButtons.length) return;

    const backdrop = document.createElement('div');
    backdrop.className = 'account-modal-backdrop';
    backdrop.id = 'mealplanner-delete-modal';

    backdrop.innerHTML = `
        <div class="account-modal">
            <h2>Are you sure?</h2>
            <p>Do you want to delete this meal plan? This action cannot be undone.</p>
            <div class="account-modal-actions">
                <button class="account-modal-btn account-modal-btn--danger" id="mp-delete-confirm">Delete</button>
                <button class="account-modal-btn account-modal-btn--secondary" id="mp-delete-cancel">Cancel</button>
            </div>
        </div>
    `;

    document.body.appendChild(backdrop);

    const showModal  = () => backdrop.classList.add('show');
    const hideModal  = () => backdrop.classList.remove('show');
    const cancelBtn  = backdrop.querySelector('#mp-delete-cancel');
    const confirmBtn = backdrop.querySelector('#mp-delete-confirm');

    let currentDeleteUrl = null;

    deleteButtons.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            currentDeleteUrl = btn.getAttribute('href');
            showModal();
        });
    });

    cancelBtn.addEventListener('click', (e) => {
        e.preventDefault();
        hideModal();
        currentDeleteUrl = null;
    });

    backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop) {
            hideModal();
            currentDeleteUrl = null;
        }
    });

    confirmBtn.addEventListener('click', (e) => {
        e.preventDefault();
        if (currentDeleteUrl) {
            window.location.href = currentDeleteUrl;
        }
    });
});
