<?php
/**
 * Mock Exam PDFs - Admin Login Page
 */

require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    header("Location: " . url('admin/index.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $result = admin_login($username, $password);
        if ($result['success']) {
            set_flash('success', 'Welcome back, ' . htmlspecialchars($username) . '!');
            header("Location: " . url('admin/index.php'));
            exit;
        } else {
            $error = $result['error'];
        }
    }
}

$csrfToken = generate_csrf_token();
$siteName = get_setting('site_name', 'Mock Exam PDFs');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | <?= e($siteName) ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>?v=<?= time() ?>">
</head>
<body class="login-body">

<div class="login-card">
    <div style="text-align: center; margin-bottom: 24px;">
        <div class="sidebar-icon" style="margin: 0 auto 12px; width: 48px; height: 48px;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
        </div>
        <h2 style="font-size: 1.5rem; margin-bottom: 4px;">Admin Portal</h2>
        <p style="color: var(--admin-muted); font-size: 0.875rem;">Sign in to publish and manage mock exams</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

        <div class="form-group">
            <label class="form-label" for="username">Username</label>
            <input type="text" id="username" name="username" class="form-control" required autofocus placeholder="admin" value="<?= e($_POST['username'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; margin-top: 8px;">
            Sign In to Dashboard →
        </button>
    </form>

    <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--admin-border); font-size: 0.8rem; color: var(--admin-muted); text-align: center;">
        <p>Default credentials: <strong>admin</strong> / <strong>Admin@12345</strong></p>
        <p style="margin-top: 4px;"><a href="<?= url() ?>">← Return to Website</a></p>
    </div>
</div>

</body>
</html>
