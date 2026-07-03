<?php
/**
 * View — Template Rendering & Helper Functions
 *
 * Renders PHP templates from the views/ directory using output buffering
 * and provides global helper functions for escaping, URLs, and flash
 * messages.
 */
class View
{
    // ── Rendering ───────────────────────────────────────────────────────────

    /**
     * Render a view template with the given data.
     *
     * @param string               $template  Dot-or-slash path relative to views/
     *                                         e.g. "admin/posts/index" or "frontend/home"
     * @param array<string,mixed>  $data      Variables to extract into the template scope
     */
    public static function render(string $template, array $data = []): void
    {
        // Convert dots to directory separators if used
        $template = str_replace('.', '/', $template);
        $file     = BASE_PATH . '/views/' . $template . '.php';

        if (!file_exists($file)) {
            http_response_code(500);
            error_log("View not found: {$file}");
            echo "<!-- View '{$template}' not found -->";
            return;
        }

        // Extract variables into local scope
        extract($data);

        ob_start();
        require $file;
        $content = ob_get_clean();

        // Parse all shortcodes
        if (class_exists('Shortcodes')) {
            $content = Shortcodes::parse($content);
        }

        echo $content;
    }

    // ── Output Escaping ─────────────────────────────────────────────────────

    /**
     * Shorthand for htmlspecialchars — prevents XSS in HTML output.
     */
    public static function he(?string $string): string
    {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }

    // ── URL Helpers ─────────────────────────────────────────────────────────

    /**
     * Return the full URL to a static asset file.
     *
     * @param string $path  e.g. "css/style.css" or "js/app.js"
     */
    public static function asset(string $path): string
    {
        $base = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
        return $base . '/assets/' . ltrim($path, '/');
    }

    /**
     * Return the full site URL for any path.
     *
     * @param string $path  e.g. "/admin/posts" or "blog/hello-world"
     */
    public static function url(string $path = ''): string
    {
        $base = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
        return $base . '/' . ltrim($path, '/');
    }

    // ── Flash Messages ──────────────────────────────────────────────────────

    /**
     * Retrieve old form input from the session (for repopulating forms after
     * validation failure).
     *
     * @param string $field   Form field name
     * @param string $default Fallback value
     */
    public static function old(string $field, string $default = ''): string
    {
        return $_SESSION['_old_input'][$field] ?? $default;
    }

    /**
     * Get (and clear) a flash message from the session.
     *
     * @return mixed|null
     */
    public static function flash(string $key): mixed
    {
        if (isset($_SESSION['_flash'][$key])) {
            $value = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
            return $value;
        }
        return null;
    }

    /**
     * Set a flash message that will survive exactly one subsequent request.
     */
    public static function setFlash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Store the current POST data in the session so forms can be
     * repopulated after a validation error redirect.
     */
    public static function flashOldInput(array $data): void
    {
        $_SESSION['_old_input'] = $data;
    }

    /**
     * Clear old input after it has been consumed.
     */
    public static function clearOldInput(): void
    {
        unset($_SESSION['_old_input']);
    }
}

// ── Global helper functions (available in all templates) ────────────────────

if (!function_exists('he')) {
    /** Shorthand for View::he() — escape HTML output. */
    function he(?string $string): string
    {
        return View::he($string);
    }
}

if (!function_exists('asset')) {
    /** Shorthand for View::asset() */
    function asset(string $path): string
    {
        return View::asset($path);
    }
}

if (!function_exists('url')) {
    /** Shorthand for View::url() */
    function url(string $path = ''): string
    {
        return View::url($path);
    }
}
