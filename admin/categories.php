<?php
/**
 * Mock Exam PDFs - Categories Management (admin/categories.php)
 */

$adminTitle = "Manage Exam Categories";
require_once __DIR__ . '/inc/admin-header.php';

$errors = [];

// Handle New Category Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $errors[] = 'Invalid security token. Please refresh and try again.';
    }

    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    if (empty($slug)) {
        $slug = slugify($name);
    } else {
        $slug = slugify($slug);
    }
    $description = trim($_POST['description'] ?? '');
    $color = trim($_POST['color'] ?? '#4f46e5');

    if (empty($name)) {
        $errors[] = 'Category Name is required.';
    }

    // Check slug
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
    $checkStmt->execute([$slug]);
    if ($checkStmt->fetchColumn() > 0) {
        $errors[] = 'A category with this URL slug already exists.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, color) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $slug, $description, $color]);
        set_flash('success', 'Category "' . htmlspecialchars($name) . '" added successfully!');
        header("Location: " . url('admin/categories.php'));
        exit;
    }
}

// Handle Delete Category
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $catId = (int)$_GET['id'];
    // Update posts to null
    $pdo->prepare("UPDATE posts SET category_id = NULL WHERE category_id = ?")->execute([$catId]);
    // Delete category
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$catId]);
    set_flash('success', 'Category deleted successfully.');
    header("Location: " . url('admin/categories.php'));
    exit;
}

$categories = get_categories();
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

<div style="display: grid; grid-template-columns: 340px 1fr; gap: 24px;">
    <!-- Add Category Form -->
    <div>
        <div class="admin-card">
            <div class="admin-card-header">
                <h3>Add New Category</h3>
            </div>
            <div class="admin-card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                    <div class="form-group">
                        <label class="form-label" for="catName">Category Name <span class="required">*</span></label>
                        <input type="text" id="catName" name="name" class="form-control" required placeholder="e.g. Law & Judiciary" value="<?= e($_POST['name'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="catSlug">Slug</label>
                        <input type="text" id="catSlug" name="slug" class="form-control" placeholder="e.g. law-judiciary" value="<?= e($_POST['slug'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="catColor">Badge Color</label>
                        <input type="color" id="catColor" name="color" class="form-control" value="<?= e($_POST['color'] ?? '#4f46e5') ?>" style="height: 44px; padding: 4px; cursor: pointer;">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="catDesc">Description</label>
                        <textarea id="catDesc" name="description" class="form-control" rows="3" placeholder="Brief info about exam types in this category..."><?= e($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <span>+ Create Category</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Category List Table -->
    <div>
        <div class="admin-card">
            <div class="admin-card-header">
                <h3>All Exam Categories (<?= count($categories) ?>)</h3>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Slug</th>
                            <th>Mock Tests</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--admin-muted); padding: 30px;">
                                    No categories created yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <span style="display: inline-block; width: 14px; height: 14px; border-radius: 4px; background: <?= e($cat['color']) ?>;"></span>
                                            <strong style="font-size: 0.95rem;"><?= e($cat['name']) ?></strong>
                                        </div>
                                        <?php if (!empty($cat['description'])): ?>
                                            <div style="font-size: 0.78rem; color: var(--admin-muted); margin-top: 2px;">
                                                <?= e($cat['description']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color: var(--admin-muted); font-size: 0.85rem; font-family: monospace;">
                                        <?= e($cat['slug']) ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?= $cat['post_count'] ?> tests
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: inline-flex; gap: 6px;">
                                            <a href="<?= category_url($cat['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm" title="View category archive on website">
                                                View ↗
                                            </a>
                                            <a 
                                                href="<?= url('admin/categories.php?action=delete&id=' . $cat['id']) ?>" 
                                                class="btn btn-secondary btn-sm confirm-delete"
                                                data-item="category '<?= e($cat['name']) ?>'"
                                                style="color: #dc2626;"
                                                title="Delete category"
                                            >
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const nameInput = document.getElementById('catName');
    const slugInput = document.getElementById('catSlug');
    if (nameInput && slugInput) {
        nameInput.addEventListener('input', () => {
            slugInput.value = nameInput.value.toLowerCase().trim()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        });
    }
});
</script>

<?php require_once __DIR__ . '/inc/admin-footer.php'; ?>
