<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/Sanitizer.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../core/Taxonomy.php';
require_once __DIR__ . '/../core/Language.php';

/**
 * PostController
 * 
 * Admin CRUD controller for blog posts.
 */
class PostController
{
    private Post $post;

    public function __construct()
    {
        $this->post = new Post();
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
     * List all posts with status badges.
     */
    public function index(): void
    {
        $this->requireAuth();

        $posts = $this->post->all('created_at DESC');

        View::render('admin/posts/index', [
            'pageTitle' => 'Posts',
            'posts'     => $posts,
        ]);
    }

    /**
     * GET: Show create post form.
     * POST: Validate input, create post, redirect to list.
     */
    public function create(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
                View::setFlash('error', 'Invalid security token.');
                header('Location: ' . View::url('/admin/posts/create'));
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
                View::render('admin/posts/form', [
                    'pageTitle' => 'Create Post',
                    'post'      => $data,
                ]);
                return;
            }

            // Auto-generate slug from title if not provided
            if (empty($data['slug'])) {
                $data['slug'] = $this->post->generateSlug($data['title']);
            }

            // Assign current user as author
            $user = Auth::user();
            $data['author_id'] = $user['id'];

            // Filter to only fillable fields
            $postData = array_intersect_key($data, array_flip($this->post->getFillable ?? [
                'title', 'slug', 'content', 'excerpt', 'featured_image',
                'status', 'author_id', 'seo_title', 'seo_description',
                'seo_keywords', 'og_image',
            ]));

            $insertId = $this->post->create($postData);

            if ($insertId) {
                Taxonomy::processFormSubmission('post', (int)$insertId, $_POST);
                if (!empty($_POST['language_code'])) {
                    Language::setContentLanguage('post', (int)$insertId, $_POST['language_code']);
                }
            }

            View::setFlash('success', 'Post created successfully.');
            header('Location: ' . View::url('/admin/posts'));
            exit;
        }

        // GET — show empty form
        View::render('admin/posts/form', [
            'pageTitle' => 'Create Post',
            'post'      => [],
        ]);
    }

    /**
     * GET: Show edit form with existing post data.
     * POST: Validate input, update post, redirect.
     *
     * @param int $id Post ID
     */
    public function edit(int $id): void
    {
        $this->requireAuth();

        $post = $this->post->find($id);
        if (!$post) {
            View::setFlash('error', 'Post not found.');
            header('Location: ' . View::url('/admin/posts'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
                View::setFlash('error', 'Invalid security token.');
                header('Location: ' . View::url('/admin/posts/edit/' . $id));
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
                View::render('admin/posts/form', [
                    'pageTitle' => 'Edit Post',
                    'post'      => array_merge($post, $data),
                ]);
                return;
            }

            if (empty($data['slug'])) {
                $data['slug'] = $this->post->generateSlug($data['title']);
            }

            $postData = array_intersect_key($data, array_flip([
                'title', 'slug', 'content', 'excerpt', 'featured_image',
                'status', 'seo_title', 'seo_description', 'seo_keywords', 'og_image',
            ]));

            $this->post->update($id, $postData);

            Taxonomy::processFormSubmission('post', $id, $_POST);
            if (!empty($_POST['language_code'])) {
                Language::setContentLanguage('post', $id, $_POST['language_code']);
            }

            View::setFlash('success', 'Post updated successfully.');
            header('Location: ' . View::url('/admin/posts'));
            exit;
        }

        // GET — show form with existing data
        View::render('admin/posts/form', [
            'pageTitle' => 'Edit Post',
            'post'      => $post,
        ]);
    }

    /**
     * Delete a post (POST only).
     *
     * @param int $id Post ID
     */
    public function delete(int $id): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . View::url('/admin/posts'));
            exit;
        }

        if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
            View::setFlash('error', 'Invalid security token.');
            header('Location: ' . View::url('/admin/posts'));
            exit;
        }

        $post = $this->post->find($id);
        if (!$post) {
            View::setFlash('error', 'Post not found.');
            header('Location: ' . View::url('/admin/posts'));
            exit;
        }

        $this->post->delete($id);

        View::setFlash('success', 'Post deleted successfully.');
        header('Location: ' . View::url('/admin/posts'));
        exit;
    }
}
