<?php
/**
 * Mock Exam PDFs - Single Exam Notification Post Page (exam.php)
 *
 * @package   MockExamPDFs
 * @author    Himanshu Ranjan Sahu <https://github.com/himanshu-hivecorp>
 * @copyright 2026 Himanshu Ranjan Sahu
 * @license   MIT License
 * @link      https://github.com/himanshu-hivecorp/mock-exam-pdfs
 */

require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');

if (empty($slug)) {
    header("Location: " . url('upcoming-exams.php'));
    exit;
}

$notif = get_exam_notification_by_slug($slug, true);

if (!$notif) {
    http_response_code(404);
    $customTitle = "Exam Notification Not Found - 404";
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="container" style="text-align: center; padding: 100px 20px;">
        <div style="font-size: 4rem; margin-bottom: 16px;">📢</div>
        <h2>404 - Exam Notification Not Found</h2>
        <p style="color: var(--text-muted); margin: 12px 0 24px;">The exam notification alert you are looking for might have expired or been moved.</p>
        <a href="<?= url('upcoming-exams.php') ?>" class="btn btn-primary">Browse All Upcoming Exams</a>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Increment view count
increment_notification_views((int)$notif['id']);

// SEO Meta Variables
$customTitle = !empty($notif['meta_title']) ? $notif['meta_title'] : $notif['title'];
$customDesc = !empty($notif['meta_description']) ? $notif['meta_description'] : $notif['details'];
$customKeywords = !empty($notif['meta_keywords']) ? $notif['meta_keywords'] : ($notif['exam_name'] . ', exam date, notification pdf, apply online, vacancies 2026');
$customCanonical = exam_url($notif['slug']);

// Schema.org Structured Data
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'NewsArticle',
    'headline' => $notif['title'],
    'description' => $notif['details'],
    'datePublished' => date('c', strtotime($notif['created_at'])),
    'url' => exam_url($notif['slug']),
    'publisher' => [
        '@type' => 'Organization',
        'name' => get_setting('site_name', 'Mock Exam PDFs'),
        'url' => url()
    ]
];
$schemaJson = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

// Fetch Related Mock Tests for this exam category
$relatedMocks = [];
if (!empty($notif['category_id'])) {
    $relatedMocks = get_posts([
        'category_id' => $notif['category_id'],
        'limit'       => 4,
        'order_by'    => 'downloads DESC'
    ])['items'];
}

// Fetch other active upcoming notifications
$otherNotifs = get_exam_notifications(4);
$otherNotifs = array_filter($otherNotifs, fn($n) => $n['id'] != $notif['id']);

$tagClass = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $notif['badge_tag']));

require_once __DIR__ . '/includes/header.php';
?>

<!-- Post Header Section -->
<section class="post-header-section">
    <div class="container">
        <!-- Breadcrumbs -->
        <nav class="breadcrumb" aria-label="breadcrumb">
            <a href="<?= url() ?>">Home</a>
            <span>/</span>
            <a href="<?= url('upcoming-exams.php') ?>">Upcoming Exams</a>
            <span>/</span>
            <span style="color: var(--text-dark); font-weight: 600;"><?= e($notif['title']) ?></span>
        </nav>

        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; flex-wrap: wrap;">
            <span class="notif-tag tag-<?= $tagClass ?>" style="font-size: 0.8rem; padding: 4px 12px;">
                <?= e($notif['badge_tag']) ?>
            </span>

            <?php if (!empty($notif['category_name'])): ?>
                <span class="exam-badge" style="background-color: <?= e($notif['category_color']) ?>18; color: <?= e($notif['category_color']) ?>;">
                    <?= e($notif['category_name']) ?>
                </span>
            <?php endif; ?>

            <span style="font-size: 0.85rem; color: var(--text-muted); margin-left: auto;">
                Published on <?= format_date($notif['created_at']) ?>
            </span>
        </div>

        <h1 style="margin-bottom: 10px;"><?= e($notif['title']) ?></h1>
        <div style="font-size: 1.15rem; font-weight: 700; color: var(--primary); margin-bottom: 12px;">
            <?= e($notif['exam_name']) ?>
        </div>

        <p style="font-size: 1.15rem; color: var(--text-muted); max-width: 1200px; line-height: 1.6;">
            <?= e($notif['details']) ?>
        </p>
    </div>
</section>

<!-- Main Post Body & Sidebar -->
<div class="container">
    <div class="post-layout">
        <!-- Main Content Column -->
        <div class="post-main-col">
            <!-- Key Dates Highlight Strip -->
            <div style="background: #ffffff; border: 1px solid var(--border-card); border-radius: var(--radius-lg); padding: 20px 24px; margin-bottom: 28px; box-shadow: var(--shadow-sm); display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-light); display: block;">📅 Exam Date</span>
                    <strong style="font-size: 1.1rem; color: var(--text-dark);"><?= e($notif['exam_date'] ?: 'To Be Announced') ?></strong>
                </div>
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-light); display: block;">⏳ Application Deadline</span>
                    <strong style="font-size: 1.1rem; color: #dc2626;"><?= e($notif['application_deadline'] ?: 'To Be Announced') ?></strong>
                </div>
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-light); display: block;">👥 Total Vacancies</span>
                    <strong style="font-size: 1.1rem; color: #059669;"><?= e($notif['total_vacancies'] ?: 'As per advertisement') ?></strong>
                </div>
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-light); display: block;">🏷️ Exam Category</span>
                    <strong style="font-size: 1.1rem; color: var(--primary);"><?= e($notif['category_name'] ?: 'Competitive Exam') ?></strong>
                </div>
            </div>

            <!-- Full Article Content -->
            <article class="post-content-card">
                <div class="post-content-body">
                    <?php if (!empty($notif['content'])): ?>
                        <?= $notif['content'] ?>
                    <?php else: ?>
                        <h3>Notification Details</h3>
                        <p><?= nl2br(e($notif['details'])) ?></p>
                        <p>Aspirants are advised to read the complete notification brochure for specific details regarding educational criteria, syllabus breakdown, reservation rules, and age criteria before applying online.</p>
                    <?php endif; ?>
                </div>

                <!-- Official Application Box -->
                <div style="margin-top: 36px; background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #eff6ff 100%); border: 2px solid #86efac; border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-md);">
                    <div style="display: flex; align-items: flex-start; gap: 16px;">
                        <div style="font-size: 2.2rem;">🚀</div>
                        <div style="flex: 1;">
                            <h3 style="color: #166534; font-size: 1.35rem; margin-bottom: 6px;">Official Application & Registration Portal</h3>
                            <p style="color: #15803d; font-size: 0.92rem; line-height: 1.5; margin-bottom: 18px;">
                                You can submit your application, complete OTR registration, and read the primary recruitment brochure through the official portal link below:
                            </p>

                            <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
                                <?php if (!empty($notif['official_link'])): ?>
                                    <a href="<?= e($notif['official_link']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-emerald btn-lg" style="font-weight: 700;">
                                        <span>Apply Online on Official Website ↗</span>
                                    </a>
                                <?php endif; ?>

                                <?php if (!empty($notif['notification_pdf'])): ?>
                                    <a href="<?= url($notif['notification_pdf']) ?>" target="_blank" class="btn btn-secondary btn-lg" style="border-color: #86efac; background: #fff;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                        <span>Download Official Notice PDF</span>
                                    </a>
                                <?php endif; ?>
                            </div>

                            <div style="font-size: 0.78rem; color: #166534; margin-top: 14px;">
                                ⚠️ Note: Please verify all dates, eligibility conditions, and guidelines on the official portal before making payments.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Share Bar -->
                <div style="margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border-subtle);">
                    <span style="font-weight: 700; font-size: 0.9rem; color: var(--text-dark); display: block; margin-bottom: 8px;">
                        Share this Exam Alert with fellow students:
                    </span>
                    <div class="share-bar">
                        <a href="https://api.whatsapp.com/send?text=<?= urlencode($notif['title'] . ' - Read details & apply: ' . exam_url($notif['slug'])) ?>" target="_blank" rel="noopener" class="share-btn share-wa" title="Share on WhatsApp">
                            📱
                        </a>
                        <a href="https://t.me/share/url?url=<?= urlencode(exam_url($notif['slug'])) ?>&text=<?= urlencode($notif['title']) ?>" target="_blank" rel="noopener" class="share-btn share-tg" title="Share on Telegram">
                            ✈️
                        </a>
                        <a href="https://twitter.com/intent/tweet?text=<?= urlencode($notif['title'] . ' ' . exam_url($notif['slug'])) ?>" target="_blank" rel="noopener" class="share-btn share-tw" title="Share on Twitter / X">
                            🐦
                        </a>
                        <button class="btn btn-secondary btn-sm btn-copy-link" data-url="<?= exam_url($notif['slug']) ?>" title="Copy link to clipboard">
                            <span>📋 Copy Link</span>
                        </button>
                    </div>
                </div>
            </article>
        </div>

        <!-- Sidebar Column -->
        <aside class="post-sidebar-col">
            <!-- Apply Shortcut Card -->
            <div class="sidebar-card">
                <h4 class="sidebar-title">Important Summary</h4>
                <ul class="exam-specs-list">
                    <li>
                        <span class="spec-lbl">Status:</span>
                        <span class="notif-tag tag-<?= $tagClass ?>"><?= e($notif['badge_tag']) ?></span>
                    </li>
                    <li>
                        <span class="spec-lbl">Exam Date:</span>
                        <span class="spec-val"><?= e($notif['exam_date'] ?: 'TBA') ?></span>
                    </li>
                    <li>
                        <span class="spec-lbl">Last Date:</span>
                        <span class="spec-val" style="color: #dc2626;"><?= e($notif['application_deadline'] ?: 'TBA') ?></span>
                    </li>
                    <li>
                        <span class="spec-lbl">Vacancies:</span>
                        <span class="spec-val" style="color: #059669;"><?= e($notif['total_vacancies'] ?: 'N/A') ?></span>
                    </li>
                    <li>
                        <span class="spec-lbl">Category:</span>
                        <span class="spec-val"><?= e($notif['category_name'] ?: 'General') ?></span>
                    </li>
                </ul>

                <?php if (!empty($notif['official_link'])): ?>
                    <a href="<?= e($notif['official_link']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-emerald" style="width: 100%; margin-top: 10px;">
                        <span>Apply on Official Portal ↗</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Free Practice Mock Papers for this Exam -->
            <?php if (!empty($relatedMocks)): ?>
                <div class="sidebar-card">
                    <h4 class="sidebar-title">Practice Tests for this Exam</h4>
                    <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 12px;">
                        Boost your preparation with free downloadable practice papers:
                    </p>
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <?php foreach ($relatedMocks as $rel): ?>
                            <div style="padding-bottom: 12px; border-bottom: 1px solid var(--border-subtle);">
                                <h5 style="font-size: 0.92rem; margin-bottom: 4px;">
                                    <a href="<?= post_url($rel['slug']) ?>"><?= e($rel['title']) ?></a>
                                </h5>
                                <div style="display: flex; justify-content: space-between; font-size: 0.78rem; color: var(--text-muted);">
                                    <span><?= e($rel['total_questions']) ?> Qs • <?= e($rel['difficulty']) ?></span>
                                    <span style="color: #059669; font-weight: 700;">PDF Download</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- More Upcoming Exams -->
            <?php if (!empty($otherNotifs)): ?>
                <div class="sidebar-card">
                    <h4 class="sidebar-title">More Upcoming Exams</h4>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php foreach ($otherNotifs as $on): ?>
                            <div style="padding-bottom: 10px; border-bottom: 1px solid var(--border-subtle);">
                                <h5 style="font-size: 0.9rem; margin-bottom: 2px;">
                                    <a href="<?= exam_url($on['slug']) ?>"><?= e($on['title']) ?></a>
                                </h5>
                                <div style="font-size: 0.76rem; color: var(--text-muted);">
                                    Exam: <strong><?= e($on['exam_date'] ?: 'TBA') ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?= url('upcoming-exams.php') ?>" class="btn btn-secondary btn-sm" style="width: 100%; margin-top: 12px;">
                        View All Upcoming Exams →
                    </a>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
