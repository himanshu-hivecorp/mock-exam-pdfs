<?php
/**
 * Mock Exam PDFs - SQLite Database Connection & Auto-Initializer
 * Works out-of-the-box on cPanel Apache with zero configuration.
 *
 * @package   MockExamPDFs
 * @author    Himanshu Ranjan Sahu <https://github.com/himanshu-hivecorp>
 * @copyright 2026 Himanshu Ranjan Sahu
 * @license   MIT License
 * @link      https://github.com/himanshu-hivecorp/mock-exam-pdfs
 */

if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

$dataDir = __DIR__ . '/../data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

$dbPath = $dataDir . '/database.sqlite';
$needsInit = !file_exists($dbPath) || filesize($dbPath) === 0;

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON;');
    $pdo->exec('PRAGMA journal_mode = WAL;');
} catch (PDOException $e) {
    die("Database Connection Failed: " . htmlspecialchars($e->getMessage()));
}

if ($needsInit) {
    initDatabase($pdo);
}

/**
 * Initialize SQLite tables and default seed data
 */
function initDatabase(PDO $pdo): void {
    // 1. Users Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            email TEXT,
            role TEXT DEFAULT 'admin',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // 2. Categories Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            description TEXT,
            color TEXT DEFAULT '#4f46e5',
            icon TEXT DEFAULT 'file-text',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // 3. Posts Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            category_id INTEGER,
            excerpt TEXT,
            content TEXT,
            pdf_file TEXT NOT NULL,
            pdf_name TEXT,
            pdf_size INTEGER DEFAULT 0,
            solution_file TEXT,
            solution_name TEXT,
            cover_image TEXT,
            exam_year TEXT,
            difficulty TEXT DEFAULT 'Medium',
            duration_mins INTEGER DEFAULT 120,
            total_questions INTEGER DEFAULT 100,
            total_marks INTEGER DEFAULT 200,
            negative_marking TEXT DEFAULT '1/3rd (0.33) marks',
            downloads_count INTEGER DEFAULT 0,
            views_count INTEGER DEFAULT 0,
            is_featured INTEGER DEFAULT 0,
            status TEXT DEFAULT 'published',
            meta_title TEXT,
            meta_description TEXT,
            meta_keywords TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        );
    ");

    // 4. Settings Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT
        );
    ");

    // 5. Exam Notifications Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS exam_notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            exam_name TEXT NOT NULL,
            category_id INTEGER,
            exam_date TEXT,
            application_deadline TEXT,
            total_vacancies TEXT,
            official_link TEXT,
            notification_pdf TEXT,
            notification_pdf_name TEXT,
            details TEXT,
            content TEXT,
            badge_tag TEXT DEFAULT 'Registration Open',
            is_active INTEGER DEFAULT 1,
            views_count INTEGER DEFAULT 0,
            meta_title TEXT,
            meta_description TEXT,
            meta_keywords TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        );
    ");

    // Indexes for high performance
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_posts_slug ON posts(slug);");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_posts_status ON posts(status);");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_posts_cat ON posts(category_id);");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_cat_slug ON categories(slug);");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_notif_active ON exam_notifications(is_active);");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_notif_slug ON exam_notifications(slug);");

    // Seed Admin user (Username: admin, Password: Admin@12345)
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO users (username, password_hash, email, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        'admin',
        password_hash('Admin@12345', PASSWORD_BCRYPT),
        'admin@mockexampdfs.local',
        'admin'
    ]);

    // Seed Default Settings
    $defaultSettings = [
        'site_name' => 'Mock Exam PDFs',
        'site_tagline' => 'Free High-Quality Mock Exam Papers & Practice Tests in PDF',
        'site_description' => 'Download free competitive mock exam question papers, full practice sets, previous year papers, and detailed solution keys in PDF format.',
        'contact_email' => 'support@mockexampdfs.com',
        'items_per_page' => '12',
        'enable_pdf_preview' => '1',
        'footer_text' => '© ' . date('Y') . ' Mock Exam PDFs. All rights reserved. Free resources for competitive exam aspirants.'
    ];

    $stmtSet = $pdo->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)");
    foreach ($defaultSettings as $k => $v) {
        $stmtSet->execute([$k, $v]);
    }

    // Seed Categories
    $categories = [
        ['UPSC & Civil Services', 'upsc-civil-services', 'Civil Services, IAS, IPS, IFS Prelims & Mains mock tests', '#4f46e5', 'award'],
        ['SSC & State PSC', 'ssc-state-psc', 'SSC CGL, CHSL, MTS, and State Public Service commission test series', '#059669', 'book-open'],
        ['Banking & Insurance', 'banking-insurance', 'IBPS PO, SBI PO, Clerk, RBI Grade B, and LIC AAO mock tests', '#2563eb', 'briefcase'],
        ['Engineering & GATE', 'engineering-gate', 'GATE, IES/ESE, SSC JE, and State AE/JE exam papers', '#d97706', 'cpu'],
        ['Medical & NEET', 'medical-neet', 'NEET UG, AIIMS, and State Medical entrance mock papers', '#dc2626', 'activity'],
        ['Management & CAT', 'management-cat', 'CAT, XAT, MAT, CMAT management aptitude practice tests', '#7c3aed', 'pie-chart'],
        ['Defense & Police', 'defense-police', 'NDA, CDS, AFCAT, CAPF, and Police SI test papers', '#0891b2', 'shield'],
        ['Railways RRB', 'railways-rrb', 'RRB NTPC, Group D, ALP, and Technician mock exam papers', '#ea580c', 'compass']
    ];

    $stmtCat = $pdo->prepare("INSERT OR IGNORE INTO categories (name, slug, description, color, icon) VALUES (?, ?, ?, ?, ?)");
    foreach ($categories as $cat) {
        $stmtCat->execute($cat);
    }
}
