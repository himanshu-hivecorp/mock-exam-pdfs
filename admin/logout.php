<?php
/**
 * Mock Exam PDFs - Admin Logout
 */

require_once __DIR__ . '/../includes/auth.php';

admin_logout();
set_flash('info', 'You have been successfully logged out.');
header("Location: " . url('admin/login.php'));
exit;
