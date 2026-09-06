/**
 * Mock Exam PDFs - Admin Panel Interactive Scripts
 */

document.addEventListener('DOMContentLoaded', () => {
    // Auto-generate slug from title
    const titleInput = document.getElementById('postTitle');
    const slugInput = document.getElementById('postSlug');
    let userEditedSlug = false;

    if (slugInput) {
        slugInput.addEventListener('input', () => {
            userEditedSlug = true;
        });
    }

    if (titleInput && slugInput) {
        titleInput.addEventListener('input', () => {
            if (!userEditedSlug || slugInput.value.trim() === '') {
                const slug = titleInput.value
                    .toLowerCase()
                    .trim()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                slugInput.value = slug;
            }
        });
    }

    // PDF file input preview feedback
    const pdfFileInput = document.getElementById('pdfFile');
    const pdfFileInfo = document.getElementById('pdfFileInfo');
    if (pdfFileInput && pdfFileInfo) {
        pdfFileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const sizeInMb = (file.size / (1024 * 1024)).toFixed(2);
                pdfFileInfo.innerHTML = `✓ Selected: <strong>${file.name}</strong> (${sizeInMb} MB)`;
                pdfFileInfo.style.display = 'block';
            }
        });
    }

    // Solution file input preview feedback
    const solutionFileInput = document.getElementById('solutionFile');
    const solutionFileInfo = document.getElementById('solutionFileInfo');
    if (solutionFileInput && solutionFileInfo) {
        solutionFileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const sizeInMb = (file.size / (1024 * 1024)).toFixed(2);
                solutionFileInfo.innerHTML = `✓ Selected: <strong>${file.name}</strong> (${sizeInMb} MB)`;
                solutionFileInfo.style.display = 'block';
            }
        });
    }

    // Character counter for SEO fields
    const metaTitleInput = document.getElementById('metaTitle');
    const metaTitleCount = document.getElementById('metaTitleCount');
    if (metaTitleInput && metaTitleCount) {
        const updateTitleCount = () => {
            const len = metaTitleInput.value.length;
            metaTitleCount.textContent = `${len}/60 chars (Recommended: 50-60)`;
            metaTitleCount.style.color = (len > 60) ? '#dc2626' : '#64748b';
        };
        metaTitleInput.addEventListener('input', updateTitleCount);
        updateTitleCount();
    }

    const metaDescInput = document.getElementById('metaDesc');
    const metaDescCount = document.getElementById('metaDescCount');
    if (metaDescInput && metaDescCount) {
        const updateDescCount = () => {
            const len = metaDescInput.value.length;
            metaDescCount.textContent = `${len}/160 chars (Recommended: 120-160)`;
            metaDescCount.style.color = (len > 160) ? '#dc2626' : '#64748b';
        };
        metaDescInput.addEventListener('input', updateDescCount);
        updateDescCount();
    }

    // Delete confirmation handler
    const deleteLinks = document.querySelectorAll('.confirm-delete');
    deleteLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            const item = link.getAttribute('data-item') || 'this item';
            if (!confirm(`Are you sure you want to permanently delete ${item}? This action cannot be undone.`)) {
                e.preventDefault();
            }
        });
    });
});
