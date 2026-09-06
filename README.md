# Mock Exam PDFs 📚

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)](https://php.net)
[![Database](https://img.shields.io/badge/Database-SQLite%20(Zero--Config)-003B57?logo=sqlite&logoColor=white)](https://sqlite.org)
[![Deployment](https://img.shields.io/badge/Deployment-cPanel%20Drop--In%20Ready-F36E21?logo=cpanel&logoColor=white)](https://cpanel.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Author](https://img.shields.io/badge/Author-Himanshu%20Ranjan%20Sahu-blue?logo=github)](https://github.com/himanshu-hivecorp)

> **Free High-Quality Mock Exam Papers & Practice Tests CMS**  
> A lightweight, blazing-fast, self-contained PHP + SQLite Content Management System designed for competitive exam mock papers, previous year question PDFs, and recruitment notifications.

Built with pure Vanilla PHP, SQLite (PDO), and responsive Vanilla CSS. **Zero MySQL setup, zero external dependencies, zero composer build step required.** Just drop into any web hosting (cPanel `public_html` or Apache/Nginx VPS) and start publishing immediately.

---

## 👨‍💻 Author & Credits

- **Creator & Developer:** [**Himanshu Ranjan Sahu**](https://github.com/himanshu-hivecorp)
- **GitHub Profile:** [@himanshu-hivecorp](https://github.com/himanshu-hivecorp)
- **Repository:** [https://github.com/himanshu-hivecorp/mock-exam-pdfs](https://github.com/himanshu-hivecorp/mock-exam-pdfs)

---

## ✨ Key Features

### 1. 🚀 Zero-Config cPanel Drop-in Deployment
- Self-initializing SQLite database with WAL (Write-Ahead Logging) mode.
- Automatically creates database tables, initial categories, admin account, and default site settings on the first run.
- Upload directly into cPanel `public_html` via File Manager or FTP—no database creation, user permissions, or `phpMyAdmin` import needed.

### 2. 📝 Full Mock Exam Post Management
- Admin panel to publish, edit, and organize test series papers.
- Drag-and-drop PDF upload with automatic file sanitization and size detection.
- Optional cover image support.
- Built-in embedded PDF preview (`<iframe>` viewer) allowing students to practice directly on the page.
- Direct secure download streaming with automatic download counter increment.
- Real-time page view tracking.

### 3. 🔔 Upcoming Exam Notifications (Dedicated Posts)
- Dedicated announcement post pages for recruitment drives (e.g. UPSC, SSC, Banking, Railways, State PSCs).
- **Official Apply Link Privacy**: Official application links and notice PDFs are **only visible inside the post**, ensuring aspirants read full eligibility criteria and key dates before applying.
- Homepage and directory cards link directly to the post with `"Read Post & Apply Online →"`.
- Highlights strip for Exam Date, Last Date to Apply, and Total Vacancies.

### 4. 🔒 Secret & Secure Admin Area
- Public navigation, headers, footers, and mobile drawers are **100% free of admin links** to keep the administrative portal discreet.
- Admin dashboard accessible exclusively via `/admin/login.php`.
- CSRF token verification across all forms.
- Secure password hashing (`PASSWORD_BCRYPT`).
- Session-based authentication with auto-redirect guards.

### 5. 🖥️ Widescreen Fluid Responsive Design
- Optimized for all viewports from ultra-wide 2560px / 4K monitors down to smartphones.
- Mixed navigation bar with Exam Categories mega-dropdown, instant live search filter, and mobile sliding drawer with backdrop blur.
- Curated color palette: Deep Slate / Indigo / Emerald accents with smooth micro-animations.

### 6. 🔍 SEO & Social Sharing Suite
- Automated Schema.org structured data (`LearningResource` for mock tests, `NewsArticle` for exam notices).
- Dynamic OpenGraph (`og:title`, `og:image`, `og:description`) and Twitter Card tags.
- Canonical URL generation and clean Apache `.htaccess` rewrite rules (`/post/:slug`, `/exam/:slug`, `/category/:slug`, `/download/:slug`).
- Native one-click sharing buttons for WhatsApp, Telegram, X (Twitter), and clipboard copy.

---

## 📂 Project Structure

```
pdf website/
├── .htaccess                   # Clean URL rewrites & security headers
├── .gitignore                  # Git ignore rules
├── LICENSE                     # MIT License
├── README.md                   # Project documentation
├── index.php                   # Homepage (Hero, live search, upcoming exams, mock tests grid)
├── post.php                    # Single mock test paper view with PDF preview & download
├── exam.php                    # Dedicated upcoming exam notification post with official apply portal
├── upcoming-exams.php          # Directory of active competitive exam recruitment alerts
├── download.php                # Secure download counter and PDF streamer
├── category.php                # Category route handler
├── search.php                  # Search query route handler
├── data/                       # SQLite database folder (auto-created with 0755 permissions)
│   └── database.sqlite         # SQLite database file
├── uploads/                    # Uploaded mock exam PDFs and covers
│   ├── covers/
│   └── pdfs/
├── includes/                   # Core backend utilities
│   ├── db.php                  # PDO connection, auto-schema migration & seeds
│   ├── functions.php           # Helpers, queries, security, slug generator, and counters
│   ├── header.php              # Shared frontend navigation with category mega-dropdown
│   └── footer.php              # Shared frontend footer with author credits
├── assets/                     # Frontend styling & scripts
│   ├── css/style.css           # Modern design system & widescreen responsive CSS
│   └── js/main.js              # Interactive UI, live search filter, and mobile drawer
└── admin/                      # Secret Admin Panel
    ├── index.php               # Admin overview metrics dashboard
    ├── login.php               # Admin authentication portal
    ├── logout.php              # Session logout
    ├── posts.php               # Mock exam posts list & quick management
    ├── post-new.php            # Create new mock exam post with PDF upload
    ├── post-edit.php           # Edit mock exam post, metadata & replace PDF
    ├── notifications.php       # Manage upcoming exam alerts (toggle active/inactive)
    ├── notification-new.php    # Publish new upcoming exam post
    ├── notification-edit.php   # Edit upcoming exam post & official portal links
    ├── categories.php          # Exam categories manager with custom color badges
    ├── settings.php            # Site configuration, tagline, items per page & password change
    ├── inc/                    # Admin layout components
    │   ├── admin-header.php
    │   └── admin-footer.php
    └── assets/                 # Admin styles & scripts
```

---

## ⚡ Quick Start & Installation

### Option 1: Run Locally via PHP Built-in Server

1. **Clone the repository:**
   ```bash
   git clone https://github.com/himanshu-hivecorp/mock-exam-pdfs.git
   cd mock-exam-pdfs
   ```

2. **Start PHP server:**
   ```bash
   php -S 127.0.0.1:8000
   ```

3. Open your browser:
   - **Public Website:** [http://127.0.0.1:8000](http://127.0.0.1:8000)
   - **Admin Portal:** [http://127.0.0.1:8000/admin/login.php](http://127.0.0.1:8000/admin/login.php)

---

### Option 2: Deploy to cPanel (Shared Hosting / Apache)

1. Compress all files into a `.zip` archive (or clone directly via cPanel Git Version Control).
2. Open **cPanel > File Manager** and navigate to `public_html/` (or your subdomain folder).
3. Upload and extract the archive into `public_html/`.
4. Ensure the web server has write permissions for `data/` and `uploads/` directories (permissions `755` or `775`).
5. Open your domain in any browser: the database will auto-initialize seamlessly.

---

## 🔑 Default Admin Credentials

| Field | Default Value |
| :--- | :--- |
| **Login URL** | `/admin/login.php` |
| **Username** | `admin` |
| **Password** | `Admin@12345` |

> ⚠️ **Important:** Change the default admin password immediately in **Admin Panel > Settings > Change Password** before taking the site public.

---

## 📜 License

This project is open-source software licensed under the [MIT License](LICENSE).

Developed with ❤️ by [**Himanshu Ranjan Sahu**](https://github.com/himanshu-hivecorp).
