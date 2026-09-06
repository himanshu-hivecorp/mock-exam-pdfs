<?php
/**
 * Mock Exam PDFs - Single Post / PDF Download Page (post.php)
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
    header("Location: " . url());
    exit;
}

$post = get_post_by_slug($slug, true);

if (!$post) {
    http_response_code(404);
    $customTitle = "Mock Exam Not Found - 404";
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="container" style="text-align: center; padding: 100px 20px;">
        <div style="font-size: 4rem; margin-bottom: 16px;">🔍</div>
        <h2>404 - Mock Exam PDF Not Found</h2>
        <p style="color: var(--text-muted); margin: 12px 0 24px;">The mock exam you are looking for might have been moved, renamed, or unpublished.</p>
        <a href="<?= url() ?>" class="btn btn-primary">Browse All Mock Exams</a>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Increment view count
increment_views($post['id']);

// SEO Meta Variables
$customTitle = !empty($post['meta_title']) ? $post['meta_title'] : $post['title'];
$customDesc = !empty($post['meta_description']) ? $post['meta_description'] : $post['excerpt'];
$customKeywords = !empty($post['meta_keywords']) ? $post['meta_keywords'] : ($post['title'] . ', mock exam pdf, practice test, answer key download');
$customCanonical = post_url($post['slug']);

// Schema.org Structured Data
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'LearningResource',
    'name' => $post['title'],
    'description' => $post['excerpt'],
    'educationalLevel' => $post['difficulty'] . ' Level',
    'learningResourceType' => 'Practice Test / Mock Examination',
    'datePublished' => date('c', strtotime($post['created_at'])),
    'url' => post_url($post['slug']),
    'provider' => [
        '@type' => 'Organization',
        'name' => get_setting('site_name', 'Mock Exam PDFs'),
        'url' => url()
    ]
];
$schemaJson = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

// Fetch Related Posts in the same category
$relatedPosts = [];
if (!empty($post['category_id'])) {
    $relatedData = get_posts([
        'category_id' => $post['category_id'],
        'limit'       => 4,
        'order_by'    => 'downloads DESC'
    ]);
    // Filter out current post
    $relatedPosts = array_filter($relatedData['items'], fn($p) => $p['id'] != $post['id']);
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Post Header Section -->
<section class="post-header-section">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb" aria-label="breadcrumb">
            <a href="<?= url() ?>">Home</a>
            <span>/</span>
            <?php if (!empty($post['category_name'])): ?>
                <a href="<?= category_url($post['category_slug']) ?>"><?= e($post['category_name']) ?></a>
                <span>/</span>
            <?php endif; ?>
            <span style="color: var(--text-dark); font-weight: 600;"><?= e($post['title']) ?></span>
        </nav>

        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; flex-wrap: wrap;">
            <?php if (!empty($post['category_name'])): ?>
                <span class="exam-badge" style="background-color: <?= e($post['category_color']) ?>18; color: <?= e($post['category_color']) ?>;">
                    <?= e($post['category_name']) ?>
                </span>
            <?php endif; ?>

            <span class="difficulty-badge difficulty-<?= e($post['difficulty']) ?>">
                <?= e($post['difficulty']) ?> Level
            </span>

            <?php if (!empty($post['exam_year'])): ?>
                <span style="font-size: 0.8rem; font-weight: 700; background: #e2e8f0; color: #334155; padding: 3px 8px; border-radius: var(--radius-sm);">
                    Year <?= e($post['exam_year']) ?>
                </span>
            <?php endif; ?>

            <span style="font-size: 0.85rem; color: var(--text-muted); margin-left: auto;">
                Published on <?= format_date($post['created_at']) ?>
            </span>
        </div>

        <h1 style="margin-bottom: 12px;"><?= e($post['title']) ?></h1>

        <p style="font-size: 1.15rem; color: var(--text-muted); max-width: 1200px; line-height: 1.6;">
            <?= e($post['excerpt']) ?>
        </p>
    </div>
</section>

<!-- Main Post Body & Sidebar -->
<div class="container">
    <div class="post-layout">
        <!-- Main Content Column -->
        <div class="post-main-col">
            <!-- Content & Instructions -->
            <article class="post-content-card">
                <div class="post-content-body">
                    <?= $post['content'] ?>
                </div>

                <!-- Social Share -->
                <div style="margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border-subtle);">
                    <span style="font-weight: 700; font-size: 0.9rem; color: var(--text-dark); display: block; margin-bottom: 8px;">
                        Share this Mock Test with friends & study groups:
                    </span>
                    <div class="share-bar">
                        <a href="https://api.whatsapp.com/send?text=<?= urlencode($post['title'] . ' - Free PDF Download: ' . post_url($post['slug'])) ?>" target="_blank" rel="noopener" class="share-btn share-wa" title="Share on WhatsApp">
                            📱
                        </a>
                        <a href="https://t.me/share/url?url=<?= urlencode(post_url($post['slug'])) ?>&text=<?= urlencode($post['title']) ?>" target="_blank" rel="noopener" class="share-btn share-tg" title="Share on Telegram">
                            ✈️
                        </a>
                        <a href="https://twitter.com/intent/tweet?text=<?= urlencode($post['title'] . ' ' . post_url($post['slug'])) ?>" target="_blank" rel="noopener" class="share-btn share-tw" title="Share on Twitter / X">
                            🐦
                        </a>
                        <button class="btn btn-secondary btn-sm btn-copy-link" data-url="<?= post_url($post['slug']) ?>" title="Copy link to clipboard">
                            <span>📋 Copy Link</span>
                        </button>
                    </div>
                </div>
            </article>

            <!-- Embedded PDF Previewer -->
            <section class="pdf-viewer-card" id="pdf-viewer">
                <div class="pdf-viewer-header">
                    <h3>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                        <span>Interactive PDF Preview: <?= e($post['pdf_name'] ?: 'Mock Test Paper') ?></span>
                    </h3>

                    <div style="display: flex; gap: 8px;">
                        <a href="<?= url($post['pdf_file']) ?>" target="_blank" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.15); color: #fff; border: none;">
                            Full Screen ⛶
                        </a>
                        <a href="<?= download_url($post['slug']) ?>" class="btn btn-emerald btn-sm">
                            Download ↓
                        </a>
                    </div>
                </div>

                <div class="pdf-embed-wrapper">
                    <iframe 
                        src="<?= url($post['pdf_file']) ?>#toolbar=1&navpanes=0" 
                        title="<?= e($post['title']) ?>"
                        loading="lazy"
                    >
                        <div style="padding: 40px; text-align: center;">
                            <p>Your browser does not support inline PDF viewing.</p>
                            <a href="<?= download_url($post['slug']) ?>" class="btn btn-primary" style="margin-top: 10px;">Download PDF directly</a>
                        </div>
                    </iframe>
                </div>
            </section>
        </div>

        <!-- Sticky Sidebar Column -->
        <aside class="post-sidebar-col">
            <!-- Primary Download Card -->
            <div class="sidebar-card">
                <div class="download-box">
                    <div style="font-size: 2rem; margin-bottom: 6px;">📥</div>
                    <h3 style="font-size: 1.25rem; color: #166534; margin-bottom: 4px;">Free PDF Download</h3>
                    <p style="font-size: 0.85rem; color: #15803d; margin-bottom: 12px;">Instant download without registration</p>

                    <a href="<?= download_url($post['slug']) ?>" class="btn btn-emerald btn-download btn-lg">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span>Download Paper PDF</span>
                    </a>

                    <div style="font-size: 0.8rem; color: #166534; margin-top: 10px;">
                        File Size: <strong><?= format_bytes((int)$post['pdf_size']) ?></strong> • <?= number_format($post['downloads_count']) ?> downloads
                    </div>

                    <?php if (!empty($post['solution_file'])): ?>
                        <div style="margin-top: 16px; padding-top: 16px; border-top: 1px dashed #bbf7d0;">
                            <a href="<?= download_url($post['slug'], 'solution') ?>" class="btn btn-secondary btn-sm" style="width: 100%;">
                                <span>Download Answer Key PDF</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Exam Specifications -->
                <h4 class="sidebar-title">Mock Test Overview</h4>
                <ul class="exam-specs-list">
                    <li>
                        <span class="spec-lbl">Category:</span>
                        <span class="spec-val"><?= e($post['category_name'] ?: 'General') ?></span>
                    </li>
                    <li>
                        <span class="spec-lbl">Target Year:</span>
                        <span class="spec-val"><?= e($post['exam_year'] ?: '2026') ?></span>
                    </li>
                    <li>
                        <span class="spec-lbl">Time Limit:</span>
                        <span class="spec-val"><?= e($post['duration_mins']) ?> Minutes</span>
                    </li>
                    <li>
                        <span class="spec-lbl">Total Questions:</span>
                        <span class="spec-val"><?= e($post['total_questions']) ?> MCQs</span>
                    </li>
                    <li>
                        <span class="spec-lbl">Maximum Marks:</span>
                        <span class="spec-val"><?= e($post['total_marks']) ?> Marks</span>
                    </li>
                    <li>
                        <span class="spec-lbl">Negative Marking:</span>
                        <span class="spec-val" style="color: #dc2626;"><?= e($post['negative_marking']) ?></span>
                    </li>
                    <li>
                        <span class="spec-lbl">Difficulty:</span>
                        <span class="spec-val"><?= e($post['difficulty']) ?></span>
                    </li>
                    <li>
                        <span class="spec-lbl">Format:</span>
                        <span class="spec-val">Printable PDF</span>
                    </li>
                </ul>

                <a href="#pdf-viewer" class="btn btn-outline-primary" style="width: 100%;">
                    <span>Scroll to Online Preview</span>
                </a>
            </div>

            <!-- Related Mock Exams in this Category -->
            <?php if (!empty($relatedPosts)): ?>
                <div class="sidebar-card">
                    <h4 class="sidebar-title">More <?= e($post['category_name']) ?> Tests</h4>
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <?php foreach ($relatedPosts as $rel): ?>
                            <div style="padding-bottom: 12px; border-bottom: 1px solid var(--border-subtle);">
                                <h5 style="font-size: 0.95rem; margin-bottom: 4px;">
                                    <a href="<?= post_url($rel['slug']) ?>"><?= e($rel['title']) ?></a>
                                </h5>
                                <div style="display: flex; justify-content: space-between; font-size: 0.78rem; color: var(--text-muted);">
                                    <span><?= e($rel['total_questions']) ?> Questions</span>
                                    <span><?= number_format($rel['downloads_count']) ?> downloads</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
