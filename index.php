<?php
/**
 * Mock Exam PDFs - Homepage (index.php)
 *
 * @package   MockExamPDFs
 * @author    Himanshu Ranjan Sahu <https://github.com/himanshu-hivecorp>
 * @copyright 2026 Himanshu Ranjan Sahu
 * @license   MIT License
 * @link      https://github.com/himanshu-hivecorp/mock-exam-pdfs
 */

require_once __DIR__ . '/includes/functions.php';

$currentCat = $_GET['cat'] ?? null;
$searchQuery = trim($_GET['q'] ?? '');
$sortBy = $_GET['order'] ?? 'created_at DESC';
if ($sortBy === 'downloads') $orderParam = 'downloads DESC';
elseif ($sortBy === 'views') $orderParam = 'views DESC';
else $orderParam = 'created_at DESC';

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = (int)get_setting('items_per_page', '12');
$offset = ($page - 1) * $limit;

$postsData = get_posts([
    'category_slug' => $currentCat,
    'search'        => $searchQuery,
    'order_by'      => $orderParam,
    'limit'         => $limit,
    'offset'        => $offset,
    'status'        => 'published'
]);

$posts = $postsData['items'];
$totalPosts = $postsData['total'];
$totalPages = $postsData['total_pages'];
$categories = get_categories();
$adminStats = get_admin_stats();
$notifications = get_exam_notifications(3);

$siteName = get_setting('site_name', 'Mock Exam PDFs');
$siteTagline = get_setting('site_tagline', 'Free High-Quality Mock Exam Papers & Practice Tests in PDF');
$customTitle = $currentCat ? "Mock Exams - " . ucwords(str_replace('-', ' ', $currentCat)) : $siteName . ' - ' . $siteTagline;

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <div class="hero-pill">
                <span class="dot"></span>
                <span>Verified 2026 Competitive Exam Mock Papers</span>
            </div>
            <h1 class="hero-title">Download Free Mock Exam PDFs & Full Practice Sets</h1>
            <p class="hero-desc">
                Instant access to real exam-pattern test papers, previous year solved question papers, and full-length answer keys with detailed explanations.
            </p>

            <form action="<?= url() ?>" method="GET" class="hero-search-box">
                <?php if ($currentCat): ?>
                    <input type="hidden" name="cat" value="<?= e($currentCat) ?>">
                <?php endif; ?>
                <div class="search-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <input 
                    type="text" 
                    id="liveSearchInput" 
                    name="q" 
                    placeholder="Search by exam name, paper, subject, or year (e.g. UPSC, SSC CGL, Banking)..." 
                    value="<?= e($searchQuery) ?>"
                    autocomplete="off"
                >
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
            </form>

            <div class="hero-stats">
                <div class="hero-stat-item">
                    <span class="stat-num"><?= number_format($adminStats['published_posts']) ?>+</span>
                    <span class="stat-lbl">Mock Papers</span>
                </div>
                <div class="hero-stat-item">
                    <span class="stat-num"><?= number_format($adminStats['total_downloads']) ?>+</span>
                    <span class="stat-lbl">PDF Downloads</span>
                </div>
                <div class="hero-stat-item">
                    <span class="stat-num"><?= number_format($adminStats['total_categories']) ?></span>
                    <span class="stat-lbl">Exam Boards</span>
                </div>
                <div class="hero-stat-item">
                    <span class="stat-num">100%</span>
                    <span class="stat-lbl">Free Access</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Category Filter Pills Bar -->
<section id="categories" class="category-filter-section">
    <div class="container">
        <div class="category-scroll">
            <a href="<?= url() ?>" class="category-pill <?= empty($currentCat) ? 'active' : '' ?>">
                <span>All Exams</span>
                <span class="badge-count"><?= $adminStats['published_posts'] ?></span>
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= url('?cat=' . urlencode($cat['slug'])) ?>" class="category-pill <?= ($currentCat === $cat['slug']) ? 'active' : '' ?>">
                    <span><?= e($cat['name']) ?></span>
                    <span class="badge-count"><?= $cat['post_count'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Upcoming Exam Notifications Section -->
<?php if (!empty($notifications) && empty($searchQuery) && empty($currentCat)): ?>
<section class="container notif-section">
    <div class="notif-banner">
        <div class="notif-header">
            <div class="notif-header-left">
                <div class="notif-icon-pulse">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                </div>
                <div>
                    <h3 style="font-size: 1.25rem; color: var(--text-dark); line-height: 1.2;">Upcoming Competitive Exams & Key Deadlines</h3>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">Latest recruitment schedules, registration alerts, and official notices</p>
                </div>
            </div>
            <a href="<?= url('upcoming-exams.php') ?>" class="btn btn-secondary btn-sm" style="font-weight: 700;">
                <span>View All Alerts (<?= $adminStats['total_notifications'] ?>) →</span>
            </a>
        </div>

        <div class="notif-grid">
            <?php foreach ($notifications as $notif): 
                $tagClass = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $notif['badge_tag']));
            ?>
                <div class="notif-card">
                    <div class="notif-card-top">
                        <span class="notif-tag tag-<?= $tagClass ?>">
                            <?= e($notif['badge_tag']) ?>
                        </span>
                        <?php if (!empty($notif['category_name'])): ?>
                            <span style="font-size: 0.75rem; font-weight: 700; color: <?= e($notif['category_color']) ?>;">
                                <?= e($notif['category_name']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <h4 class="notif-card-title">
                        <a href="<?= exam_url($notif['slug']) ?>"><?= e($notif['title']) ?></a>
                    </h4>
                    <div class="notif-exam-name"><?= e($notif['exam_name']) ?></div>

                    <div class="notif-pills">
                        <?php if (!empty($notif['exam_date'])): ?>
                            <div class="notif-pill-item">
                                <span>📅 Exam:</span>
                                <strong><?= e($notif['exam_date']) ?></strong>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($notif['application_deadline'])): ?>
                            <div class="notif-pill-item">
                                <span>⏳ Deadline:</span>
                                <strong><?= e($notif['application_deadline']) ?></strong>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($notif['total_vacancies'])): ?>
                            <div class="notif-pill-item">
                                <span>👥 Posts:</span>
                                <strong><?= e($notif['total_vacancies']) ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>

                    <p class="notif-desc"><?= e($notif['details']) ?></p>

                    <div class="notif-card-actions">
                        <a href="<?= exam_url($notif['slug']) ?>" class="btn btn-primary btn-sm" style="width: 100%; font-size: 0.85rem;">
                            <span>Read Post & Apply Online →</span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Main Listing Section -->
<section class="container" style="padding-top: 10px;">
    <div class="section-header">
        <div>
            <div class="sub-title">Practice Test Series</div>
            <h2>
                <?php if (!empty($searchQuery)): ?>
                    Search Results for "<?= e($searchQuery) ?>"
                <?php elseif (!empty($currentCat)): ?>
                    <?= e(ucwords(str_replace('-', ' ', $currentCat))) ?> Mock Exams
                <?php else: ?>
                    All Available Mock Exam Papers
                <?php endif; ?>
            </h2>
        </div>

        <!-- Sort Filter -->
        <div style="display: flex; align-items: center; gap: 8px;">
            <label for="sortOrder" style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Sort by:</label>
            <select id="sortOrder" onchange="location = this.value;" style="padding: 8px 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle); font-family: inherit; font-size: 0.875rem; background: #fff;">
                <option value="<?= url('?' . http_build_query(array_merge($_GET, ['order' => 'latest']))) ?>" <?= ($sortBy === 'created_at DESC' || $sortBy === 'latest') ? 'selected' : '' ?>>Latest First</option>
                <option value="<?= url('?' . http_build_query(array_merge($_GET, ['order' => 'downloads']))) ?>" <?= ($sortBy === 'downloads') ? 'selected' : '' ?>>Most Downloaded</option>
                <option value="<?= url('?' . http_build_query(array_merge($_GET, ['order' => 'views']))) ?>" <?= ($sortBy === 'views') ? 'selected' : '' ?>>Most Viewed</option>
            </select>
        </div>
    </div>

    <!-- Live search zero-results placeholder -->
    <div id="noResultsMsg" style="display: none; text-align: center; padding: 60px 20px;">
        <div style="font-size: 3rem; margin-bottom: 12px;">📑</div>
        <h3>No matching mock exams found</h3>
        <p style="color: var(--text-muted); margin-top: 6px;">Try searching for a different exam name, subject, or year.</p>
    </div>

    <?php if (empty($posts)): ?>
        <div style="text-align: center; padding: 60px 20px; background: #ffffff; border-radius: var(--radius-lg); border: 1px dashed var(--border-subtle); margin: 20px 0 40px;">
            <div style="font-size: 3.5rem; margin-bottom: 12px;">📚</div>
            <h3>No mock papers found in this section</h3>
            <p style="color: var(--text-muted); margin: 8px 0 20px;">We are currently adding papers for this exam category. Check back soon!</p>
            <a href="<?= url() ?>" class="btn btn-primary">View All Mock Exams</a>
        </div>
    <?php else: ?>
        <div class="exam-grid">
            <?php foreach ($posts as $post): ?>
                <article class="exam-card" 
                    data-title="<?= e($post['title']) ?>" 
                    data-category="<?= e($post['category_name']) ?>" 
                    data-excerpt="<?= e($post['excerpt']) ?>"
                >
                    <div class="card-top">
                        <span class="exam-badge" style="background-color: <?= e($post['category_color']) ?>18; color: <?= e($post['category_color']) ?>;">
                            <?= e($post['category_name'] ?: 'Mock Test') ?>
                        </span>
                        <span class="difficulty-badge difficulty-<?= e($post['difficulty']) ?>">
                            <?= e($post['difficulty']) ?>
                        </span>
                    </div>

                    <div class="card-body">
                        <h3 class="card-title">
                            <a href="<?= post_url($post['slug']) ?>"><?= e($post['title']) ?></a>
                        </h3>
                        <p class="card-excerpt"><?= e($post['excerpt']) ?></p>

                        <div class="exam-meta-grid">
                            <div class="meta-item">
                                <span class="val"><?= e($post['duration_mins']) ?> M</span>
                                <span class="lbl">Duration</span>
                            </div>
                            <div class="meta-item">
                                <span class="val"><?= e($post['total_questions']) ?> Qs</span>
                                <span class="lbl">Questions</span>
                            </div>
                            <div class="meta-item">
                                <span class="val"><?= format_bytes((int)$post['pdf_size']) ?></span>
                                <span class="lbl">PDF Size</span>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="download-stat">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            <span><?= number_format($post['downloads_count']) ?> downloads</span>
                        </div>

                        <div class="card-actions">
                            <a href="<?= post_url($post['slug']) ?>" class="btn btn-secondary btn-sm" title="View details and embedded preview">
                                Preview
                            </a>
                            <a href="<?= download_url($post['slug']) ?>" class="btn btn-emerald btn-sm" title="Direct download PDF">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                <span>PDF</span>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav class="pagination" aria-label="Page navigation">
                <?php if ($page > 1): ?>
                    <a href="<?= url('?' . http_build_query(array_merge($_GET, ['page' => $page - 1]))) ?>" class="page-link">← Prev</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?= url('?' . http_build_query(array_merge($_GET, ['page' => $i]))) ?>" class="page-link <?= ($i === $page) ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="<?= url('?' . http_build_query(array_merge($_GET, ['page' => $page + 1]))) ?>" class="page-link">Next →</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>

    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
