<?php
/**
 * Mock Exam PDFs - Edit Mock Exam Post (admin/post-edit.php)
 */

$adminTitle = "Edit Mock Exam";
require_once __DIR__ . '/inc/admin-header.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header("Location: " . url('admin/posts.php'));
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    set_flash('danger', 'Mock exam post not found.');
    header("Location: " . url('admin/posts.php'));
    exit;
}

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

    // Check slug uniqueness (excluding current post)
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE slug = ? AND id != ?");
    $checkStmt->execute([$slug, $id]);
    if ($checkStmt->fetchColumn() > 0) {
        $slug .= '-' . time();
    }

    $pdfPath = $post['pdf_file'];
    $pdfName = $post['pdf_name'];
    $pdfSize = $post['pdf_size'];

    // Check if new primary PDF is uploaded
    if (!empty($_FILES['pdf_file']['name'])) {
        $pdfUpload = handle_file_upload($_FILES['pdf_file'], 'pdfs', ['pdf']);
        if (!$pdfUpload['success']) {
            $errors[] = 'PDF Upload Error: ' . $pdfUpload['error'];
        } else {
            // Delete old file
            if (!empty($post['pdf_file']) && file_exists(__DIR__ . '/../' . $post['pdf_file'])) {
                @unlink(__DIR__ . '/../' . $post['pdf_file']);
            }
            $pdfPath = $pdfUpload['path'];
            $pdfName = $pdfUpload['original_name'];
            $pdfSize = $pdfUpload['size'];
        }
    }

    $solutionPath = $post['solution_file'];
    $solutionName = $post['solution_name'];

    // Check if new solution PDF is uploaded
    if (!empty($_FILES['solution_file']['name'])) {
        $solutionUpload = handle_file_upload($_FILES['solution_file'], 'pdfs', ['pdf']);
        if (!$solutionUpload['success']) {
            $errors[] = 'Answer Key Upload Error: ' . $solutionUpload['error'];
        } else {
            if (!empty($post['solution_file']) && file_exists(__DIR__ . '/../' . $post['solution_file'])) {
                @unlink(__DIR__ . '/../' . $post['solution_file']);
            }
            $solutionPath = $solutionUpload['path'];
            $solutionName = $solutionUpload['original_name'];
        }
    }

    if (empty($errors)) {
        $updateSql = "
            UPDATE posts SET
                title = ?, slug = ?, category_id = ?, excerpt = ?, content = ?,
                pdf_file = ?, pdf_name = ?, pdf_size = ?,
                solution_file = ?, solution_name = ?, exam_year = ?, difficulty = ?,
                duration_mins = ?, total_questions = ?, total_marks = ?, negative_marking = ?,
                is_featured = ?, status = ?, meta_title = ?, meta_description = ?, meta_keywords = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ";

        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([
            $title, $slug, $categoryId, $excerpt, $content,
            $pdfPath, $pdfName, $pdfSize,
            $solutionPath, $solutionName, $examYear, $difficulty,
            $durationMins, $totalQuestions, $totalMarks, $negativeMarking,
            $isFeatured, $status, $metaTitle, $metaDesc, $metaKeywords,
            $id
        ]);

        set_flash('success', 'Mock exam paper updated successfully!');
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
                    <h3>Edit Mock Exam Details</h3>
                    <a href="<?= post_url($post['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm">View on Website ↗</a>
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
                            value="<?= e($_POST['title'] ?? $post['title']) ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="postSlug">URL Slug</label>
                        <input 
                            type="text" 
                            id="postSlug" 
                            name="slug" 
                            class="form-control" 
                            value="<?= e($_POST['slug'] ?? $post['slug']) ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="excerpt">Short Excerpt / Summary</label>
                        <textarea 
                            id="excerpt" 
                            name="excerpt" 
                            class="form-control" 
                            rows="2" 
                            style="min-height: 80px;"
                        ><?= e($_POST['excerpt'] ?? $post['excerpt']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="content">Detailed Content & Syllabus Instructions</label>
                        <textarea 
                            id="content" 
                            name="content" 
                            class="form-control" 
                            rows="8"
                        ><?= e($_POST['content'] ?? $post['content']) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- PDF Upload Section -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>PDF Files</h3>
                </div>
                <div class="admin-card-body">
                    <div class="form-group">
                        <label class="form-label">Primary Mock Exam Question Paper PDF</label>
                        <div style="background: #f1f5f9; padding: 12px 16px; border-radius: 8px; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                📄 Current File: <strong><?= e($post['pdf_name'] ?: basename($post['pdf_file'])) ?></strong>
                                (<?= format_bytes((int)$post['pdf_size']) ?>)
                            </div>
                            <a href="<?= url($post['pdf_file']) ?>" target="_blank" class="btn btn-secondary btn-sm">Preview File</a>
                        </div>

                        <div class="file-upload-zone">
                            <div class="upload-icon">🔄</div>
                            <div style="font-weight: 700; margin-bottom: 4px;">Click or drag to replace PDF file</div>
                            <div style="font-size: 0.8rem; color: var(--admin-muted);">Leave empty to keep current file</div>
                            <input type="file" id="pdfFile" name="pdf_file" accept=".pdf,application/pdf">
                            <div id="pdfFileInfo" class="file-preview-info" style="display: none;"></div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 24px;">
                        <label class="form-label">Answer Key / Solution PDF</label>
                        <?php if (!empty($post['solution_file'])): ?>
                            <div style="background: #f1f5f9; padding: 12px 16px; border-radius: 8px; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    📑 Current Solution: <strong><?= e($post['solution_name'] ?: basename($post['solution_file'])) ?></strong>
                                </div>
                                <a href="<?= url($post['solution_file']) ?>" target="_blank" class="btn btn-secondary btn-sm">Preview Solution</a>
                            </div>
                        <?php endif; ?>

                        <div class="file-upload-zone">
                            <div class="upload-icon" style="color: #059669;">📑</div>
                            <div style="font-weight: 700; margin-bottom: 4px;">Click or drag to upload/replace solution PDF</div>
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
                            value="<?= e($_POST['meta_title'] ?? $post['meta_title']) ?>"
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
                        ><?= e($_POST['meta_description'] ?? $post['meta_description']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="metaKeywords">SEO Keywords</label>
                        <input 
                            type="text" 
                            id="metaKeywords" 
                            name="meta_keywords" 
                            class="form-control" 
                            value="<?= e($_POST['meta_keywords'] ?? $post['meta_keywords']) ?>"
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
                            <option value="published" <?= (($_POST['status'] ?? $post['status']) === 'published') ? 'selected' : '' ?>>Published (Live)</option>
                            <option value="draft" <?= (($_POST['status'] ?? $post['status']) === 'draft') ? 'selected' : '' ?>>Draft</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer;">
                            <input type="checkbox" name="is_featured" value="1" <?= (!empty($_POST['is_featured']) || $post['is_featured']) ? 'checked' : '' ?>>
                            <span>Featured on Homepage</span>
                        </label>
                    </div>

                    <div style="font-size: 0.82rem; color: var(--admin-muted); margin-bottom: 16px;">
                        <div>Downloads: <strong><?= number_format($post['downloads_count']) ?></strong></div>
                        <div>Views: <strong><?= number_format($post['views_count']) ?></strong></div>
                        <div>Created: <?= format_date($post['created_at']) ?></div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">
                        <span>💾 Save Changes</span>
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
                                <option value="<?= $cat['id'] ?>" <?= (($_POST['category_id'] ?? $post['category_id']) == $cat['id']) ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="examYear">Target Exam Year</label>
                        <input type="text" id="examYear" name="exam_year" class="form-control" value="<?= e($_POST['exam_year'] ?? $post['exam_year']) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="difficulty">Difficulty Level</label>
                        <select id="difficulty" name="difficulty" class="form-control">
                            <option value="Easy" <?= (($_POST['difficulty'] ?? $post['difficulty']) === 'Easy') ? 'selected' : '' ?>>Easy</option>
                            <option value="Medium" <?= (($_POST['difficulty'] ?? $post['difficulty']) === 'Medium') ? 'selected' : '' ?>>Medium</option>
                            <option value="Hard" <?= (($_POST['difficulty'] ?? $post['difficulty']) === 'Hard') ? 'selected' : '' ?>>Hard</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="durationMins">Duration (Minutes)</label>
                        <input type="number" id="durationMins" name="duration_mins" class="form-control" value="<?= e($_POST['duration_mins'] ?? $post['duration_mins']) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="totalQuestions">Total Questions</label>
                        <input type="number" id="totalQuestions" name="total_questions" class="form-control" value="<?= e($_POST['total_questions'] ?? $post['total_questions']) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="totalMarks">Total Marks</label>
                        <input type="number" id="totalMarks" name="total_marks" class="form-control" value="<?= e($_POST['total_marks'] ?? $post['total_marks']) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="negativeMarking">Negative Marking Rule</label>
                        <input type="text" id="negativeMarking" name="negative_marking" class="form-control" value="<?= e($_POST['negative_marking'] ?? $post['negative_marking']) ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/inc/admin-footer.php'; ?>
