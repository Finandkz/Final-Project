(function () {

  function markActiveSidebar() {
    try {
      const path = window.location.pathname.split('/').pop().toLowerCase();
      document.querySelectorAll('.admin-sidebar a').forEach(a => {
        const href = (a.getAttribute('href') || '').toLowerCase();
        a.classList.toggle('active', href.includes(path));
      });
    } catch (e) {
    }
  }

  let currentForm = null;

  function initDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (!modal) return;

    const textEl = document.getElementById('deleteModalText');
    const confirmBtn = document.getElementById('confirmDelete');
    const cancelBtn = document.getElementById('cancelDelete');

    document.addEventListener('click', function (e) {

      const deleteBtn = e.target.closest('.btn-delete');
      if (deleteBtn) {
        e.preventDefault();

        currentForm = deleteBtn.closest('form');

        const name = deleteBtn.dataset.name || '';
        const type = deleteBtn.dataset.type || 'data';

        let label = 'data';
        if (type === 'user') label = 'user';
        if (type === 'template') label = 'template';

        if (textEl) {
          textEl.innerHTML =
            `Do you want to delete ${label} <strong>${name}</strong>?<br>
             This action cannot be undone.`;
        }

        modal.style.display = 'flex';
        return;
      }

      if (e.target === cancelBtn) {
        modal.style.display = 'none';
        currentForm = null;
        return;
      }

      if (e.target === confirmBtn) {
        if (currentForm) currentForm.submit();
        return;
      }

      if (e.target === modal) {
        modal.style.display = 'none';
        currentForm = null;
      }
    });
  }

  function init() {
    markActiveSidebar();
    initDeleteModal();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
