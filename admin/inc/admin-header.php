<?php
/**
 * Mock Exam PDFs - Admin Header Template
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_login();

$currentUser = current_user();
$currentScript = basename($_SERVER['SCRIPT_NAME']);
$siteName = get_setting('site_name', 'Mock Exam PDFs');
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($adminTitle ?? 'Admin Dashboard') ?> | <?= e($siteName) ?> CMS</title>
    <link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>?v=<?= time() ?>">
</head>
<body>

<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
            </div>
            <span>Mock Exam CMS</span>
        </div>

        <ul class="sidebar-menu">
            <li class="menu-label">Main</li>
            <li class="sidebar-item">
                <a href="<?= url('admin/index.php') ?>" class="sidebar-link <?= ($currentScript === 'index.php') ? 'active' : '' ?>">
                    <span>📊 Dashboard</span>
                </a>
            </li>

            <li class="menu-label">Mock Exam Posts</li>
            <li class="sidebar-item">
                <a href="<?= url('admin/posts.php') ?>" class="sidebar-link <?= ($currentScript === 'posts.php' || $currentScript === 'post-edit.php') ? 'active' : '' ?>">
                    <span>📑 All Mock Tests</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/post-new.php') ?>" class="sidebar-link <?= ($currentScript === 'post-new.php') ? 'active' : '' ?>">
                    <span>➕ Add New Mock Exam</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/categories.php') ?>" class="sidebar-link <?= ($currentScript === 'categories.php') ? 'active' : '' ?>">
                    <span>🏷️ Exam Categories</span>
                </a>
            </li>

            <li class="menu-label">Notifications & Alerts</li>
            <li class="sidebar-item">
                <a href="<?= url('admin/notifications.php') ?>" class="sidebar-link <?= (str_contains($currentScript, 'notification')) ? 'active' : '' ?>">
                    <span>📢 Exam Notifications</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url('admin/notification-new.php') ?>" class="sidebar-link <?= ($currentScript === 'notification-new.php') ? 'active' : '' ?>">
                    <span>➕ Post New Alert</span>
                </a>
            </li>

            <li class="menu-label">Configuration</li>
            <li class="sidebar-item">
                <a href="<?= url('admin/settings.php') ?>" class="sidebar-link <?= ($currentScript === 'settings.php') ? 'active' : '' ?>">
                    <span>⚙️ Site & SEO Settings</span>
                </a>
            </li>

            <li class="menu-label">External</li>
            <li class="sidebar-item">
                <a href="<?= url() ?>" target="_blank" class="sidebar-link">
                    <span>🌐 View Live Website ↗</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <div style="font-size: 0.85rem; color: #cbd5e1;">
                Logged in as <strong><?= e($currentUser['username']) ?></strong>
            </div>
            <a href="<?= url('admin/logout.php') ?>" title="Logout" style="color: #ef4444; font-size: 0.85rem; font-weight: 600;">
                Logout
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="admin-main">
        <!-- Topbar -->
        <header class="admin-topbar">
            <div class="topbar-left">
                <h2 style="font-size: 1.25rem;"><?= e($adminTitle ?? 'Dashboard') ?></h2>
            </div>

            <div class="topbar-right">
                <a href="<?= url('admin/post-new.php') ?>" class="btn btn-primary btn-sm">
                    <span>+ Add Mock Exam</span>
                </a>
                <a href="<?= url('admin/logout.php') ?>" class="btn btn-secondary btn-sm">
                    <span>Logout</span>
                </a>
            </div>
        </header>

        <!-- Admin Body -->
        <div class="admin-body">
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> alert-auto-dismiss">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>
