<?php
/**
 * BeaconCMS Installer
 * One-time web installer — creates database, config file, and admin user.
 * Delete or rename this file after installation for security.
 */

// Prevent running if already installed
if (file_exists(__DIR__ . '/config.php')) {
    // If it's a dynamic config, check if DB_HOST is configured
    require_once __DIR__ . '/config.php';
    if (defined('DB_HOST')) {
        header('Location: /admin');
        exit;
    }
}

$step = (int)($_POST['step'] ?? $_GET['step'] ?? 1);
$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    
    // CSRF check
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = 'Invalid security token. Please try again.';
        $step = (int)$_POST['step'];
    }

    if ($step === 2 && empty($errors)) {
        // Test database connection
        $dbHost = trim($_POST['db_host'] ?? 'localhost');
        $dbName = trim($_POST['db_name'] ?? '');
        $dbUser = trim($_POST['db_user'] ?? '');
        $dbPass = $_POST['db_pass'] ?? '';

        try {
            $pdo = new PDO("mysql:host=$dbHost", $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            // Create database if not exists
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $_SESSION['db'] = compact('dbHost', 'dbName', 'dbUser', 'dbPass');
            $step = 3;
        } catch (PDOException $e) {
            $errors[] = 'Database connection failed: ' . $e->getMessage();
            $step = 2;
        }
    } elseif ($step === 3 && empty($errors)) {
        // Create config, tables, and admin user
        $db = $_SESSION['db'] ?? null;
        if (!$db) {
            $errors[] = 'Database session expired. Please start over.';
            $step = 2;
        } else {
            $siteName = trim($_POST['site_name'] ?? 'Beacon Hospital');
            $siteUrl = rtrim(trim($_POST['site_url'] ?? ''), '/');
            $adminUser = trim($_POST['admin_username'] ?? '');
            $adminEmail = trim($_POST['admin_email'] ?? '');
            $adminPass = $_POST['admin_password'] ?? '';

            if (empty($adminUser)) $errors[] = 'Admin username is required.';
            if (empty($adminEmail) || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid admin email is required.';
            if (strlen($adminPass) < 8) $errors[] = 'Password must be at least 8 characters.';

            if (empty($errors)) {
                try {
                    $pdo = new PDO(
                        "mysql:host={$db['dbHost']};dbname={$db['dbName']};charset=utf8mb4",
                        $db['dbUser'], $db['dbPass'],
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );

                    // Create tables
                    $sql = "
                    CREATE TABLE IF NOT EXISTS users (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        username VARCHAR(50) NOT NULL UNIQUE,
                        email VARCHAR(100) NOT NULL UNIQUE,
                        password VARCHAR(255) NOT NULL,
                        role ENUM('admin', 'editor', 'author') DEFAULT 'admin',
                        avatar VARCHAR(255),
                        two_factor_secret VARCHAR(32) NULL,
                        two_factor_enabled TINYINT(1) DEFAULT 0,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS posts (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        title VARCHAR(255) NOT NULL,
                        slug VARCHAR(255) UNIQUE NOT NULL,
                        content LONGTEXT,
                        excerpt TEXT,
                        featured_image VARCHAR(255),
                        status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
                        author_id INT,
                        seo_title VARCHAR(255),
                        seo_description TEXT,
                        seo_keywords VARCHAR(255),
                        og_image VARCHAR(255),
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS pages (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        title VARCHAR(255) NOT NULL,
                        slug VARCHAR(255) UNIQUE NOT NULL,
                        content LONGTEXT,
                        template VARCHAR(50) DEFAULT 'default',
                        status ENUM('draft', 'published') DEFAULT 'draft',
                        sort_order INT DEFAULT 0,
                        parent_id INT DEFAULT NULL,
                        seo_title VARCHAR(255),
                        seo_description TEXT,
                        og_image VARCHAR(255),
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS media (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        filename VARCHAR(255) NOT NULL,
                        original_name VARCHAR(255),
                        mime_type VARCHAR(100),
                        file_size INT,
                        path VARCHAR(500),
                        alt_text VARCHAR(255),
                        uploaded_by INT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS settings (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        setting_key VARCHAR(100) UNIQUE NOT NULL,
                        setting_value TEXT,
                        setting_group VARCHAR(50) DEFAULT 'general'
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS consultants (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        slug VARCHAR(255) UNIQUE NOT NULL,
                        photo VARCHAR(255),
                        specialty_id INT,
                        qualifications TEXT,
                        experience TEXT,
                        bio LONGTEXT,
                        clinic_hours TEXT,
                        contact_number VARCHAR(50),
                        email VARCHAR(100),
                        booking_link VARCHAR(500),
                        status ENUM('draft', 'published') DEFAULT 'draft',
                        sort_order INT DEFAULT 0,
                        seo_title VARCHAR(255),
                        seo_description TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS promotions (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        title VARCHAR(255) NOT NULL,
                        slug VARCHAR(255) UNIQUE NOT NULL,
                        description LONGTEXT,
                        featured_image VARCHAR(255),
                        start_date DATE,
                        end_date DATE,
                        status ENUM('draft', 'published', 'expired') DEFAULT 'draft',
                        seo_title VARCHAR(255),
                        seo_description TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS specialties (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        slug VARCHAR(255) UNIQUE NOT NULL,
                        description TEXT,
                        icon VARCHAR(100),
                        status ENUM('draft', 'published') DEFAULT 'draft',
                        sort_order INT DEFAULT 0,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS languages (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        code VARCHAR(10) UNIQUE NOT NULL,
                        name VARCHAR(100) NOT NULL,
                        native_name VARCHAR(100),
                        flag VARCHAR(50),
                        is_default TINYINT(1) DEFAULT 0,
                        is_active TINYINT(1) DEFAULT 1,
                        direction ENUM('ltr', 'rtl') DEFAULT 'ltr',
                        sort_order INT DEFAULT 0
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS translations (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        content_type VARCHAR(50) NOT NULL,
                        content_id INT NOT NULL,
                        language_code VARCHAR(10) NOT NULL,
                        translation_group VARCHAR(50) NOT NULL,
                        UNIQUE KEY unique_translation (content_type, language_code, translation_group)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS categories (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        slug VARCHAR(255) NOT NULL,
                        description TEXT,
                        taxonomy_type VARCHAR(50) NOT NULL,
                        parent_id INT DEFAULT NULL,
                        icon VARCHAR(100),
                        color VARCHAR(50),
                        sort_order INT DEFAULT 0,
                        UNIQUE KEY unique_slug_type (slug, taxonomy_type)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS category_items (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        category_id INT NOT NULL,
                        content_type VARCHAR(50) NOT NULL,
                        content_id INT NOT NULL,
                        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
                        UNIQUE KEY unique_item (category_id, content_type, content_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS forms (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        title VARCHAR(255) NOT NULL,
                        shortcode VARCHAR(100) UNIQUE NOT NULL,
                        fields_json JSON,
                        settings_json JSON,
                        status ENUM('active', 'inactive') DEFAULT 'active',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS form_entries (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        form_id INT NOT NULL,
                        entry_data_json JSON,
                        ip_address VARCHAR(45),
                        user_agent TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS form_connectors (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        form_id INT NOT NULL,
                        connector_type VARCHAR(50) NOT NULL,
                        config_json JSON,
                        is_active TINYINT(1) DEFAULT 1,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE,
                        UNIQUE KEY unique_form_connector (form_id, connector_type)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS post_types (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(100) NOT NULL,
                        singular_name VARCHAR(100) NOT NULL,
                        slug VARCHAR(50) NOT NULL UNIQUE,
                        icon VARCHAR(50) DEFAULT 'fa-file',
                        supports_json JSON,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS postmeta (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        content_type VARCHAR(50) NOT NULL,
                        content_id INT NOT NULL,
                        meta_key VARCHAR(100) NOT NULL,
                        meta_value LONGTEXT,
                        UNIQUE KEY unique_meta (content_type, content_id, meta_key)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS snippets (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        title VARCHAR(100) NOT NULL,
                        location ENUM('header', 'footer') DEFAULT 'header',
                        code_content TEXT,
                        is_active TINYINT(1) DEFAULT 1,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                    ";

                    // Execute each CREATE TABLE separately
                    foreach (explode(';', $sql) as $query) {
                        $query = trim($query);
                        if (!empty($query)) {
                            $pdo->exec($query);
                        }
                    }

                    $hashedPass = password_hash($adminPass, PASSWORD_BCRYPT, ['cost' => 12]);
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
                    $stmt->execute([$adminUser, $adminEmail, $hashedPass]);

                    // Insert default languages
                    $stmt = $pdo->prepare("INSERT INTO languages (code, name, native_name, flag, is_default, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute(['en', 'English', 'English', '🇬🇧', 1, 1]);
                    $stmt->execute(['ms', 'Malay', 'Bahasa Melayu', '🇲🇾', 0, 1]);
                    $stmt->execute(['zh', 'Chinese', '中文', '🇨🇳', 0, 1]);

                    // Insert default settings
                    $defaults = [
                        ['site_name', $siteName, 'general'],
                        ['site_description', 'Your Trusted Healthcare Partner', 'general'],
                        ['site_url', $siteUrl, 'general'],
                        ['seo_title_format', '{title} | {site_name}', 'seo'],
                        ['default_meta_description', '', 'seo'],
                        ['default_og_image', '', 'seo'],
                        ['google_analytics_id', '', 'seo'],
                        ['footer_text', '© ' . date('Y') . ' ' . $siteName . '. All rights reserved.', 'footer'],
                        ['footer_address', '', 'footer'],
                        ['footer_phone', '', 'footer'],
                        ['footer_email', '', 'footer'],
                        ['social_facebook', '', 'social'],
                        ['social_twitter', '', 'social'],
                        ['social_instagram', '', 'social'],
                        ['social_linkedin', '', 'social'],
                        ['social_youtube', '', 'social'],
                    ];
                    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)");
                    foreach ($defaults as $setting) {
                        $stmt->execute($setting);
                    }

                    // Generate config.php
                    $configContent = "<?php\n";
                    $configContent .= "/**\n * BeaconCMS Configuration\n * Generated on " . date('Y-m-d H:i:s') . "\n */\n\n";
                    $configContent .= "// Database\n";
                    $configContent .= "define('DB_HOST', " . var_export($db['dbHost'], true) . ");\n";
                    $configContent .= "define('DB_NAME', " . var_export($db['dbName'], true) . ");\n";
                    $configContent .= "define('DB_USER', " . var_export($db['dbUser'], true) . ");\n";
                    $configContent .= "define('DB_PASS', " . var_export($db['dbPass'], true) . ");\n\n";
                    $configContent .= "// Site\n";
                    $configContent .= "define('SITE_URL', " . var_export($siteUrl, true) . ");\n";
                    $configContent .= "define('SITE_NAME', " . var_export($siteName, true) . ");\n\n";
                    $configContent .= "// Environment\n";
                    $configContent .= "define('DEBUG', false);\n";
                    $configContent .= "define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB\n";
                    $configContent .= "define('BCMS_VERSION', '1.0.0');\n";

                    if (file_put_contents(__DIR__ . '/config.php', $configContent) === false) {
                        // If writing failed (e.g. read-only on Vercel) but config.php already exists,
                        // we treat it as successful and save the details in session to display env vars.
                        if (file_exists(__DIR__ . '/config.php')) {
                            $_SESSION['vercel_setup'] = [
                                'DB_HOST' => $db['dbHost'],
                                'DB_NAME' => $db['dbName'],
                                'DB_USER' => $db['dbUser'],
                                'DB_PASS' => $db['dbPass'],
                                'SITE_URL' => $siteUrl,
                                'SITE_NAME' => $siteName,
                            ];
                            $success = true;
                            $step = 4;
                        } else {
                            $errors[] = 'Failed to write config.php. Please check directory permissions.';
                        }
                    } else {
                        // Create uploads directory
                        if (!is_dir(__DIR__ . '/uploads')) {
                            mkdir(__DIR__ . '/uploads', 0755, true);
                        }
                        $success = true;
                        $step = 4;
                    }
                } catch (PDOException $e) {
                    $errors[] = 'Installation failed: ' . $e->getMessage();
                    $step = 3;
                }
            }
        }
    }
}

// Generate CSRF token
if (!isset($_SESSION)) session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$csrfToken = $_SESSION['csrf_token'];

// Auto-detect site URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$detectedUrl = "$protocol://$host$path";

// Check system requirements
$requirements = [
    ['PHP Version ≥ 8.0', version_compare(PHP_VERSION, '8.0.0', '>=')],
    ['PDO Extension', extension_loaded('pdo')],
    ['PDO MySQL Driver', extension_loaded('pdo_mysql')],
    ['GD Library (images)', extension_loaded('gd')],
    ['JSON Extension', extension_loaded('json')],
    ['Session Support', extension_loaded('session')],
    ['Writable Directory', is_writable(__DIR__)],
];
$allPassed = !in_array(false, array_column($requirements, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeaconCMS Installer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0f1117;
            background-image: radial-gradient(ellipse at 20% 50%, rgba(99, 102, 241, 0.15) 0%, transparent 60%),
                              radial-gradient(ellipse at 80% 20%, rgba(16, 185, 129, 0.1) 0%, transparent 50%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e2e8f0;
            padding: 2rem;
        }
        .installer {
            width: 100%;
            max-width: 600px;
        }
        .installer-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .installer-header .logo {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #6366f1, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .installer-header .logo i { -webkit-text-fill-color: #6366f1; margin-right: 0.5rem; }
        .installer-header p { color: #94a3b8; margin-top: 0.5rem; }
        
        /* Steps indicator */
        .steps {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }
        .step-dot {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 600;
            background: #1e2130;
            border: 2px solid #2d3148;
            color: #64748b;
            transition: all 0.3s;
        }
        .step-dot.active {
            background: linear-gradient(135deg, #6366f1, #818cf8);
            border-color: #6366f1;
            color: white;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
        }
        .step-dot.completed {
            background: #10b981;
            border-color: #10b981;
            color: white;
        }
        .step-line {
            width: 40px;
            height: 2px;
            background: #2d3148;
            align-self: center;
        }
        .step-line.completed { background: #10b981; }

        /* Card */
        .card {
            background: rgba(30, 33, 48, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(99, 102, 241, 0.1);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }
        .card h2 {
            font-size: 1.4rem;
            margin-bottom: 0.5rem;
            color: #f1f5f9;
        }
        .card .subtitle {
            color: #94a3b8;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        /* Form */
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            color: #cbd5e1;
            margin-bottom: 0.4rem;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            background: #0f1117;
            border: 1px solid #2d3148;
            border-radius: 8px;
            color: #e2e8f0;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .form-control:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        .form-control::placeholder { color: #475569; }
        .form-hint { font-size: 0.8rem; color: #64748b; margin-top: 0.3rem; }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            font-family: 'Inter', sans-serif;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #818cf8);
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
        }
        .btn-success {
            background: linear-gradient(135deg, #10b981, #34d399);
            color: white;
        }
        .btn-block { width: 100%; justify-content: center; }
        .btn-lg { padding: 1rem 2rem; font-size: 1.05rem; }

        /* Requirements */
        .req-list { list-style: none; }
        .req-list li {
            padding: 0.6rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid rgba(45, 49, 72, 0.5);
            font-size: 0.9rem;
        }
        .req-list li:last-child { border-bottom: none; }
        .req-pass { color: #10b981; }
        .req-fail { color: #ef4444; }

        /* Alerts */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }
        .alert-danger ul { margin: 0.5rem 0 0 1.25rem; }

        /* Success */
        .success-icon {
            text-align: center;
            font-size: 4rem;
            color: #10b981;
            margin-bottom: 1rem;
        }
        .success-message {
            text-align: center;
        }
        .success-message h2 { color: #10b981; margin-bottom: 0.5rem; }
        .success-message p { color: #94a3b8; margin-bottom: 1.5rem; }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        @media (max-width: 600px) {
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="installer">
        <div class="installer-header">
            <div class="logo"><i class="fa-solid fa-bolt"></i> BeaconCMS</div>
            <p>Installation Wizard</p>
        </div>

        <!-- Step Indicators -->
        <div class="steps">
            <div class="step-dot <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">
                <?php echo $step > 1 ? '<i class="fa-solid fa-check"></i>' : '1'; ?>
            </div>
            <div class="step-line <?php echo $step > 1 ? 'completed' : ''; ?>"></div>
            <div class="step-dot <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">
                <?php echo $step > 2 ? '<i class="fa-solid fa-check"></i>' : '2'; ?>
            </div>
            <div class="step-line <?php echo $step > 2 ? 'completed' : ''; ?>"></div>
            <div class="step-dot <?php echo $step >= 3 ? ($step > 3 ? 'completed' : 'active') : ''; ?>">
                <?php echo $step > 3 ? '<i class="fa-solid fa-check"></i>' : '3'; ?>
            </div>
            <div class="step-line <?php echo $step > 3 ? 'completed' : ''; ?>"></div>
            <div class="step-dot <?php echo $step >= 4 ? 'active' : ''; ?>">4</div>
        </div>

        <!-- Step 1: System Check -->
        <?php if ($step === 1): ?>
        <div class="card">
            <h2><i class="fa-solid fa-server"></i> System Requirements</h2>
            <p class="subtitle">Checking if your server meets the requirements</p>

            <ul class="req-list">
                <?php foreach ($requirements as [$name, $passed]): ?>
                    <li>
                        <i class="fa-solid <?php echo $passed ? 'fa-circle-check req-pass' : 'fa-circle-xmark req-fail'; ?>"></i>
                        <span><?php echo $name; ?></span>
                        <span style="margin-left:auto" class="<?php echo $passed ? 'req-pass' : 'req-fail'; ?>">
                            <?php echo $passed ? 'Pass' : 'Fail'; ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($allPassed): ?>
                <a href="?step=2" class="btn btn-primary btn-block" style="margin-top: 1.5rem;">
                    Continue <i class="fa-solid fa-arrow-right"></i>
                </a>
            <?php else: ?>
                <div class="alert alert-danger" style="margin-top: 1rem;">
                    <i class="fa-solid fa-exclamation-triangle"></i> Please fix the failed requirements before continuing.
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Step 2: Database -->
        <?php if ($step === 2): ?>
        <div class="card">
            <h2><i class="fa-solid fa-database"></i> Database Configuration</h2>
            <p class="subtitle">Enter your MySQL database credentials</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul><?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="step" value="2">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="form-group">
                    <label for="db_host">Database Host</label>
                    <input type="text" id="db_host" name="db_host" class="form-control" value="<?php echo htmlspecialchars($_POST['db_host'] ?? 'localhost'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="db_name">Database Name</label>
                    <input type="text" id="db_name" name="db_name" class="form-control" value="<?php echo htmlspecialchars($_POST['db_name'] ?? 'beaconcms'); ?>" required>
                    <p class="form-hint">Will be created automatically if it doesn't exist</p>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="db_user">Database User</label>
                        <input type="text" id="db_user" name="db_user" class="form-control" value="<?php echo htmlspecialchars($_POST['db_user'] ?? 'root'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="db_pass">Database Password</label>
                        <input type="password" id="db_pass" name="db_pass" class="form-control" value="">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                    Test Connection & Continue <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Step 3: Site Setup -->
        <?php if ($step === 3): ?>
        <div class="card">
            <h2><i class="fa-solid fa-gear"></i> Site Setup</h2>
            <p class="subtitle">Configure your site and create admin account</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul><?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="step" value="3">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="form-group">
                    <label for="site_name">Site Name</label>
                    <input type="text" id="site_name" name="site_name" class="form-control" value="<?php echo htmlspecialchars($_POST['site_name'] ?? 'Beacon Hospital'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="site_url">Site URL</label>
                    <input type="url" id="site_url" name="site_url" class="form-control" value="<?php echo htmlspecialchars($_POST['site_url'] ?? $detectedUrl); ?>" required>
                </div>

                <hr style="border-color: #2d3148; margin: 1.5rem 0;">
                <h3 style="font-size: 1rem; margin-bottom: 1rem; color: #94a3b8;">Admin Account</h3>

                <div class="form-group">
                    <label for="admin_username">Username</label>
                    <input type="text" id="admin_username" name="admin_username" class="form-control" value="<?php echo htmlspecialchars($_POST['admin_username'] ?? 'admin'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="admin_email">Email</label>
                    <input type="email" id="admin_email" name="admin_email" class="form-control" value="<?php echo htmlspecialchars($_POST['admin_email'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="admin_password">Password</label>
                    <input type="password" id="admin_password" name="admin_password" class="form-control" minlength="8" required>
                    <p class="form-hint">Minimum 8 characters</p>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fa-solid fa-rocket"></i> Install BeaconCMS
                </button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Step 4: Success -->
        <?php if ($step === 4): ?>
        <div class="card">
            <div class="success-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="success-message">
                <h2>Installation Complete!</h2>
                <p>BeaconCMS has been installed successfully. Your CMS is ready to use.</p>

                <?php if (isset($_SESSION['vercel_setup'])): ?>
                    <div style="text-align: left; margin: 1.5rem 0; padding: 1.5rem; background: #1e293b; border-radius: 8px; border: 1px solid #334155;">
                        <h4 style="color: #f59e0b; margin-bottom: 0.5rem;"><i class="fa-solid fa-circle-info"></i> Vercel Deployment Action Required</h4>
                        <p style="font-size: 0.9rem; color: #94a3b8; margin-bottom: 1rem;">
                            Since your Vercel filesystem is read-only, we could not save the config file directly. You <strong>MUST</strong> add these Environment Variables to your Vercel Dashboard for the site to work:
                        </p>
                        <table style="width: 100%; border-collapse: collapse; font-family: monospace; font-size: 0.85rem; color: #e2e8f0;">
                            <thead>
                                <tr style="border-bottom: 1px solid #334155; text-align: left;">
                                    <th style="padding: 0.5rem;">Key</th>
                                    <th style="padding: 0.5rem;">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($_SESSION['vercel_setup'] as $key => $val): ?>
                                    <tr style="border-bottom: 1px solid #1e293b;">
                                        <td style="padding: 0.5rem; font-weight: bold; color: #38bdf8;"><?php echo htmlspecialchars($key); ?></td>
                                        <td style="padding: 0.5rem; word-break: break-all;"><?php echo htmlspecialchars($val); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <a href="<?php echo htmlspecialchars($detectedUrl); ?>/admin/login" class="btn btn-success btn-lg btn-block">
                        <i class="fa-solid fa-sign-in-alt"></i> Login to Admin Panel
                    </a>
                <?php endif; ?>

                <p style="margin-top: 1.5rem; font-size: 0.8rem; color: #f59e0b;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <strong>Security:</strong> Delete or rename <code>install.php</code> after installation.
                </p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
