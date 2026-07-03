<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/View.php';
require_once __DIR__ . '/../../core/Sanitizer.php';
require_once __DIR__ . '/../../core/MediaManager.php';
require_once __DIR__ . '/../../core/Postmeta.php';
require_once __DIR__ . '/../../models/cpt/Promotion.php';

/**
 * PromotionController (Admin CPT)
 * 
 * Admin CRUD controller for managing promotions with date ranges.
 */
class PromotionController
{
    private Promotion $promotion;

    public function __construct()
    {
        $this->promotion = new Promotion();
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
     * List all promotions with date range and status display.
     */
    public function index(): void
    {
        $this->requireAuth();

        $promotions = $this->promotion->all('start_date DESC');

        View::render('admin/cpt/promotions/index', [
            'pageTitle'  => 'Promotions',
            'promotions' => $promotions,
        ]);
    }

    /**
     * GET: Show create promotion form with date pickers.
     * POST: Validate input, handle image upload, create promotion, redirect.
     */
    public function create(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
                View::setFlash('error', 'Invalid security token.');
                header('Location: ' . View::url('/admin/promotions/create'));
                exit;
            }

            $data = Sanitizer::cleanArray($_POST);

            $rules = [
                'title'      => 'required',
                'start_date' => 'required',
                'status'     => 'required',
            ];
            $errors = Sanitizer::validate($data, $rules);

            if (!empty($errors)) {
                View::setFlash('error', implode(' ', $errors));
                View::render('admin/cpt/promotions/create', [
                    'pageTitle' => 'Create Promotion',
                    'promotion' => $data,
                ]);
                return;
            }

            // Validate date logic: end_date must be >= start_date if provided
            if (!empty($data['end_date']) && $data['end_date'] < $data['start_date']) {
                View::setFlash('error', 'End date must be on or after the start date.');
                View::render('admin/cpt/promotions/create', [
                    'pageTitle' => 'Create Promotion',
                    'promotion' => $data,
                ]);
                return;
            }

            if (empty($data['slug'])) {
                $data['slug'] = $this->promotion->generateSlug($data['title']);
            }

            // Handle featured image upload
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                $mediaManager = new MediaManager();
                $uploadResult = $mediaManager->upload($_FILES['featured_image']);
                if ($uploadResult) {
                    $data['featured_image'] = $uploadResult['path'];
                }
            }

            // Handle home banner image upload
            if (isset($_FILES['home_banner_image']) && $_FILES['home_banner_image']['error'] === UPLOAD_ERR_OK) {
                $mediaManager = new MediaManager();
                $uploadResult = $mediaManager->upload($_FILES['home_banner_image']);
                if ($uploadResult) {
                    $homeBannerImage = $uploadResult['path'];
                }
            }

            // Set empty end_date to null
            $data['end_date'] = !empty($data['end_date']) ? $data['end_date'] : null;

            $promotionData = array_intersect_key($data, array_flip([
                'title', 'slug', 'description', 'featured_image',
                'start_date', 'end_date', 'status',
                'seo_title', 'seo_description',
            ]));

            $insertId = $this->promotion->create($promotionData);

            if ($insertId && isset($homeBannerImage)) {
                Postmeta::set('promotion', (int)$insertId, 'home_banner_image', $homeBannerImage);
            }

            View::setFlash('success', 'Promotion created successfully.');
            header('Location: ' . View::url('/admin/promotions'));
            exit;
        }

        // GET — show empty form
        View::render('admin/cpt/promotions/create', [
            'pageTitle' => 'Create Promotion',
            'promotion' => [],
        ]);
    }

    /**
     * GET: Show edit promotion form pre-filled.
     * POST: Validate, update promotion, redirect.
     *
     * @param int $id Promotion ID
     */
    public function edit(int $id): void
    {
        $this->requireAuth();

        $promotion = $this->promotion->find($id);
        if (!$promotion) {
            View::setFlash('error', 'Promotion not found.');
            header('Location: ' . View::url('/admin/promotions'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
                View::setFlash('error', 'Invalid security token.');
                header('Location: ' . View::url('/admin/promotions/edit/' . $id));
                exit;
            }

            $data = Sanitizer::cleanArray($_POST);

            $rules = [
                'title'      => 'required',
                'start_date' => 'required',
                'status'     => 'required',
            ];
            $errors = Sanitizer::validate($data, $rules);

            if (!empty($errors)) {
                View::setFlash('error', implode(' ', $errors));
                View::render('admin/cpt/promotions/edit', [
                    'pageTitle' => 'Edit Promotion',
                    'promotion' => array_merge($promotion, $data),
                ]);
                return;
            }

            if (!empty($data['end_date']) && $data['end_date'] < $data['start_date']) {
                View::setFlash('error', 'End date must be on or after the start date.');
                View::render('admin/cpt/promotions/edit', [
                    'pageTitle' => 'Edit Promotion',
                    'promotion' => array_merge($promotion, $data),
                ]);
                return;
            }

            if (empty($data['slug'])) {
                $data['slug'] = $this->promotion->generateSlug($data['title']);
            }

            // Handle featured image upload (keep existing if none uploaded)
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                $mediaManager = new MediaManager();
                $uploadResult = $mediaManager->upload($_FILES['featured_image']);
                if ($uploadResult) {
                    // Delete old image if it exists
                    if (!empty($promotion['featured_image'])) {
                        $mediaManager->delete($promotion['featured_image']);
                    }
                    $data['featured_image'] = $uploadResult['path'];
                }
            } else {
                $data['featured_image'] = $promotion['featured_image'];
            }

            // Handle home banner image upload
            if (isset($_FILES['home_banner_image']) && $_FILES['home_banner_image']['error'] === UPLOAD_ERR_OK) {
                $mediaManager = new MediaManager();
                $uploadResult = $mediaManager->upload($_FILES['home_banner_image']);
                if ($uploadResult) {
                    $homeBannerImage = $uploadResult['path'];
                }
            }

            $data['end_date'] = !empty($data['end_date']) ? $data['end_date'] : null;

            $promotionData = array_intersect_key($data, array_flip([
                'title', 'slug', 'description', 'featured_image',
                'start_date', 'end_date', 'status',
                'seo_title', 'seo_description',
            ]));

            $this->promotion->update($id, $promotionData);

            if (isset($homeBannerImage)) {
                Postmeta::set('promotion', $id, 'home_banner_image', $homeBannerImage);
            }

            View::setFlash('success', 'Promotion updated successfully.');
            header('Location: ' . View::url('/admin/promotions'));
            exit;
        }

        // GET — show form with existing data
        View::render('admin/cpt/promotions/edit', [
            'pageTitle' => 'Edit Promotion',
            'promotion' => $promotion,
        ]);
    }

    /**
     * Delete a promotion (POST only).
     *
     * @param int $id Promotion ID
     */
    public function delete(int $id): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . View::url('/admin/promotions'));
            exit;
        }

        if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
            View::setFlash('error', 'Invalid security token.');
            header('Location: ' . View::url('/admin/promotions'));
            exit;
        }

        $promotion = $this->promotion->find($id);
        if (!$promotion) {
            View::setFlash('error', 'Promotion not found.');
            header('Location: ' . View::url('/admin/promotions'));
            exit;
        }

        // Delete featured image from disk if it exists
        if (!empty($promotion['featured_image'])) {
            $mediaManager = new MediaManager();
            $mediaManager->delete($promotion['featured_image']);
        }

        $this->promotion->delete($id);

        View::setFlash('success', 'Promotion deleted successfully.');
        header('Location: ' . View::url('/admin/promotions'));
        exit;
    }
}
