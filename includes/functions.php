<?php
/**
 * Mock Exam PDFs - Helper Functions & Core Utilities
 *
 * @package   MockExamPDFs
 * @author    Himanshu Ranjan Sahu <https://github.com/himanshu-hivecorp>
 * @copyright 2026 Himanshu Ranjan Sahu
 * @license   MIT License
 * @link      https://github.com/himanshu-hivecorp/mock-exam-pdfs
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

/**
 * Sanitize output for HTML
 */
function e(?string $str): string {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a clean URL slug from title
 */
function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a-' . time() : $text;
}

/**
 * Get dynamic application base URL
 */
function get_base_url(): string {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    
    $protocol = $isHttps ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Determine subdirectory if any
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $dirName = str_replace('\\', '/', dirname($scriptName));
    
    // If inside admin/ or includes/, go to root
    $dirName = preg_replace('~/(admin|includes)$~', '', $dirName);
    $dirName = rtrim($dirName, '/');
    
    return $protocol . $host . $dirName;
}

/**
 * Return absolute or root-relative URL for a path
 */
function url(string $path = ''): string {
    $base = get_base_url();
    $path = ltrim($path, '/');
    return $base . ($path !== '' ? '/' . $path : '');
}

/**
 * Generate post URL
 */
function post_url(string $slug): string {
    return url('post.php?slug=' . urlencode($slug));
}

/**
 * Generate category URL
 */
function category_url(string $slug): string {
    return url('category.php?slug=' . urlencode($slug));
}

/**
 * Generate download URL
 */
function download_url(string $slug, string $type = 'paper'): string {
    return url('download.php?slug=' . urlencode($slug) . '&type=' . urlencode($type));
}

/**
 * Format bytes into human readable format (KB, MB, GB)
 */
function format_bytes(int $bytes, int $precision = 1): string {
    if ($bytes <= 0) return '0 KB';
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Format date nicely
 */
function format_date(?string $dateStr, string $format = 'M j, Y'): string {
    if (!$dateStr) return '';
    $timestamp = strtotime($dateStr);
    return $timestamp ? date($format, $timestamp) : '';
}

/**
 * Get setting value from database
 */
function get_setting(string $key, string $default = ''): string {
    global $pdo;
    static $settingsCache = null;

    if ($settingsCache === null) {
        $stmt = $pdo->query("SELECT key, value FROM settings");
        $settingsCache = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    }

    return $settingsCache[$key] ?? $default;
}

/**
 * Update or insert setting value
 */
function set_setting(string $key, string $value): bool {
    global $pdo;
    $stmt = $pdo->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)");
    return $stmt->execute([$key, $value]);
}

/**
 * CSRF Protection
 */
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Flash messages
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Secure file upload handler
 */
function handle_file_upload(array $file, string $subDir, array $allowedExtensions = ['pdf'], int $maxSizeBytes = 52428800): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE specified in the form.',
            UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder on the server.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.'
        ];
        return ['success' => false, 'error' => $uploadErrors[$file['error']] ?? 'Unknown upload error.'];
    }

    if ($file['size'] > $maxSizeBytes) {
        return ['success' => false, 'error' => 'File size exceeds maximum limit of ' . format_bytes($maxSizeBytes) . '.'];
    }

    $originalName = basename($file['name']);
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions, true)) {
        return ['success' => false, 'error' => 'Invalid file extension: .' . $extension . '. Allowed: ' . implode(', ', $allowedExtensions)];
    }

    // Secondary MIME inspection
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = [
        'pdf'  => ['application/pdf', 'application/x-pdf', 'application/acrobat', 'applications/vnd.pdf', 'text/pdf'],
        'jpg'  => ['image/jpeg', 'image/pjpeg'],
        'jpeg' => ['image/jpeg', 'image/pjpeg'],
        'png'  => ['image/png', 'image/x-png'],
        'webp' => ['image/webp']
    ];

    $isValidMime = false;
    if (isset($allowedMimes[$extension]) && in_array($mimeType, $allowedMimes[$extension], true)) {
        $isValidMime = true;
    }
    // For PDFs, occasionally some servers report octet-stream, check PDF header magic bytes %PDF-
    if (!$isValidMime && $extension === 'pdf') {
        $handle = fopen($file['tmp_name'], 'rb');
        $header = fread($handle, 5);
        fclose($handle);
        if ($header === '%PDF-') {
            $isValidMime = true;
        }
    }

    if (!$isValidMime) {
        return ['success' => false, 'error' => 'Uploaded file MIME type is invalid or corrupted.'];
    }

    $targetDir = __DIR__ . '/../uploads/' . trim($subDir, '/');
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $safeFilename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
    $destination = $targetDir . '/' . $safeFilename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'error' => 'Failed to save uploaded file to destination.'];
    }

    return [
        'success'       => true,
        'filename'      => $safeFilename,
        'original_name' => $originalName,
        'size'          => $file['size'],
        'path'          => 'uploads/' . trim($subDir, '/') . '/' . $safeFilename
    ];
}

/**
 * Increment download counter safely
 */
function increment_downloads(int $postId): void {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE posts SET downloads_count = downloads_count + 1 WHERE id = ?");
    $stmt->execute([$postId]);
}

/**
 * Increment view counter
 */
function increment_views(int $postId): void {
    global $pdo;
    $sessionKey = 'viewed_post_' . $postId;
    if (empty($_SESSION[$sessionKey])) {
        $_SESSION[$sessionKey] = true;
        $stmt = $pdo->prepare("UPDATE posts SET views_count = views_count + 1 WHERE id = ?");
        $stmt->execute([$postId]);
    }
}

/**
 * Fetch a single post by slug
 */
function get_post_by_slug(string $slug, bool $publishedOnly = true): ?array {
    global $pdo;
    $sql = "
        SELECT p.*, c.name AS category_name, c.slug AS category_slug, c.color AS category_color
        FROM posts p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.slug = ? " . ($publishedOnly ? "AND p.status = 'published'" : "") . "
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$slug]);
    $post = $stmt->fetch();
    return $post ?: null;
}

/**
 * Fetch posts with flexible options
 */
function get_posts(array $options = []): array {
    global $pdo;

    $categorySlug = $options['category_slug'] ?? null;
    $categoryId   = $options['category_id'] ?? null;
    $status       = $options['status'] ?? 'published';
    $search       = $options['search'] ?? null;
    $difficulty   = $options['difficulty'] ?? null;
    $isFeatured   = $options['is_featured'] ?? null;
    $limit        = (int)($options['limit'] ?? 12);
    $offset       = (int)($options['offset'] ?? 0);
    $orderBy      = $options['order_by'] ?? 'created_at DESC';

    $where = [];
    $params = [];

    if ($status !== 'all') {
        $where[] = "p.status = ?";
        $params[] = $status;
    }

    if ($categorySlug) {
        $where[] = "c.slug = ?";
        $params[] = $categorySlug;
    } elseif ($categoryId) {
        $where[] = "p.category_id = ?";
        $params[] = $categoryId;
    }

    if ($difficulty) {
        $where[] = "p.difficulty = ?";
        $params[] = $difficulty;
    }

    if ($isFeatured !== null) {
        $where[] = "p.is_featured = ?";
        $params[] = (int)$isFeatured;
    }

    if ($search) {
        $where[] = "(p.title LIKE ? OR p.excerpt LIKE ? OR p.content LIKE ? OR p.exam_year LIKE ?)";
        $s = '%' . $search . '%';
        $params[] = $s;
        $params[] = $s;
        $params[] = $s;
        $params[] = $s;
    }

    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    $allowedOrders = [
        'created_at DESC' => 'p.created_at DESC',
        'created_at ASC'  => 'p.created_at ASC',
        'downloads DESC'  => 'p.downloads_count DESC',
        'views DESC'      => 'p.views_count DESC',
        'title ASC'       => 'p.title ASC'
    ];
    $orderSql = $allowedOrders[$orderBy] ?? 'p.created_at DESC';

    // Total Count Query
    $countSql = "SELECT COUNT(*) FROM posts p LEFT JOIN categories c ON p.category_id = c.id $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalCount = (int)$countStmt->fetchColumn();

    // Data Query
    $sql = "
        SELECT p.*, c.name AS category_name, c.slug AS category_slug, c.color AS category_color
        FROM posts p
        LEFT JOIN categories c ON p.category_id = c.id
        $whereClause
        ORDER BY $orderSql
        LIMIT $limit OFFSET $offset
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    return [
        'items'       => $items,
        'total'       => $totalCount,
        'limit'       => $limit,
        'offset'      => $offset,
        'total_pages' => $limit > 0 ? (int)ceil($totalCount / $limit) : 1
    ];
}

/**
 * Fetch all categories with post count
 */
function get_categories(): array {
    global $pdo;
    $sql = "
        SELECT c.*, COUNT(p.id) AS post_count
        FROM categories c
        LEFT JOIN posts p ON c.id = p.category_id AND p.status = 'published'
        GROUP BY c.id
        ORDER BY post_count DESC, c.name ASC
    ";
    return $pdo->query($sql)->fetchAll();
}

/**
 * Fetch admin statistics
 */
function get_admin_stats(): array {
    global $pdo;
    $totalPosts = (int)$pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
    $publishedPosts = (int)$pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
    $draftPosts = (int)$pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'draft'")->fetchColumn();
    $totalDownloads = (int)$pdo->query("SELECT COALESCE(SUM(downloads_count), 0) FROM posts")->fetchColumn();
    $totalViews = (int)$pdo->query("SELECT COALESCE(SUM(views_count), 0) FROM posts")->fetchColumn();
    $totalCategories = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $totalNotifications = (int)$pdo->query("SELECT COUNT(*) FROM exam_notifications WHERE is_active = 1")->fetchColumn();

    return [
        'total_posts'         => $totalPosts,
        'published_posts'     => $publishedPosts,
        'draft_posts'         => $draftPosts,
        'total_downloads'     => $totalDownloads,
        'total_views'         => $totalViews,
        'total_categories'    => $totalCategories,
        'total_notifications' => $totalNotifications
    ];
}

/**
 * Fetch upcoming exam notifications
 */
function get_exam_notifications(int $limit = 6, bool $activeOnly = true): array {
    global $pdo;
    $where = $activeOnly ? "WHERE n.is_active = 1" : "";
    $sql = "
        SELECT n.*, c.name AS category_name, c.color AS category_color, c.slug AS category_slug
        FROM exam_notifications n
        LEFT JOIN categories c ON n.category_id = c.id
        $where
        ORDER BY n.created_at DESC
        LIMIT ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$limit]);
    return $stmt->fetchAll() ?: [];
}

/**
 * Generate single exam notification post URL
 */
function exam_url(string $slug): string {
    return url('exam.php?slug=' . urlencode($slug));
}

/**
 * Fetch a single exam notification post by slug
 */
function get_exam_notification_by_slug(string $slug, bool $activeOnly = true): ?array {
    global $pdo;
    $where = $activeOnly ? "AND n.is_active = 1" : "";
    $sql = "
        SELECT n.*, c.name AS category_name, c.color AS category_color, c.slug AS category_slug
        FROM exam_notifications n
        LEFT JOIN categories c ON n.category_id = c.id
        WHERE n.slug = ? $where
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$slug]);
    $notif = $stmt->fetch();
    return $notif ?: null;
}

/**
 * Increment view count on exam notification post
 */
function increment_notification_views(int $notifId): void {
    global $pdo;
    $sessionKey = 'viewed_exam_' . $notifId;
    if (empty($_SESSION[$sessionKey])) {
        $_SESSION[$sessionKey] = true;
        $stmt = $pdo->prepare("UPDATE exam_notifications SET views_count = views_count + 1 WHERE id = ?");
        $stmt->execute([$notifId]);
    }
}

