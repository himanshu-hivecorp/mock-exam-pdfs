<?php
/**
 * Mock Exam PDFs - Search Route Handler
 */

require_once __DIR__ . '/includes/functions.php';

$query = trim($_GET['q'] ?? '');

if (empty($query)) {
    header("Location: " . url());
    exit;
}

header("Location: " . url('?q=' . urlencode($query)));
exit;
