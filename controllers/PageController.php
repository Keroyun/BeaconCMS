<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/Sanitizer.php';
require_once __DIR__ . '/../models/Page.php';

/**
 * PageController
 * 
 * Admin CRUD controller for CMS pages.
 */
class PageController
{
    private Page $page;

    public function __construct()
    {
        $this->page = new Page();
    }

    /**
     * Ensure the user is authenticated; redirect to login if not.
     */
    private function requireAuth(): void
    {
        if (!Auth::check()) {
            header('Location: ' . View::url('/admin/login'));
            exit;
        }
    }

    /**
     * List all pages with status badges.
     */
    public function index(): void
    {
        $this->requireAuth();

        $pages = $this->page->all('sort_order ASC');

        View::render('admin/pages/index', [
            'pageTitle' => 'Pages',
            'pages'     => $pages,
        ]);
    }

    /**
     * GET: Show create page form.
     * POST: Validate input, create page, redirect.
     */
    public function create(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
                View::setFlash('error', 'Invalid security token.');
                header('Location: ' . View::url('/admin/pages/create'));
                exit;
            }

            $data = Sanitizer::cleanArray($_POST);

            $rules = [
                'title'   => 'required',
                'content' => 'required',
                'status'  => 'required',
            ];
            $errors = Sanitizer::validate($data, $rules);

            if (!empty($errors)) {
                View::setFlash('error', implode(' ', $errors));
                View::render('admin/pages/create', [
                    'pageTitle' => 'Create Page',
                    'page'      => $data,
                ]);
                return;
            }

            if (empty($data['slug'])) {
                $data['slug'] = $this->page->generateSlug($data['title']);
            }

            // Default sort_order to 0 if not provided
            $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

            // Use null for empty parent_id
            $data['parent_id'] = !empty($data['parent_id']) ? (int) $data['parent_id'] : null;

            $pageData = array_intersect_key($data, array_flip([
                'title', 'slug', 'content', 'template', 'status',
                'sort_order', 'parent_id', 'seo_title', 'seo_description', 'og_image',
            ]));

            $this->page->create($pageData);

            View::setFlash('success', 'Page created successfully.');
            header('Location: ' . View::url('/admin/pages'));
            exit;
        }

        // GET — show empty form; pass all pages for parent dropdown
        $allPages = $this->page->all('title ASC');

        View::render('admin/pages/create', [
            'pageTitle' => 'Create Page',
            'page'      => [],
            'allPages'  => $allPages,
        ]);
    }

    /**
     * GET: Show edit form with existing page data.
     * POST: Validate input, update page, redirect.
     *
     * @param int $id Page ID
     */
    public function edit(int $id): void
    {
        $this->requireAuth();

        $page = $this->page->find($id);
        if (!$page) {
            View::setFlash('error', 'Page not found.');
            header('Location: ' . View::url('/admin/pages'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
                View::setFlash('error', 'Invalid security token.');
                header('Location: ' . View::url('/admin/pages/edit/' . $id));
                exit;
            }

            $data = Sanitizer::cleanArray($_POST);

            $rules = [
                'title'   => 'required',
                'content' => 'required',
                'status'  => 'required',
            ];
            $errors = Sanitizer::validate($data, $rules);

            if (!empty($errors)) {
                View::setFlash('error', implode(' ', $errors));
                $allPages = $this->page->all('title ASC');
                View::render('admin/pages/edit', [
                    'pageTitle' => 'Edit Page',
                    'page'      => array_merge($page, $data),
                    'allPages'  => $allPages,
                ]);
                return;
            }

            if (empty($data['slug'])) {
                $data['slug'] = $this->page->generateSlug($data['title']);
            }

            $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
            $data['parent_id']  = !empty($data['parent_id']) ? (int) $data['parent_id'] : null;

            $pageData = array_intersect_key($data, array_flip([
                'title', 'slug', 'content', 'template', 'status',
                'sort_order', 'parent_id', 'seo_title', 'seo_description', 'og_image',
            ]));

            $this->page->update($id, $pageData);

            View::setFlash('success', 'Page updated successfully.');
            header('Location: ' . View::url('/admin/pages'));
            exit;
        }

        // GET — show form with existing data
        $allPages = $this->page->all('title ASC');

        View::render('admin/pages/edit', [
            'pageTitle' => 'Edit Page',
            'page'      => $page,
            'allPages'  => $allPages,
        ]);
    }

    /**
     * Delete a page (POST only).
     *
     * @param int $id Page ID
     */
    public function delete(int $id): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . View::url('/admin/pages'));
            exit;
        }

        if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
            View::setFlash('error', 'Invalid security token.');
            header('Location: ' . View::url('/admin/pages'));
            exit;
        }

        $page = $this->page->find($id);
        if (!$page) {
            View::setFlash('error', 'Page not found.');
            header('Location: ' . View::url('/admin/pages'));
            exit;
        }

        $this->page->delete($id);

        View::setFlash('success', 'Page deleted successfully.');
        header('Location: ' . View::url('/admin/pages'));
        exit;
    }
}
