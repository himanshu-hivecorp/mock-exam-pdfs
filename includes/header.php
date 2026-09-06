<?php
/**
 * Mock Exam PDFs - Frontend Header Template
 */
require_once __DIR__ . '/functions.php';

$siteName = get_setting('site_name', 'Mock Exam PDFs');
$siteTagline = get_setting('site_tagline', 'Free High-Quality Mock Exam Papers & Practice Tests in PDF');
$siteDesc = get_setting('site_description', 'Download free competitive mock exam question papers and practice sets with solutions.');

$pageTitle = isset($customTitle) ? $customTitle . ' | ' . $siteName : $siteName . ' - ' . $siteTagline;
$metaDescription = $customDesc ?? $siteDesc;
$metaKeywords = $customKeywords ?? 'mock exam pdf, competitive exam papers, upsc mock test, ssc cgl mock test, sbi po practice set, free pdf download';
$canonicalUrl = $customCanonical ?? (get_base_url() . $_SERVER['REQUEST_URI']);
$ogImage = $customImage ?? url('assets/img/og-cover.png');
$flash = get_flash();
$navCategories = get_categories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    
    <!-- SEO Primary Meta Tags -->
    <meta name="title" content="<?= e($pageTitle) ?>">
    <meta name="description" content="<?= e($metaDescription) ?>">
    <meta name="keywords" content="<?= e($metaKeywords) ?>">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#4f46e5">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="<?= e($ogType ?? 'website') ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:site_name" content="<?= e($siteName) ?>">

    <!-- Twitter Card -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= e($canonicalUrl) ?>">
    <meta property="twitter:title" content="<?= e($pageTitle) ?>">
    <meta property="twitter:description" content="<?= e($metaDescription) ?>">
    <meta property="twitter:image" content="<?= e($ogImage) ?>">

    <?php if (!empty($schemaJson)): ?>
    <!-- Structured Data JSON-LD -->
    <script type="application/ld+json">
        <?= $schemaJson ?>
    </script>
    <?php endif; ?>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>?v=<?= time() ?>">
</head>
<body>

<header class="site-header">
    <div class="container">
        <nav class="navbar">
            <!-- Brand Logo -->
            <a href="<?= url() ?>" class="nav-brand">
                <div class="brand-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                </div>
                <span><?= e($siteName) ?> <span class="brand-badge">PDFs</span></span>
            </a>

            <!-- Center Mixed Menu Links & Dropdown -->
            <ul class="nav-links">
                <li>
                    <a href="<?= url() ?>" class="nav-link <?= empty($_GET['cat']) && empty($_GET['order']) ? 'active' : '' ?>">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
                        <span>Home</span>
                    </a>
                </li>

                <!-- Categories Mega Dropdown -->
                <li class="nav-item-dropdown">
                    <button class="nav-link nav-dropdown-btn" id="categoryDropdownBtn" aria-expanded="false">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                        <span>Exam Categories</span>
                        <svg class="dropdown-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>
                    
                    <div class="mega-dropdown-menu" id="categoryDropdownMenu">
                        <div class="dropdown-header">
                            <span>Browse By Exam Category</span>
                            <a href="<?= url() ?>" style="font-size: 0.75rem; color: var(--primary);">View All</a>
                        </div>
                        <div class="dropdown-grid">
                            <?php foreach ($navCategories as $cat): ?>
                                <a href="<?= category_url($cat['slug']) ?>" class="dropdown-item">
                                    <span class="cat-dot" style="background-color: <?= e($cat['color']) ?>;"></span>
                                    <div class="cat-details">
                                        <div class="cat-name"><?= e($cat['name']) ?></div>
                                        <div class="cat-meta"><?= $cat['post_count'] ?> mock papers</div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </li>

                <li>
                    <a href="<?= url('upcoming-exams.php') ?>" class="nav-link <?= (basename($_SERVER['SCRIPT_NAME']) === 'upcoming-exams.php') ? 'active' : '' ?>">
                        <span style="display: inline-block; animation: pulse 2s infinite; font-size: 0.9rem;">📢</span>
                        <span>Upcoming Exams</span>
                        <span style="font-size: 0.65rem; background: #fee2e2; color: #dc2626; font-weight: 800; padding: 1px 6px; border-radius: 9999px;">Alerts</span>
                    </a>
                </li>

                <li>
                    <a href="<?= url('?order=downloads') ?>" class="nav-link <?= (($_GET['order'] ?? '') === 'downloads') ? 'active' : '' ?>">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                        <span>Top Downloaded</span>
                    </a>
                </li>
            </ul>

            <!-- Right Actions & Quick Search -->
            <div class="nav-actions">
                <form action="<?= url() ?>" method="GET" class="nav-quick-search">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" name="q" placeholder="Quick search exams..." value="<?= e($_GET['q'] ?? '') ?>">
                </form>

                <button id="menuToggle" class="menu-toggle" aria-label="Toggle Menu">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
            </div>
        </nav>
    </div>
    
    <!-- Mobile Drawer Overlay & Menu -->
    <div id="mobileDrawerOverlay" class="drawer-overlay"></div>
    <div id="mobileMenu" class="nav-mobile-drawer">
        <div class="drawer-header">
            <div class="nav-brand" style="font-size: 1.15rem;">
                <div class="brand-icon" style="width: 32px; height: 32px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path></svg>
                </div>
                <span><?= e($siteName) ?></span>
            </div>
            <button id="closeDrawerBtn" class="close-drawer-btn" aria-label="Close menu">&times;</button>
        </div>

        <div class="drawer-body">
            <form action="<?= url() ?>" method="GET" class="drawer-search">
                <input type="text" name="q" placeholder="Search mock tests..." value="<?= e($_GET['q'] ?? '') ?>">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
            </form>

            <div class="drawer-section-title">Navigation</div>
            <ul class="drawer-links">
                <li><a href="<?= url() ?>" class="drawer-link">🏠 Home</a></li>
                <li><a href="<?= url('upcoming-exams.php') ?>" class="drawer-link" style="color: #dc2626; font-weight: 700;">📢 Upcoming Exams & Alerts</a></li>
                <li><a href="<?= url('?order=downloads') ?>" class="drawer-link">🔥 Most Downloaded</a></li>
                <li><a href="<?= url('?order=views') ?>" class="drawer-link">👁️ Most Viewed</a></li>
            </ul>

            <div class="drawer-section-title">Exam Categories</div>
            <div class="drawer-categories">
                <?php foreach ($navCategories as $cat): ?>
                    <a href="<?= category_url($cat['slug']) ?>" class="drawer-cat-pill">
                        <span class="cat-dot" style="background-color: <?= e($cat['color']) ?>;"></span>
                        <span><?= e($cat['name']) ?></span>
                        <span class="count"><?= $cat['post_count'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</header>

<?php if ($flash): ?>
<div class="container" style="margin-top: 20px;">
    <div class="alert alert-<?= e($flash['type']) ?> alert-auto-dismiss">
        <?= e($flash['message']) ?>
    </div>
</div>
<?php endif; ?>

<main>
