<?php
/**
 * Mock Exam PDFs - Authentication & Admin Security Handler
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/**
 * Check if current user is logged in as admin
 */
function is_logged_in(): bool {
    return !empty($_SESSION['admin_user_id']) && !empty($_SESSION['admin_logged_in']);
}

/**
 * Protect admin routes
 */
function require_login(): void {
    if (!is_logged_in()) {
        set_flash('danger', 'Please log in to access the admin dashboard.');
        $loginUrl = url('admin/login.php');
        header("Location: $loginUrl");
        exit;
    }
}

/**
 * Get logged-in user record
 */
function current_user(): ?array {
    global $pdo;
    if (!is_logged_in()) {
        return null;
    }
    $stmt = $pdo->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['admin_user_id']]);
    return $stmt->fetch() ?: null;
}

/**
 * Attempt admin login
 */
function admin_login(string $username, string $password): array {
    global $pdo;

    $username = trim($username);
    if (empty($username) || empty($password)) {
        return ['success' => false, 'error' => 'Please provide both username and password.'];
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'error' => 'Invalid username or password.'];
    }

    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);

    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_user_id'] = $user['id'];
    $_SESSION['admin_username'] = $user['username'];
    $_SESSION['admin_role'] = $user['role'];

    return ['success' => true, 'user' => $user];
}

/**
 * Logout admin
 */
function admin_logout(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}
