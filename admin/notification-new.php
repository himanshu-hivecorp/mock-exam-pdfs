<?php
/**
 * Mock Exam PDFs - Create New Upcoming Exam Notification Post (admin/notification-new.php)
 */

$adminTitle = "Post New Exam Alert";
require_once __DIR__ . '/inc/admin-header.php';

$errors = [];
$categories = get_categories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $errors[] = 'Invalid security token.';
    }

    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    if (empty($slug)) {
        $slug = slugify($title);
    } else {
        $slug = slugify($slug);
    }

    $examName = trim($_POST['exam_name'] ?? '');
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $badgeTag = trim($_POST['badge_tag'] ?? 'Registration Open');
    $examDate = trim($_POST['exam_date'] ?? '');
    $deadline = trim($_POST['application_deadline'] ?? '');
    $vacancies = trim($_POST['total_vacancies'] ?? '');
    $officialLink = trim($_POST['official_link'] ?? '');
    $details = trim($_POST['details'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    // SEO
    $metaTitle = trim($_POST['meta_title'] ?? '');
    $metaDesc = trim($_POST['meta_description'] ?? '');
    $metaKeywords = trim($_POST['meta_keywords'] ?? '');

    if (empty($title)) {
        $errors[] = 'Notification Title is required.';
    }
    if (empty($examName)) {
        $errors[] = 'Exam Name / Board is required.';
    }

    // Slug check
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM exam_notifications WHERE slug = ?");
    $checkStmt->execute([$slug]);
    if ($checkStmt->fetchColumn() > 0) {
        $slug .= '-' . time();
    }

    // Handle optional notification PDF file
    $pdfPath = null;
    $pdfName = null;
    if (!empty($_FILES['notification_pdf']['name'])) {
        $upload = handle_file_upload($_FILES['notification_pdf'], 'pdfs', ['pdf']);
        if (!$upload['success']) {
            $errors[] = 'PDF Upload Error: ' . $upload['error'];
        } else {
            $pdfPath = $upload['path'];
            $pdfName = $upload['original_name'];
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO exam_notifications (
                title, slug, exam_name, category_id, badge_tag, exam_date, application_deadline,
                total_vacancies, official_link, notification_pdf, notification_pdf_name, details, content,
                is_active, meta_title, meta_description, meta_keywords
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?
            )
        ");
        $stmt->execute([
            $title, $slug, $examName, $categoryId, $badgeTag, $examDate, $deadline,
            $vacancies, $officialLink, $pdfPath, $pdfName, $details, $content,
            $isActive, $metaTitle, $metaDesc, $metaKeywords
        ]);

        set_flash('success', 'Exam notification post created successfully! Visitors can now read the post and apply.');
        header("Location: " . url('admin/notifications.php'));
        exit;
    }
}

$csrfToken = generate_csrf_token();
?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul style="padding-left: 20px;">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

    <div class="post-form-grid">
        <!-- Main Details -->
        <div>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Exam Post Details</h3>
                </div>
                <div class="admin-card-body">
                    <div class="form-group">
                        <label class="form-label" for="postTitle">Notification Title <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="postTitle" 
                            name="title" 
                            class="form-control" 
                            required 
                            placeholder="e.g. UPSC Civil Services Prelims 2026 Notification Out"
                            value="<?= e($_POST['title'] ?? '') ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="postSlug">Post URL Slug</label>
                        <input 
                            type="text" 
                            id="postSlug" 
                            name="slug" 
                            class="form-control" 
                            placeholder="auto-generated-from-title"
                            value="<?= e($_POST['slug'] ?? '') ?>"
                        >
                        <div class="form-hint">Public URL: <?= url('exam/') ?><strong>your-slug</strong></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="examName">Exam / Board Name <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="examName" 
                            name="exam_name" 
                            class="form-control" 
                            required 
                            placeholder="e.g. UPSC CSE 2026 (Prelims)"
                            value="<?= e($_POST['exam_name'] ?? '') ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="details">Short Excerpt / Summary <span class="required">*</span></label>
                        <textarea 
                            id="details" 
                            name="details" 
                            class="form-control" 
                            rows="3"
                            placeholder="Short summary displayed on cards in home page and upcoming exams directory..."
                        ><?= e($_POST['details'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="content">Full Post Content & Application Guidelines</label>
                        <textarea 
                            id="content" 
                            name="content" 
                            class="form-control" 
                            rows="10"
                            placeholder="Full detailed article: Educational qualifications, age limits, syllabus details, exam pattern, step-by-step how to apply..."
                        ><?= e($_POST['content'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Official Apply Portal & PDF Attachment -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Official Application Portal & PDF (Displayed ONLY Inside Post)</h3>
                </div>
                <div class="admin-card-body">
                    <div class="alert alert-info" style="font-size: 0.85rem; margin-bottom: 20px;">
                        💡 <strong>Notice:</strong> This official link and notice PDF will <strong>only be shown inside this post</strong>. Users on the homepage must click "Read Post" first before applying.
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="officialLink">Official Apply Online / Portal Link</label>
                        <input 
                            type="url" 
                            id="officialLink" 
                            name="official_link" 
                            class="form-control" 
                            placeholder="https://upsc.gov.in or https://ssc.gov.in"
                            value="<?= e($_POST['official_link'] ?? '') ?>"
                        >
                        <div class="form-hint">The external link where users submit their application.</div>
                    </div>

                    <div class="form-group" style="margin-top: 20px;">
                        <label class="form-label">Official Notification PDF Advertisement (Optional)</label>
                        <div class="file-upload-zone">
                            <div class="upload-icon">📄</div>
                            <div style="font-weight: 700; margin-bottom: 4px;">Upload Official Notification PDF</div>
                            <div style="font-size: 0.8rem; color: var(--admin-muted);">Optional PDF document for students to download inside the post</div>
                            <input type="file" id="pdfFile" name="notification_pdf" accept=".pdf,application/pdf">
                            <div id="pdfFileInfo" class="file-preview-info" style="display: none;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Suite -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>SEO Settings</h3>
                </div>
                <div class="admin-card-body">
                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between;">
                            <label class="form-label" for="metaTitle">SEO Meta Title</label>
                            <span id="metaTitleCount" style="font-size: 0.75rem; color: var(--admin-muted);">0/60</span>
                        </div>
                        <input 
                            type="text" 
                            id="metaTitle" 
                            name="meta_title" 
                            class="form-control" 
                            placeholder="Custom Google Search title"
                            value="<?= e($_POST['meta_title'] ?? '') ?>"
                        >
                    </div>

                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between;">
                            <label class="form-label" for="metaDesc">SEO Meta Description</label>
                            <span id="metaDescCount" style="font-size: 0.75rem; color: var(--admin-muted);">0/160</span>
                        </div>
                        <textarea 
                            id="metaDesc" 
                            name="meta_description" 
                            class="form-control" 
                            rows="3" 
                            placeholder="Google search description snippet..."
                        ><?= e($_POST['meta_description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="metaKeywords">SEO Keywords</label>
                        <input 
                            type="text" 
                            id="metaKeywords" 
                            name="meta_keywords" 
                            class="form-control" 
                            placeholder="e.g. upsc cse 2026 notification, exam date, apply online"
                            value="<?= e($_POST['meta_keywords'] ?? '') ?>"
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Attributes -->
        <div>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Exam Dates & Publish</h3>
                </div>
                <div class="admin-card-body">
                    <div class="form-group">
                        <label class="form-label" for="badgeTag">Alert Badge / Status</label>
                        <select id="badgeTag" name="badge_tag" class="form-control">
                            <option value="Registration Open" <?= (($_POST['badge_tag'] ?? '') === 'Registration Open') ? 'selected' : '' ?>>Registration Open</option>
                            <option value="Exam Date Announced" <?= (($_POST['badge_tag'] ?? '') === 'Exam Date Announced') ? 'selected' : '' ?>>Exam Date Announced</option>
                            <option value="Upcoming Alert" <?= (($_POST['badge_tag'] ?? '') === 'Upcoming Alert') ? 'selected' : '' ?>>Upcoming Alert</option>
                            <option value="Admit Card Out" <?= (($_POST['badge_tag'] ?? '') === 'Admit Card Out') ? 'selected' : '' ?>>Admit Card Out</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="categoryId">Exam Category</label>
                        <select id="categoryId" name="category_id" class="form-control">
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="examDate">Exam Date</label>
                        <input type="text" id="examDate" name="exam_date" class="form-control" placeholder="e.g. May 24, 2026" value="<?= e($_POST['exam_date'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="deadline">Application Deadline</label>
                        <input type="text" id="deadline" name="application_deadline" class="form-control" placeholder="e.g. March 25, 2026" value="<?= e($_POST['application_deadline'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="vacancies">Total Vacancies</label>
                        <input type="text" id="vacancies" name="total_vacancies" class="form-control" placeholder="e.g. 1,056 Vacancies" value="<?= e($_POST['total_vacancies'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1" <?= (!isset($_POST['is_active']) || !empty($_POST['is_active'])) ? 'checked' : '' ?>>
                            <span>Publish as Active (Live)</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; margin-top: 10px;">
                        <span>📢 Publish Exam Post</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/inc/admin-footer.php'; ?>
