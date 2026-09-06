<?php
/**
 * Mock Exam PDFs - Admin Exam Notifications Management (admin/notifications.php)
 */

$adminTitle = "Manage Upcoming Exam Notifications";
require_once __DIR__ . '/inc/admin-header.php';

// Handle Actions
if (isset($_GET['action'])) {
    $actionId = (int)($_GET['id'] ?? 0);

    if ($_GET['action'] === 'delete' && $actionId) {
        $stmt = $pdo->prepare("SELECT notification_pdf FROM exam_notifications WHERE id = ?");
        $stmt->execute([$actionId]);
        $pdfFile = $stmt->fetchColumn();
        if (!empty($pdfFile) && file_exists(__DIR__ . '/../' . $pdfFile)) {
            @unlink(__DIR__ . '/../' . $pdfFile);
        }

        $pdo->prepare("DELETE FROM exam_notifications WHERE id = ?")->execute([$actionId]);
        set_flash('success', 'Exam notification deleted successfully.');
        header("Location: " . url('admin/notifications.php'));
        exit;
    }

    if ($_GET['action'] === 'toggle' && $actionId) {
        $pdo->prepare("UPDATE exam_notifications SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = ?")->execute([$actionId]);
        set_flash('success', 'Notification status updated.');
        header("Location: " . url('admin/notifications.php'));
        exit;
    }
}

// Fetch all notifications
$sql = "
    SELECT n.*, c.name AS category_name, c.color AS category_color
    FROM exam_notifications n
    LEFT JOIN categories c ON n.category_id = c.id
    ORDER BY n.created_at DESC
";
$notifications = $pdo->query($sql)->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <h3 style="font-size: 1.25rem;">Upcoming Exam Alerts (<?= count($notifications) ?>)</h3>
        <p style="color: var(--admin-muted); font-size: 0.85rem;">Publish official exam date schedules, registration deadlines, and vacancies.</p>
    </div>

    <div style="display: flex; gap: 10px;">
        <a href="<?= url('upcoming-exams.php') ?>" target="_blank" class="btn btn-secondary">
            <span>View Public Page ↗</span>
        </a>
        <a href="<?= url('admin/notification-new.php') ?>" class="btn btn-primary">
            <span>+ Post New Exam Alert</span>
        </a>
    </div>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Exam & Title</th>
                    <th>Category</th>
                    <th>Badge / Tag</th>
                    <th>Key Dates</th>
                    <th>Vacancies</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($notifications)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--admin-muted); padding: 40px;">
                            No exam notifications posted yet. Start by creating one!
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($notifications as $notif): 
                        $tagClass = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $notif['badge_tag']));
                    ?>
                        <tr>
                            <td>
                                <strong>
                                    <a href="<?= url('admin/notification-edit.php?id=' . $notif['id']) ?>">
                                        <?= e($notif['title']) ?>
                                    </a>
                                </strong>
                                <div style="font-size: 0.78rem; color: var(--admin-primary); font-weight: 600; margin-top: 2px;">
                                    <?= e($notif['exam_name']) ?>
                                    <?php if (!empty($notif['notification_pdf'])): ?>
                                        • <span style="color: #059669;">[PDF Attached]</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background: <?= e($notif['category_color'] ?? '#e2e8f0') ?>20; color: <?= e($notif['category_color'] ?? '#334155') ?>;">
                                    <?= e($notif['category_name'] ?: 'General') ?>
                                </span>
                            </td>
                            <td>
                                <span class="notif-tag tag-<?= $tagClass ?>" style="display: inline-block;">
                                    <?= e($notif['badge_tag']) ?>
                                </span>
                            </td>
                            <td style="font-size: 0.82rem;">
                                <div>📅 <strong><?= e($notif['exam_date'] ?: 'TBA') ?></strong></div>
                                <div style="color: var(--admin-muted);">⏳ Deadline: <?= e($notif['application_deadline'] ?: 'TBA') ?></div>
                            </td>
                            <td style="font-weight: 600; font-size: 0.85rem;">
                                <?= e($notif['total_vacancies'] ?: 'N/A') ?>
                            </td>
                            <td>
                                <a href="<?= url('admin/notifications.php?action=toggle&id=' . $notif['id']) ?>" class="badge badge-<?= $notif['is_active'] ? 'success' : 'danger' ?>" title="Click to toggle active status">
                                    <?= $notif['is_active'] ? '● Active (Live)' : '○ Inactive' ?>
                                </a>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <?php if (!empty($notif['slug'])): ?>
                                        <a href="<?= exam_url($notif['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm" title="View Public Post">
                                            View ↗
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= url('admin/notification-edit.php?id=' . $notif['id']) ?>" class="btn btn-secondary btn-sm" title="Edit">
                                        Edit
                                    </a>
                                    <a 
                                        href="<?= url('admin/notifications.php?action=delete&id=' . $notif['id']) ?>" 
                                        class="btn btn-secondary btn-sm confirm-delete"
                                        data-item="exam alert '<?= e($notif['title']) ?>'"
                                        style="color: #dc2626;"
                                        title="Delete"
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

<?php require_once __DIR__ . '/inc/admin-footer.php'; ?>
