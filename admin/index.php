<?php
/**
 * Mock Exam PDFs - Admin Dashboard (admin/index.php)
 */

$adminTitle = "Dashboard Overview";
require_once __DIR__ . '/inc/admin-header.php';

$stats = get_admin_stats();

// Fetch latest 6 posts
$recentPosts = get_posts([
    'status'   => 'all',
    'limit'    => 6,
    'order_by' => 'created_at DESC'
])['items'];

// Fetch top 5 downloaded
$topDownloaded = get_posts([
    'status'   => 'published',
    'limit'    => 5,
    'order_by' => 'downloads DESC'
])['items'];
?>

<!-- Statistics Overview -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-val"><?= number_format($stats['total_posts']) ?></div>
            <div class="stat-name">Total Mock Exams</div>
        </div>
        <div class="stat-badge-icon icon-indigo">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-val"><?= number_format($stats['total_downloads']) ?></div>
            <div class="stat-name">Total PDF Downloads</div>
        </div>
        <div class="stat-badge-icon icon-emerald">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-val"><?= number_format($stats['total_views']) ?></div>
            <div class="stat-name">Total Post Views</div>
        </div>
        <div class="stat-badge-icon icon-blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-val"><?= number_format($stats['total_categories']) ?></div>
            <div class="stat-name">Exam Categories</div>
        </div>
        <div class="stat-badge-icon icon-amber">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                <polyline points="2 17 12 22 22 17"></polyline>
                <polyline points="2 12 12 17 22 12"></polyline>
            </svg>
        </div>
    </div>
</div>

<!-- Quick Action Shortcuts -->
<div style="display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap;">
    <a href="<?= url('admin/post-new.php') ?>" class="btn btn-primary">
        <span>+ Upload & Publish New Mock Exam PDF</span>
    </a>
    <a href="<?= url('admin/posts.php') ?>" class="btn btn-secondary">
        <span>Manage All Posts (<?= $stats['total_posts'] ?>)</span>
    </a>
    <a href="<?= url('admin/categories.php') ?>" class="btn btn-secondary">
        <span>Manage Categories</span>
    </a>
    <a href="<?= url('admin/notifications.php') ?>" class="btn btn-secondary" style="border-color: #10b981; color: #047857; font-weight: 700;">
        <span>📢 Exam Alerts (<?= $stats['total_notifications'] ?>)</span>
    </a>
    <a href="<?= url('admin/settings.php') ?>" class="btn btn-secondary">
        <span>Site Settings</span>
    </a>
</div>

<!-- Recent Posts & Top Downloaded Layout -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Recent Posts Card -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>Recently Published Mock Exams</h3>
            <a href="<?= url('admin/posts.php') ?>" style="font-size: 0.85rem; font-weight: 600;">View All →</a>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title & Category</th>
                        <th>Downloads</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentPosts)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--admin-muted); padding: 30px;">
                                No mock exams found. Start by creating one!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentPosts as $p): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600;">
                                        <a href="<?= url('admin/post-edit.php?id=' . $p['id']) ?>">
                                            <?= e($p['title']) ?>
                                        </a>
                                    </div>
                                    <div style="font-size: 0.78rem; color: var(--admin-muted); margin-top: 2px;">
                                        <?= e($p['category_name'] ?: 'General') ?> • <?= format_bytes((int)$p['pdf_size']) ?>
                                    </div>
                                </td>
                                <td>
                                    <strong><?= number_format($p['downloads_count']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $p['status'] === 'published' ? 'success' : 'warning' ?>">
                                        <?= ucfirst($p['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <a href="<?= url('admin/post-edit.php?id=' . $p['id']) ?>" class="btn btn-secondary btn-sm" title="Edit Post">Edit</a>
                                        <a href="<?= post_url($p['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm" title="View Live">View</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Downloaded Tests -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>Top Downloaded Tests</h3>
        </div>
        <div class="admin-card-body" style="padding: 16px 20px;">
            <?php if (empty($topDownloaded)): ?>
                <p style="color: var(--admin-muted); font-size: 0.85rem;">No downloads recorded yet.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <?php foreach ($topDownloaded as $index => $top): ?>
                        <div style="display: flex; align-items: flex-start; gap: 10px;">
                            <span style="font-weight: 800; color: var(--admin-primary); font-size: 1rem; width: 20px;">
                                #<?= $index + 1 ?>
                            </span>
                            <div style="flex: 1;">
                                <a href="<?= post_url($top['slug']) ?>" target="_blank" style="font-weight: 600; font-size: 0.875rem; color: var(--admin-text); display: block;">
                                    <?= e($top['title']) ?>
                                </a>
                                <span style="font-size: 0.78rem; color: #059669; font-weight: 700;">
                                    <?= number_format($top['downloads_count']) ?> downloads
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/inc/admin-footer.php'; ?>
