<?php
/**
 * Mock Exam PDFs - Category Archive Page
 */

require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');

if (empty($slug)) {
    header("Location: " . url());
    exit;
}

// Redirect cleanly into index with category query
header("Location: " . url('?cat=' . urlencode($slug)));
exit;
