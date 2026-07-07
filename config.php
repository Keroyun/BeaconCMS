<?php
/**
 * BeaconCMS Configuration
 * Supports environment variables for Vercel deployment.
 */

$dbHost = getenv('DB_HOST');

// Jika DB_HOST tidak ditetapkan, kita anggap sistem belum dipasang (act as missing config)
if (!$dbHost) {
    if (basename($_SERVER['SCRIPT_NAME']) !== 'install.php') {
        header('Location: install.php');
        exit;
    }
    return;
}

// Database
define('DB_HOST', $dbHost);
define('DB_NAME', getenv('DB_NAME') ?: 'beaconcms');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Site
define('SITE_URL', getenv('SITE_URL') ?: 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
define('SITE_NAME', getenv('SITE_NAME') ?: 'BeaconCMS');

// Environment
define('DEBUG', getenv('DEBUG') === 'true');
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('BCMS_VERSION', '1.0.0');
