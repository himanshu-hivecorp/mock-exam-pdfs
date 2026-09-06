/**
 * Mock Exam PDFs - Frontend Interactive Scripts
 */

document.addEventListener('DOMContentLoaded', () => {
    // Category Mega Dropdown toggle
    const categoryDropdownBtn = document.getElementById('categoryDropdownBtn');
    const dropdownWrapper = categoryDropdownBtn ? categoryDropdownBtn.closest('.nav-item-dropdown') : null;
    if (categoryDropdownBtn && dropdownWrapper) {
        categoryDropdownBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropdownWrapper.classList.toggle('open');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!dropdownWrapper.contains(e.target)) {
                dropdownWrapper.classList.remove('open');
            }
        });
    }

    // Mobile navigation drawer toggle
    const menuToggle = document.getElementById('menuToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    const drawerOverlay = document.getElementById('mobileDrawerOverlay');
    const closeDrawerBtn = document.getElementById('closeDrawerBtn');

    const openDrawer = () => {
        if (mobileMenu) mobileMenu.classList.add('open');
        if (drawerOverlay) drawerOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    const closeDrawer = () => {
        if (mobileMenu) mobileMenu.classList.remove('open');
        if (drawerOverlay) drawerOverlay.classList.remove('active');
        document.body.style.overflow = '';
    };

    if (menuToggle) menuToggle.addEventListener('click', openDrawer);
    if (closeDrawerBtn) closeDrawerBtn.addEventListener('click', closeDrawer);
    if (drawerOverlay) drawerOverlay.addEventListener('click', closeDrawer);

    // Live search filter on home page
    const liveSearchInput = document.getElementById('liveSearchInput');
    const examCards = document.querySelectorAll('.exam-card');
    if (liveSearchInput && examCards.length > 0) {
        liveSearchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            let matches = 0;
            examCards.forEach(card => {
                const title = card.getAttribute('data-title') || '';
                const category = card.getAttribute('data-category') || '';
                const excerpt = card.getAttribute('data-excerpt') || '';
                const haystack = (title + ' ' + category + ' ' + excerpt).toLowerCase();

                if (haystack.includes(query)) {
                    card.style.display = 'flex';
                    matches++;
                } else {
                    card.style.display = 'none';
                }
            });

            const noResults = document.getElementById('noResultsMsg');
            if (noResults) {
                noResults.style.display = (matches === 0) ? 'block' : 'none';
            }
        });
    }

    // Copy share link helper
    const copyBtns = document.querySelectorAll('.btn-copy-link');
    copyBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const urlToCopy = btn.getAttribute('data-url') || window.location.href;
            navigator.clipboard.writeText(urlToCopy).then(() => {
                const originalText = btn.innerHTML;
                btn.innerHTML = '<span>✓ Copied!</span>';
                setTimeout(() => {
                    btn.innerHTML = originalText;
                }, 2000);
            }).catch(err => {
                console.error('Copy failed: ', err);
            });
        });
    });

    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-auto-dismiss');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});
