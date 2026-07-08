<?php
/**
 * BeaconCMS Production Configuration for Vercel
 *
 * EDIT HERE in Vercel Project Settings > Environment Variables.
 * DO NOT COMMIT real database passwords or API secrets into this file.
 */

// Database
define('DB_HOST', getenv('DB_HOST') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: '');
define('DB_USER', getenv('DB_USER') ?: '');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Site
define('SITE_URL', rtrim(getenv('SITE_URL') ?: 'https://example.com', '/'));
define('SITE_NAME', getenv('SITE_NAME') ?: 'Beacon Hospital');

// Environment
define('DEBUG', filter_var(getenv('DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN));
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);
define('BCMS_VERSION', '1.0.0');

if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
}

ini_set('log_errors', '1');
