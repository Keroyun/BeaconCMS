<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/Sanitizer.php';
require_once __DIR__ . '/../models/Snippet.php';

/**
 * SnippetController (Header Footer Code Manager equivalent)
 */
class SnippetController
{
    private Snippet $snippet;

    public function __construct()
    {
        $this->snippet = new Snippet();
    }

    private function requireAuth(): void
    {
        if (!Auth::check()) {
            header('Location: ' . View::url('/admin/login'));
            exit;
        }
    }

    public function index(): void
    {
        $this->requireAuth();
        $snippets = $this->snippet->all();

        View::render('admin/snippets/index', [
            'pageTitle' => 'Code Snippets (HFCM)',
            'snippets' => $snippets,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
                View::setFlash('error', 'Invalid token.');
                header('Location: ' . View::url('/admin/snippets/create'));
                exit;
            }

            $this->snippet->create([
                'title' => Sanitizer::clean($_POST['title'] ?? ''),
                'location' => in_array($_POST['location'] ?? '', ['header', 'footer']) ? $_POST['location'] : 'header',
                // Important: Do not clean() code_content aggressively, we need raw HTML/JS. But we should be careful.
                // Since this is admin-only, we allow raw input.
                'code_content' => $_POST['code_content'] ?? '',
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ]);

            View::setFlash('success', 'Snippet added successfully.');
            header('Location: ' . View::url('/admin/snippets'));
            exit;
        }

        View::render('admin/snippets/form', [
            'pageTitle' => 'Add New Snippet',
        ]);
    }

    public function edit(int $id): void
    {
        $this->requireAuth();
        $snippet = $this->snippet->find($id);

        if (!$snippet) {
            header('Location: ' . View::url('/admin/snippets'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
                $this->snippet->update($id, [
                    'title' => Sanitizer::clean($_POST['title'] ?? ''),
                    'location' => in_array($_POST['location'] ?? '', ['header', 'footer']) ? $_POST['location'] : 'header',
                    'code_content' => $_POST['code_content'] ?? '',
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ]);
                View::setFlash('success', 'Snippet updated successfully.');
            }
            header('Location: ' . View::url('/admin/snippets'));
            exit;
        }

        View::render('admin/snippets/form', [
            'pageTitle' => 'Edit Snippet',
            'snippet' => $snippet,
        ]);
    }

    public function delete(int $id): void
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
            $this->snippet->delete($id);
            View::setFlash('success', 'Snippet deleted.');
        }
        header('Location: ' . View::url('/admin/snippets'));
        exit;
    }
}
