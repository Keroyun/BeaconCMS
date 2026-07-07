<?php
/**
 * Router — URL Routing & Dispatch
 *
 * Parses the incoming URL, matches it against a table of route patterns,
 * enforces authentication where required, and dispatches to the
 * appropriate controller method.
 */
class Router
{
    /** @var array<int,array{method:string,pattern:string,handler:string,auth:bool}> */
    private array $routes = [];

    public function __construct()
    {
        Language::init();
        $this->registerRoutes();
    }

    // ── Route Registration ──────────────────────────────────────────────────

    private function registerRoutes(): void
    {
        // ── Public (frontend) routes ────────────────────────────────────────
        $this->get('',                          'FrontendController@home');
        $this->get('blog',                      'FrontendController@blogList');
        $this->get('blog/{slug}',               'FrontendController@blogSingle');
        $this->get('page/{slug}',               'FrontendController@page');
        $this->get('doctors',                   'FrontendController@consultantList');
        $this->get('doctors/{slug}',            'FrontendController@consultantSingle');
        $this->get('promotions',                'FrontendController@promotionList');
        $this->get('promotions/{slug}',         'FrontendController@promotionSingle');
        $this->get('specialties',               'FrontendController@specialtyList');
        $this->get('specialties/{slug}',        'FrontendController@specialtySingle');

        // ── Sitemap (handled by sitemap.php via .htaccess, not routed) ─────

        // ── Auth routes ─────────────────────────────────────────────────────
        $this->get('admin/login',               'AuthController@login');
        $this->post('admin/login',              'AuthController@login');
        $this->get('admin/logout',              'AuthController@logout');

        // ── Admin dashboard ─────────────────────────────────────────────────
        $this->get('admin',                     'AdminController@dashboard',    true);

        // ── CRUD: Posts ─────────────────────────────────────────────────────
        $this->crud('admin/posts',              'PostController');

        // ── CRUD: Pages ─────────────────────────────────────────────────────
        $this->crud('admin/pages',              'PageController');

        // ── CRUD: Media ─────────────────────────────────────────────────────
        $this->get('admin/media',               'MediaController@index',       true);
        $this->post('admin/media/upload',       'MediaController@upload',      true);
        $this->post('admin/media/delete/{id}',  'MediaController@delete',      true);

        // ── CRUD: Users ─────────────────────────────────────────────────────
        $this->crud('admin/users',              'UserController');

        // ── CRUD: Settings ──────────────────────────────────────────────────
        $this->get('admin/settings',            'SettingController@index',     true);
        $this->post('admin/settings',           'SettingController@update',    true);

        // ── CRUD: Categories (Taxonomy) ─────────────────────────────────────
        $this->crud('admin/categories',         'CategoryController');

        // ── CRUD: Snippets (HFCM) ───────────────────────────────────────────
        $this->crud('admin/snippets',           'SnippetController');

        // ── CRUD: Forms ─────────────────────────────────────────────────────
        $this->crud('admin/forms',              'FormController');
        $this->get('admin/forms/builder/{id}',  'FormController@builder',      true);
        $this->post('admin/forms/builder/{id}', 'FormController@builder',      true);
        $this->get('admin/forms/entries/{id}',  'FormController@entries',      true);
        $this->get('admin/forms/connectors/{id}','FormController@connectors',  true);
        $this->post('admin/forms/connectors/{id}','FormController@connectors', true);

        // ── Frontend Form Submission ────────────────────────────────────────
        $this->post('form/submit',              'FormSubmitController@submit');

        // ── CRUD: CPTs (consultants, promotions, specialties) ───────────────
        $this->crud('admin/consultants',        'ConsultantController');
        $this->crud('admin/promotions',         'PromotionController');
        $this->crud('admin/specialties',        'SpecialtyController');
    }

    /**
     * Register the standard CRUD route set for a resource.
     */
    private function crud(string $prefix, string $controller): void
    {
        $this->get($prefix,                     "{$controller}@index",    true);
        $this->get("{$prefix}/create",          "{$controller}@create",   true);
        $this->post("{$prefix}/create",         "{$controller}@create",   true);
        $this->get("{$prefix}/edit/{id}",       "{$controller}@edit",     true);
        $this->post("{$prefix}/edit/{id}",      "{$controller}@edit",     true);
        $this->post("{$prefix}/delete/{id}",    "{$controller}@delete",   true);
    }

    // ── Helper methods to register routes ───────────────────────────────────

    private function get(string $pattern, string $handler, bool $auth = false): void
    {
        $this->routes[] = [
            'method'  => 'GET',
            'pattern' => $pattern,
            'handler' => $handler,
            'auth'    => $auth,
        ];
    }

    private function post(string $pattern, string $handler, bool $auth = false): void
    {
        $this->routes[] = [
            'method'  => 'POST',
            'pattern' => $pattern,
            'handler' => $handler,
            'auth'    => $auth,
        ];
    }

    // ── Dispatch ────────────────────────────────────────────────────────────

    /**
     * Parse the current request URL, match a route, and call the controller.
     */
    public function dispatch(): void
    {
        $url    = $this->parseUrl();
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        foreach ($this->routes as $route) {
            // HTTP method must match
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchPattern($route['pattern'], $url);

            if ($params !== null) {
                // ── Auth guard ──────────────────────────────────────────
                if ($route['auth'] && !Auth::check()) {
                    header('Location: ' . $this->siteUrl('/admin/login'));
                    exit;
                }

                // ── Resolve controller & method ─────────────────────────
                [$controllerName, $methodName] = explode('@', $route['handler']);

                if (!class_exists($controllerName)) {
                    $this->notFound();
                    return;
                }

                $controller = new $controllerName();

                if (!method_exists($controller, $methodName)) {
                    $this->notFound();
                    return;
                }

                // Call with extracted URL parameters
                call_user_func_array([$controller, $methodName], $params);
                return;
            }
        }

        // No route matched
        $this->notFound();
    }

    // ── URL Parsing ─────────────────────────────────────────────────────────

    /**
     * Extract and sanitise the URL path from $_GET['url'].
     */
    private function parseUrl(): string
    {
        $url = $_GET['url'] ?? '';
        $url = filter_var(rtrim($url, '/'), FILTER_SANITIZE_URL);
        return $url ?: '';
    }

    // ── Pattern Matching ────────────────────────────────────────────────────

    /**
     * Match a route pattern against a URL.
     *
     * Patterns use {name} placeholders that map to regex capture groups.
     * Returns an array of captured values on match, or null on failure.
     *
     * @return array<string>|null
     */
    private function matchPattern(string $pattern, string $url): ?array
    {
        // Exact match (no placeholders)
        if ($pattern === $url) {
            return [];
        }

        // Convert {placeholder} to named regex groups
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[a-zA-Z0-9\-_]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $url, $matches)) {
            // Return only named captures (the placeholder values)
            return array_filter($matches, fn ($key) => !is_int($key), ARRAY_FILTER_USE_KEY);
        }

        return null;
    }

    // ── 404 Handler ─────────────────────────────────────────────────────────

    private function notFound(): void
    {
        http_response_code(404);

        // Try to render a themed 404 page
        $errorView = BASE_PATH . '/views/frontend/404.php';
        if (file_exists($errorView)) {
            View::render('frontend/404', ['pageTitle' => 'Page Not Found']);
        } else {
            echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head>';
            echo '<body><h1>404 — Page Not Found</h1>';
            echo '<p>The page you requested could not be found.</p></body></html>';
        }
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function siteUrl(string $path): string
    {
        $base = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
        return $base . '/' . ltrim($path, '/');
    }
}
