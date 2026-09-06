<?php
/**
 * Mock Exam PDFs - Create New Mock Exam Post (admin/post-new.php)
 */

$adminTitle = "Publish New Mock Exam";
require_once __DIR__ . '/inc/admin-header.php';

$errors = [];
$categories = get_categories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $errors[] = 'Invalid security token. Please refresh and try again.';
    }

    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    if (empty($slug)) {
        $slug = slugify($title);
    } else {
        $slug = slugify($slug);
    }

    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $examYear = trim($_POST['exam_year'] ?? date('Y'));
    $difficulty = $_POST['difficulty'] ?? 'Medium';
    $durationMins = (int)($_POST['duration_mins'] ?? 120);
    $totalQuestions = (int)($_POST['total_questions'] ?? 100);
    $totalMarks = (int)($_POST['total_marks'] ?? 200);
    $negativeMarking = trim($_POST['negative_marking'] ?? '0.33 Marks (1/3rd)');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $status = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'published';

    // SEO
    $metaTitle = trim($_POST['meta_title'] ?? '');
    $metaDesc = trim($_POST['meta_description'] ?? '');
    $metaKeywords = trim($_POST['meta_keywords'] ?? '');

    if (empty($title)) {
        $errors[] = 'Post Title is required.';
    }

    // Check slug uniqueness
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE slug = ?");
    $checkStmt->execute([$slug]);
    if ($checkStmt->fetchColumn() > 0) {
        $slug .= '-' . time();
    }

    // Handle Primary PDF File
    $pdfPath = '';
    $pdfName = '';
    $pdfSize = 0;

    if (empty($_FILES['pdf_file']['name'])) {
        $errors[] = 'Please upload a primary Mock Exam PDF file.';
    } else {
        $pdfUpload = handle_file_upload($_FILES['pdf_file'], 'pdfs', ['pdf']);
        if (!$pdfUpload['success']) {
            $errors[] = 'PDF Upload Error: ' . $pdfUpload['error'];
        } else {
            $pdfPath = $pdfUpload['path'];
            $pdfName = $pdfUpload['original_name'];
            $pdfSize = $pdfUpload['size'];
        }
    }

    // Handle Optional Solution PDF File
    $solutionPath = null;
    $solutionName = null;
    if (!empty($_FILES['solution_file']['name'])) {
        $solutionUpload = handle_file_upload($_FILES['solution_file'], 'pdfs', ['pdf']);
        if (!$solutionUpload['success']) {
            $errors[] = 'Answer Key Upload Error: ' . $solutionUpload['error'];
        } else {
            $solutionPath = $solutionUpload['path'];
            $solutionName = $solutionUpload['original_name'];
        }
    }

    if (empty($errors)) {
        $insertSql = "
            INSERT INTO posts (
                title, slug, category_id, excerpt, content, pdf_file, pdf_name, pdf_size,
                solution_file, solution_name, exam_year, difficulty, duration_mins, total_questions,
                total_marks, negative_marking, is_featured, status, meta_title, meta_description, meta_keywords
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?
            )
        ";

        $stmt = $pdo->prepare($insertSql);
        $stmt->execute([
            $title, $slug, $categoryId, $excerpt, $content, $pdfPath, $pdfName, $pdfSize,
            $solutionPath, $solutionName, $examYear, $difficulty, $durationMins, $totalQuestions,
            $totalMarks, $negativeMarking, $isFeatured, $status, $metaTitle, $metaDesc, $metaKeywords
        ]);

        $newId = $pdo->lastInsertId();
        set_flash('success', 'Mock exam paper published successfully!');
        header("Location: " . url('admin/posts.php'));
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
        <!-- Main Column -->
        <div>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Mock Exam Details</h3>
                </div>
                <div class="admin-card-body">
                    <div class="form-group">
                        <label class="form-label" for="postTitle">Post / Exam Title <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="postTitle" 
                            name="title" 
                            class="form-control" 
                            required 
                            placeholder="e.g. UPSC CSE 2026 Prelims - GS Paper 1 Full Length Mock Test"
                            value="<?= e($_POST['title'] ?? '') ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="postSlug">URL Slug</label>
                        <input 
                            type="text" 
                            id="postSlug" 
                            name="slug" 
                            class="form-control" 
                            placeholder="auto-generated-from-title"
                            value="<?= e($_POST['slug'] ?? '') ?>"
                        >
                        <div class="form-hint">Preview URL: <?= url('post/') ?><span id="slugPreview">your-slug</span></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="excerpt">Short Excerpt / Summary</label>
                        <textarea 
                            id="excerpt" 
                            name="excerpt" 
                            class="form-control" 
                            rows="2" 
                            style="min-height: 80px;"
                            placeholder="Brief description showing on cards and search results..."
                        ><?= e($_POST['excerpt'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="content">Detailed Content & Syllabus Instructions</label>
                        <textarea 
                            id="content" 
                            name="content" 
                            class="form-control" 
                            rows="8"
                            placeholder="Write instructions, topics covered, scoring rules, or key guidelines for students..."
                        ><?= e($_POST['content'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- PDF Upload Section -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>PDF Uploads</h3>
                </div>
                <div class="admin-card-body">
                    <div class="form-group">
                        <label class="form-label">Primary Mock Exam Question Paper PDF <span class="required">*</span></label>
                        <div class="file-upload-zone">
                            <div class="upload-icon">📄</div>
                            <div style="font-weight: 700; margin-bottom: 4px;">Click or drag & drop PDF here</div>
                            <div style="font-size: 0.8rem; color: var(--admin-muted);">Supports .pdf files up to 50MB</div>
                            <input type="file" id="pdfFile" name="pdf_file" accept=".pdf,application/pdf" required>
                            <div id="pdfFileInfo" class="file-preview-info" style="display: none;"></div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 24px;">
                        <label class="form-label">Answer Key / Detailed Solution PDF (Optional)</label>
                        <div class="file-upload-zone">
                            <div class="upload-icon" style="color: #059669;">📑</div>
                            <div style="font-weight: 700; margin-bottom: 4px;">Upload Answer Key PDF (Optional)</div>
                            <div style="font-size: 0.8rem; color: var(--admin-muted);">Upload separate solution document if available</div>
                            <input type="file" id="solutionFile" name="solution_file" accept=".pdf,application/pdf">
                            <div id="solutionFileInfo" class="file-preview-info" style="display: none;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Suite -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Search Engine Optimization (SEO)</h3>
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
                            placeholder="Custom Google Search title (defaults to post title if blank)"
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
                            style="min-height: 80px;"
                            placeholder="Compelling 150-160 character snippet that appears under your page in search engines..."
                        ><?= e($_POST['meta_description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="metaKeywords">SEO Keywords</label>
                        <input 
                            type="text" 
                            id="metaKeywords" 
                            name="meta_keywords" 
                            class="form-control" 
                            placeholder="comma, separated, keywords, e.g. upsc mock pdf, gs paper 1 2026"
                            value="<?= e($_POST['meta_keywords'] ?? '') ?>"
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Column -->
        <div>
            <!-- Publish Controls -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Publish Settings</h3>
                </div>
                <div class="admin-card-body">
                    <div class="form-group">
                        <label class="form-label" for="postStatus">Status</label>
                        <select id="postStatus" name="status" class="form-control">
                            <option value="published" <?= (($_POST['status'] ?? 'published') === 'published') ? 'selected' : '' ?>>Published (Live)</option>
                            <option value="draft" <?= (($_POST['status'] ?? '') === 'draft') ? 'selected' : '' ?>>Draft</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer;">
                            <input type="checkbox" name="is_featured" value="1" <?= (!empty($_POST['is_featured'])) ? 'checked' : '' ?>>
                            <span>Featured on Homepage</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">
                        <span>🚀 Publish Mock Exam</span>
                    </button>
                </div>
            </div>

            <!-- Exam Attributes -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Exam Specifications</h3>
                </div>
                <div class="admin-card-body">
                    <div class="form-group">
                        <label class="form-label" for="categoryId">Exam Category</label>
                        <select id="categoryId" name="category_id" class="form-control">
                            <option value="">-- Select Exam Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="examYear">Target Exam Year</label>
                        <input type="text" id="examYear" name="exam_year" class="form-control" value="<?= e($_POST['exam_year'] ?? date('Y')) ?>" placeholder="2026">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="difficulty">Difficulty Level</label>
                        <select id="difficulty" name="difficulty" class="form-control">
                            <option value="Easy" <?= (($_POST['difficulty'] ?? '') === 'Easy') ? 'selected' : '' ?>>Easy</option>
                            <option value="Medium" <?= (($_POST['difficulty'] ?? 'Medium') === 'Medium') ? 'selected' : '' ?>>Medium</option>
                            <option value="Hard" <?= (($_POST['difficulty'] ?? '') === 'Hard') ? 'selected' : '' ?>>Hard</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="durationMins">Duration (Minutes)</label>
                        <input type="number" id="durationMins" name="duration_mins" class="form-control" value="<?= e($_POST['duration_mins'] ?? '120') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="totalQuestions">Total Questions</label>
                        <input type="number" id="totalQuestions" name="total_questions" class="form-control" value="<?= e($_POST['total_questions'] ?? '100') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="totalMarks">Total Marks</label>
                        <input type="number" id="totalMarks" name="total_marks" class="form-control" value="<?= e($_POST['total_marks'] ?? '200') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="negativeMarking">Negative Marking Rule</label>
                        <input type="text" id="negativeMarking" name="negative_marking" class="form-control" value="<?= e($_POST['negative_marking'] ?? '0.33 Marks (1/3rd)') ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/inc/admin-footer.php'; ?>
