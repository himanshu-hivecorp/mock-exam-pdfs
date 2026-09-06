<?php
/**
 * Mock Exam PDFs - Admin All Posts Management (admin/posts.php)
 */

$adminTitle = "All Mock Exam Posts";
require_once __DIR__ . '/inc/admin-header.php';

// Handle Delete Request
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $deleteId = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$deleteId]);
    $toDelete = $stmt->fetch();

    if ($toDelete) {
        // Optionally delete files
        if (!empty($toDelete['pdf_file']) && file_exists(__DIR__ . '/../' . $toDelete['pdf_file'])) {
            @unlink(__DIR__ . '/../' . $toDelete['pdf_file']);
        }
        if (!empty($toDelete['solution_file']) && file_exists(__DIR__ . '/../' . $toDelete['solution_file'])) {
            @unlink(__DIR__ . '/../' . $toDelete['solution_file']);
        }

        $delStmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $delStmt->execute([$deleteId]);
        set_flash('success', 'Mock exam "' . htmlspecialchars($toDelete['title']) . '" deleted successfully.');
    } else {
        set_flash('danger', 'Mock exam post not found.');
    }

    header("Location: " . url('admin/posts.php'));
    exit;
}

// Search and filter parameters
$search = trim($_GET['q'] ?? '');
$catFilter = $_GET['cat'] ?? null;
$statusFilter = $_GET['status'] ?? 'all';

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$postsData = get_posts([
    'search'        => $search,
    'category_slug' => $catFilter,
    'status'        => $statusFilter,
    'limit'         => $limit,
    'offset'        => $offset,
    'order_by'      => 'created_at DESC'
]);

$posts = $postsData['items'];
$totalPosts = $postsData['total'];
$totalPages = $postsData['total_pages'];
$categories = get_categories();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <h3 style="font-size: 1.25rem;">Manage Mock Exams (<?= $totalPosts ?>)</h3>
        <p style="color: var(--admin-muted); font-size: 0.85rem;">View, search, edit, or delete published practice papers.</p>
    </div>

    <a href="<?= url('admin/post-new.php') ?>" class="btn btn-primary">
        <span>+ Add New Mock Exam</span>
    </a>
</div>

<!-- Search & Filters Bar -->
<div class="admin-card" style="margin-bottom: 20px;">
    <div class="admin-card-body" style="padding: 16px 20px;">
        <form method="GET" action="" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <input 
                type="text" 
                name="q" 
                placeholder="Search mock exams by title or keyword..." 
                value="<?= e($search) ?>" 
                class="form-control" 
                style="flex: 1; min-width: 200px;"
            >

            <select name="cat" class="form-control" style="width: auto; min-width: 180px;">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat['slug']) ?>" <?= ($catFilter === $cat['slug']) ? 'selected' : '' ?>>
                        <?= e($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="status" class="form-control" style="width: auto; min-width: 140px;">
                <option value="all" <?= ($statusFilter === 'all') ? 'selected' : '' ?>>All Statuses</option>
                <option value="published" <?= ($statusFilter === 'published') ? 'selected' : '' ?>>Published</option>
                <option value="draft" <?= ($statusFilter === 'draft') ? 'selected' : '' ?>>Draft</option>
            </select>

            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <?php if (!empty($search) || !empty($catFilter) || $statusFilter !== 'all'): ?>
                <a href="<?= url('admin/posts.php') ?>" class="btn btn-secondary btn-sm">Reset</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Posts Table Card -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title & Specs</th>
                    <th>Category</th>
                    <th>File Size</th>
                    <th>Downloads</th>
                    <th>Views</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($posts)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--admin-muted); padding: 40px;">
                            No mock exam posts found matching your criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td>
                                <strong style="font-size: 0.95rem;">
                                    <a href="<?= url('admin/post-edit.php?id=' . $post['id']) ?>">
                                        <?= e($post['title']) ?>
                                    </a>
                                </strong>
                                <div style="font-size: 0.78rem; color: var(--admin-muted); margin-top: 2px;">
                                    <?= e($post['total_questions']) ?> Qs • <?= e($post['duration_mins']) ?> Mins • <?= e($post['difficulty']) ?>
                                    <?php if ($post['is_featured']): ?>
                                        • <span style="color: #d97706; font-weight: 700;">★ Featured</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background: <?= e($post['category_color']) ?>20; color: <?= e($post['category_color']) ?>;">
                                    <?= e($post['category_name'] ?: 'Uncategorized') ?>
                                </span>
                            </td>
                            <td><?= format_bytes((int)$post['pdf_size']) ?></td>
                            <td>
                                <span style="font-weight: 700; color: #059669;"><?= number_format($post['downloads_count']) ?></span>
                            </td>
                            <td><?= number_format($post['views_count']) ?></td>
                            <td>
                                <span class="badge badge-<?= $post['status'] === 'published' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($post['status']) ?>
                                </span>
                            </td>
                            <td style="font-size: 0.82rem; color: var(--admin-muted);">
                                <?= format_date($post['created_at']) ?>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="<?= post_url($post['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm" title="View live">
                                        View
                                    </a>
                                    <a href="<?= url('admin/post-edit.php?id=' . $post['id']) ?>" class="btn btn-secondary btn-sm" title="Edit details">
                                        Edit
                                    </a>
                                    <a 
                                        href="<?= url('admin/posts.php?action=delete&id=' . $post['id']) ?>" 
                                        class="btn btn-secondary btn-sm confirm-delete" 
                                        data-item="<?= e($post['title']) ?>"
                                        style="color: #dc2626;"
                                        title="Delete post"
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

    <?php if ($totalPages > 1): ?>
        <div style="padding: 16px 20px; border-top: 1px solid var(--admin-border); display: flex; justify-content: center;">
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?= url('admin/posts.php?' . http_build_query(array_merge($_GET, ['page' => $i]))) ?>" class="page-link <?= ($i === $page) ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/inc/admin-footer.php'; ?>
