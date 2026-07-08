<?php
/**
 * BeaconCMS — Front Controller
 *
 * Single entry point for all HTTP requests.
 * Bootstraps the application: session, config, autoloader, routing.
 */

// ── Error Reporting (disable display in production) ─────────────────────────
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// ── Session ─────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Base Path ───────────────────────────────────────────────────────────────
define('BASE_PATH', __DIR__);

// ── Config ──────────────────────────────────────────────────────────────────
$configPath = BASE_PATH . '/config.php';

if (!file_exists($configPath)) {
    // Config missing — redirect to installer
    header('Location: install.php');
    exit;
}

require_once $configPath;

// ── Autoloader ──────────────────────────────────────────────────────────────
spl_autoload_register(function (string $className): void {
    // Map of directory prefixes to search for class files
    $directories = [
        'core/',
        'models/',
        'models/cpt/',
        'controllers/',
        'controllers/cpt/',
    ];

    foreach ($directories as $directory) {
        $file = BASE_PATH . '/' . $directory . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ── Register Custom Post Types ──────────────────────────────────────────────
CPTRegistry::init();

// ── Dispatch ────────────────────────────────────────────────────────────────
$router = new Router();
$router->dispatch();
