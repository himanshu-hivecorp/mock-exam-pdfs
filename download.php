<?php
/**
 * Mock Exam PDFs - Secure Direct Download Handler
 * Increments download counters and streams the PDF file with attachment headers.
 *
 * @package   MockExamPDFs
 * @author    Himanshu Ranjan Sahu <https://github.com/himanshu-hivecorp>
 * @copyright 2026 Himanshu Ranjan Sahu
 * @license   MIT License
 * @link      https://github.com/himanshu-hivecorp/mock-exam-pdfs
 */

require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
$type = trim($_GET['type'] ?? 'paper');

if (empty($slug)) {
    header("Location: " . url());
    exit;
}

$post = get_post_by_slug($slug, true);

if (!$post) {
    die("Error: The requested mock exam was not found.");
}

$relativeFilePath = ($type === 'solution' && !empty($post['solution_file'])) 
    ? $post['solution_file'] 
    : $post['pdf_file'];

$downloadName = ($type === 'solution' && !empty($post['solution_name']))
    ? $post['solution_name']
    : ($post['pdf_name'] ?: ($post['slug'] . '.pdf'));

// Ensure clean extension
if (!str_ends_with(strtolower($downloadName), '.pdf')) {
    $downloadName .= '.pdf';
}

$fullFilePath = __DIR__ . '/' . ltrim($relativeFilePath, '/');

if (!file_exists($fullFilePath) || !is_readable($fullFilePath)) {
    die("Error: The requested PDF file is currently unavailable on the server.");
}

// Increment download counter
increment_downloads((int)$post['id']);

// Clear all output buffers
if (ob_get_level()) {
    ob_end_clean();
}

// Stream PDF file with forced download headers
header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . addslashes($downloadName) . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($fullFilePath));

readfile($fullFilePath);
exit;
