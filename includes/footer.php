<?php
/**
 * Mock Exam PDFs - Frontend Footer Template
 */
require_once __DIR__ . '/functions.php';

$siteName = get_setting('site_name', 'Mock Exam PDFs');
$siteDesc = get_setting('site_description', 'Free competitive mock exam question papers and practice sets with solutions.');
$footerText = get_setting('footer_text', '© ' . date('Y') . ' Mock Exam PDFs. All rights reserved.');
$categories = get_categories();
?>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="nav-brand" style="margin-bottom: 16px; color: #fff;">
                    <div class="brand-icon" style="box-shadow: none;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                    </div>
                    <span><?= e($siteName) ?></span>
                </div>
                <p><?= e($siteDesc) ?></p>
                <div style="margin-top: 16px;">
                    <span style="font-size: 0.8rem; color: #64748b;">Ready to host on cPanel Apache. Powered by SQLite & PHP.</span>
                </div>
            </div>

            <div class="footer-col">
                <h4>Exam Categories</h4>
                <ul class="footer-links">
                    <?php 
                    $count = 0;
                    foreach ($categories as $cat): 
                        if ($count++ >= 5) break;
                    ?>
                        <li><a href="<?= category_url($cat['slug']) ?>"><?= e($cat['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="<?= url() ?>">Latest Mock Tests</a></li>
                    <li><a href="<?= url('?order=downloads') ?>">Most Downloaded</a></li>
                    <li><a href="<?= url('?order=views') ?>">Most Viewed Papers</a></li>
                    <li><a href="<?= url('#categories') ?>">All Exam Boards</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Disclaimer</h4>
                <p style="font-size: 0.825rem; color: #64748b;">
                    All mock exam papers and study resources provided on this portal are for self-evaluation, educational practice, and mock testing purposes only. All registered trademarks and logos belong to their respective exam boards.
                </p>
            </div>
        </div>

        <div class="footer-bottom">
            <div><?= e($footerText) ?></div>
            <div style="font-size: 0.85rem; color: #94a3b8; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <span>Developed with ❤️ by</span>
                <a href="https://github.com/himanshu-hivecorp" target="_blank" rel="noopener noreferrer" style="color: #60a5fa; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle;">
                        <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                    </svg>
                    Himanshu Ranjan Sahu
                </a>
            </div>
        </div>
    </div>
</footer>

<script src="<?= url('assets/js/main.js') ?>?v=<?= time() ?>"></script>
</body>
</html>
