// public/js/main.js

document.addEventListener('DOMContentLoaded', () => {
    // 1. Theme Toggle (Dark / Light)
    const themeBtn = document.getElementById('themeToggleBtn');
    const htmlTag = document.documentElement;

    const savedTheme = localStorage.getItem('recall_theme') || 'dark';
    htmlTag.setAttribute('data-theme', savedTheme);

    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            const currentTheme = htmlTag.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            htmlTag.setAttribute('data-theme', newTheme);
            localStorage.setItem('recall_theme', newTheme);
        });
    }

    // 2. Mobile Menu Toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const sidebar = document.getElementById('sidebar');

    if (mobileMenuBtn && sidebar) {
        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-open');
        });
    }

    // 3. Global Live Search Box
    const searchInput = document.getElementById('globalSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            const filterableRows = document.querySelectorAll('.table tbody tr, .player-card-item');

            filterableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // 4. Confirm Delete Dialogs
    const deleteBtns = document.querySelectorAll('.btn-confirm-delete');
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            if (!confirm('Tem certeza que deseja excluir este registro do sistema?')) {
                e.preventDefault();
            }
        });
    });
});
