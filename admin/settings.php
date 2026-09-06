<?php
/**
 * Mock Exam PDFs - Admin Site & Profile Settings (admin/settings.php)
 */

$adminTitle = "Site & Profile Settings";
require_once __DIR__ . '/inc/admin-header.php';

$errors = [];
$successMsg = '';

// Handle General Settings Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'general') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $errors[] = 'Invalid security token.';
    } else {
        set_setting('site_name', trim($_POST['site_name'] ?? 'Mock Exam PDFs'));
        set_setting('site_tagline', trim($_POST['site_tagline'] ?? ''));
        set_setting('site_description', trim($_POST['site_description'] ?? ''));
        set_setting('contact_email', trim($_POST['contact_email'] ?? ''));
        set_setting('items_per_page', (string)max(6, (int)($_POST['items_per_page'] ?? 12)));
        set_setting('footer_text', trim($_POST['footer_text'] ?? ''));

        set_flash('success', 'Site settings updated successfully!');
        header("Location: " . url('admin/settings.php'));
        exit;
    }
}

// Handle Password Change Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'password') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $errors[] = 'Invalid security token.';
    } else {
        $currentPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$currentUser['id']]);
        $storedHash = $stmt->fetchColumn();

        if (!password_verify($currentPass, $storedHash)) {
            $errors[] = 'The current password you entered is incorrect.';
        } elseif (strlen($newPass) < 6) {
            $errors[] = 'New password must be at least 6 characters long.';
        } elseif ($newPass !== $confirmPass) {
            $errors[] = 'New password and confirmation do not match.';
        } else {
            $newHash = password_hash($newPass, PASSWORD_BCRYPT);
            $upStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $upStmt->execute([$newHash, $currentUser['id']]);

            set_flash('success', 'Admin password changed successfully! Please keep your new password safe.');
            header("Location: " . url('admin/settings.php'));
            exit;
        }
    }
}

$siteName = get_setting('site_name', 'Mock Exam PDFs');
$siteTagline = get_setting('site_tagline', 'Free High-Quality Mock Exam Papers & Practice Tests in PDF');
$siteDesc = get_setting('site_description', 'Download free competitive mock exam question papers and practice sets with solutions.');
$contactEmail = get_setting('contact_email', 'contact@mockexampdfs.com');
$itemsPerPage = get_setting('items_per_page', '12');
$footerText = get_setting('footer_text', '© ' . date('Y') . ' Mock Exam PDFs. All rights reserved.');
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

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- General Site Settings Card -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>General & SEO Settings</h3>
        </div>
        <div class="admin-card-body">
            <form method="POST" action="">
                <input type="hidden" name="form_type" value="general">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                <div class="form-group">
                    <label class="form-label" for="siteName">Website Title</label>
                    <input type="text" id="siteName" name="site_name" class="form-control" value="<?= e($siteName) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="siteTagline">Website Tagline</label>
                    <input type="text" id="siteTagline" name="site_tagline" class="form-control" value="<?= e($siteTagline) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="siteDesc">Default Meta Description (SEO)</label>
                    <textarea id="siteDesc" name="site_description" class="form-control" rows="3"><?= e($siteDesc) ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="contactEmail">Contact / Support Email</label>
                    <input type="email" id="contactEmail" name="contact_email" class="form-control" value="<?= e($contactEmail) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="itemsPerPage">Mock Papers Per Page</label>
                    <input type="number" id="itemsPerPage" name="items_per_page" class="form-control" value="<?= e($itemsPerPage) ?>" min="6" max="60">
                </div>

                <div class="form-group">
                    <label class="form-label" for="footerText">Footer Copyright Text</label>
                    <input type="text" id="footerText" name="footer_text" class="form-control" value="<?= e($footerText) ?>">
                </div>

                <button type="submit" class="btn btn-primary">
                    <span>💾 Save Site Settings</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Security & Password Change Card -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>Admin Credentials & Security</h3>
        </div>
        <div class="admin-card-body">
            <div style="background: #f8fafc; border: 1px solid var(--admin-border); border-radius: 8px; padding: 14px 16px; margin-bottom: 20px;">
                <div style="font-size: 0.85rem; color: var(--admin-muted);">Current Username</div>
                <div style="font-weight: 700; font-size: 1.05rem; color: var(--admin-text);"><?= e($currentUser['username']) ?></div>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="form_type" value="password">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                <div class="form-group">
                    <label class="form-label" for="currPass">Current Password <span class="required">*</span></label>
                    <input type="password" id="currPass" name="current_password" class="form-control" required placeholder="Enter current password">
                    <div class="form-hint">Default initial password is: <code>Admin@12345</code></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="newPass">New Password <span class="required">*</span></label>
                    <input type="password" id="newPass" name="new_password" class="form-control" required minlength="6" placeholder="At least 6 characters">
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirmPass">Confirm New Password <span class="required">*</span></label>
                    <input type="password" id="confirmPass" name="confirm_password" class="form-control" required minlength="6" placeholder="Repeat new password">
                </div>

                <button type="submit" class="btn btn-primary">
                    <span>🔒 Update Password</span>
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/inc/admin-footer.php'; ?>
