<?php
/**
 * Mock Exam PDFs - Upcoming Exams & Notifications Directory (upcoming-exams.php)
 *
 * @package   MockExamPDFs
 * @author    Himanshu Ranjan Sahu <https://github.com/himanshu-hivecorp>
 * @copyright 2026 Himanshu Ranjan Sahu
 * @license   MIT License
 * @link      https://github.com/himanshu-hivecorp/mock-exam-pdfs
 */

require_once __DIR__ . '/includes/functions.php';

$currentCat = $_GET['cat'] ?? null;
$search = trim($_GET['q'] ?? '');

$where = ["n.is_active = 1"];
$params = [];

if ($currentCat) {
    $where[] = "c.slug = ?";
    $params[] = $currentCat;
}

if ($search) {
    $where[] = "(n.title LIKE ? OR n.exam_name LIKE ? OR n.details LIKE ?)";
    $s = '%' . $search . '%';
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
}

$whereClause = "WHERE " . implode(" AND ", $where);

$sql = "
    SELECT n.*, c.name AS category_name, c.color AS category_color, c.slug AS category_slug
    FROM exam_notifications n
    LEFT JOIN categories c ON n.category_id = c.id
    $whereClause
    ORDER BY n.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$notifications = $stmt->fetchAll();

$categories = get_categories();
$siteName = get_setting('site_name', 'Mock Exam PDFs');
$customTitle = "Upcoming Competitive Exam Notifications & Exam Dates (2026)";
$customDesc = "Latest official notifications, exam dates, application deadlines, and vacancies for UPSC, SSC, Banking, GATE, and state competitive examinations.";

require_once __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<section class="post-header-section">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= url() ?>">Home</a>
            <span>/</span>
            <span style="color: var(--text-dark); font-weight: 600;">Upcoming Exams & Notifications</span>
        </nav>

        <div style="display: inline-flex; align-items: center; gap: 8px; background: #fee2e2; color: #dc2626; font-size: 0.8rem; font-weight: 800; padding: 4px 12px; border-radius: var(--radius-pill); margin-bottom: 12px;">
            <span style="animation: pulse 2s infinite;">📢</span>
            <span>Real-time Exam Updates & Deadlines</span>
        </div>

        <h1 style="margin-bottom: 10px;">Upcoming Competitive Exam Notifications</h1>
        <p style="font-size: 1.15rem; color: var(--text-muted); max-width: 1100px; line-height: 1.6;">
            Stay ahead with official notification dates, registration deadlines, eligibility criteria, and direct recruitment links for major entrance exams.
        </p>

        <!-- Search Bar -->
        <form method="GET" action="" style="max-width: 800px; margin-top: 24px; display: flex; gap: 8px;">
            <input 
                type="text" 
                name="q" 
                value="<?= e($search) ?>" 
                placeholder="Search by exam name, post, or organization..." 
                class="form-control"
                style="padding: 12px 18px; border-radius: var(--radius-md); font-size: 1rem;"
            >
            <button type="submit" class="btn btn-primary" style="padding: 12px 24px;">Search</button>
            <?php if ($search || $currentCat): ?>
                <a href="<?= url('upcoming-exams.php') ?>" class="btn btn-secondary">Reset</a>
            <?php endif; ?>
        </form>
    </div>
</section>

<!-- Main Content -->
<section class="container" style="padding-top: 24px; padding-bottom: 60px;">
    <!-- Category Filter Tabs -->
    <div class="category-scroll" style="margin-bottom: 24px;">
        <a href="<?= url('upcoming-exams.php') ?>" class="category-pill <?= empty($currentCat) ? 'active' : '' ?>">
            <span>All Notifications</span>
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="<?= url('upcoming-exams.php?cat=' . urlencode($cat['slug'])) ?>" class="category-pill <?= ($currentCat === $cat['slug']) ? 'active' : '' ?>">
                <span><?= e($cat['name']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($notifications)): ?>
        <div style="text-align: center; padding: 60px 20px; background: #ffffff; border-radius: var(--radius-lg); border: 1px dashed var(--border-subtle); margin-top: 20px;">
            <div style="font-size: 3.5rem; margin-bottom: 12px;">🔔</div>
            <h3>No Exam Notifications Found</h3>
            <p style="color: var(--text-muted); margin: 8px 0 20px;">Check back shortly for new competitive exam updates.</p>
            <a href="<?= url('upcoming-exams.php') ?>" class="btn btn-primary">View All Upcoming Exams</a>
        </div>
    <?php else: ?>
        <div class="notif-grid" style="grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));">
            <?php foreach ($notifications as $notif): 
                $tagClass = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $notif['badge_tag']));
            ?>
                <div class="notif-card" style="padding: 24px;">
                    <div class="notif-card-top">
                        <span class="notif-tag tag-<?= $tagClass ?>">
                            <?= e($notif['badge_tag']) ?>
                        </span>
                        <?php if (!empty($notif['category_name'])): ?>
                            <span style="font-size: 0.78rem; font-weight: 700; color: <?= e($notif['category_color']) ?>;">
                                <?= e($notif['category_name']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <h3 class="notif-card-title" style="font-size: 1.15rem;">
                        <a href="<?= exam_url($notif['slug']) ?>"><?= e($notif['title']) ?></a>
                    </h3>
                    <div class="notif-exam-name" style="font-size: 0.85rem;"><?= e($notif['exam_name']) ?></div>

                    <div class="notif-pills" style="margin: 14px 0 18px;">
                        <?php if (!empty($notif['exam_date'])): ?>
                            <div class="notif-pill-item">
                                <span>📅 Exam Date:</span>
                                <strong><?= e($notif['exam_date']) ?></strong>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($notif['application_deadline'])): ?>
                            <div class="notif-pill-item">
                                <span>⏳ Apply Deadline:</span>
                                <strong><?= e($notif['application_deadline']) ?></strong>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($notif['total_vacancies'])): ?>
                            <div class="notif-pill-item">
                                <span>👥 Vacancies:</span>
                                <strong><?= e($notif['total_vacancies']) ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>

                    <p class="notif-desc" style="font-size: 0.9rem; line-height: 1.6; margin-bottom: 20px;">
                        <?= e($notif['details']) ?>
                    </p>

                    <div class="notif-card-actions">
                        <a href="<?= exam_url($notif['slug']) ?>" class="btn btn-primary btn-sm" style="width: 100%;">
                            <span>Read Full Notification & Apply Online →</span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
