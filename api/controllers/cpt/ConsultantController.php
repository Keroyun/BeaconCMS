<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/View.php';
require_once __DIR__ . '/../../core/Sanitizer.php';
require_once __DIR__ . '/../../core/MediaManager.php';
require_once __DIR__ . '/../../models/cpt/Consultant.php';
require_once __DIR__ . '/../../models/cpt/Specialty.php';
require_once __DIR__ . '/../../core/Taxonomy.php';
require_once __DIR__ . '/../../core/Language.php';

/**
 * ConsultantController (Admin CPT)
 * 
 * Admin CRUD controller for managing consultant/doctor profiles.
 */
class ConsultantController
{
    private Consultant $consultant;
    private Specialty $specialty;

    public function __construct()
    {
        $this->consultant = new Consultant();
        $this->specialty  = new Specialty();
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
     * List all consultants with specialty name and status badge.
     */
    public function index(): void
    {
        $this->requireAuth();

        $consultants = $this->consultant->withSpecialty();

        View::render('admin/cpt/consultants/index', [
            'pageTitle'   => 'Consultants',
            'consultants' => $consultants,
        ]);
    }

    /**
     * GET: Show create consultant form with all fields, specialty dropdown, and image upload.
     * POST: Validate input, handle photo upload, create consultant, redirect.
     */
    public function create(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
                View::setFlash('error', 'Invalid security token.');
                header('Location: ' . View::url('/admin/consultants/create'));
                exit;
            }

            $rawBio = $_POST['bio'] ?? '';
            $rawQuals = $_POST['qualifications'] ?? '';
            $rawExp = $_POST['experience'] ?? '';
            $data = Sanitizer::cleanArray($_POST);
            $data['bio'] = $rawBio;
            $data['qualifications'] = $rawQuals;
            $data['experience'] = $rawExp;

            $rules = [
                'name'   => 'required',
                'status' => 'required',
            ];
            $errors = Sanitizer::validate($data, $rules);

            if (!empty($errors)) {
                View::setFlash('error', implode(' ', $errors));
                $specialties = $this->specialty->all('name ASC');
                View::render('admin/cpt/consultants/form', [
                    'pageTitle'   => 'Add Consultant',
                    'consultant'  => $data,
                    'specialties' => $specialties,
                ]);
                return;
            }

            // Auto-generate slug
            if (empty($data['slug'])) {
                $data['slug'] = $this->consultant->generateSlug($data['name']);
            }

            // Handle photo upload
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $mediaManager = new MediaManager();
                $uploadResult = $mediaManager->upload($_FILES['photo']);
                if ($uploadResult) {
                    $data['photo'] = $uploadResult['path'];
                }
            }

            // Cast numeric fields
            $data['specialty_id'] = !empty($data['specialty_id']) ? (int) $data['specialty_id'] : null;
            $data['sort_order']   = (int) ($data['sort_order'] ?? 0);

            $consultantData = array_intersect_key($data, array_flip([
                'name', 'slug', 'photo', 'specialty_id', 'qualifications',
                'experience', 'bio', 'clinic_hours', 'contact_number',
                'email', 'booking_link', 'status', 'sort_order',
                'seo_title', 'seo_description',
            ]));

            $insertId = $this->consultant->create($consultantData);

            // Process Taxonomy Categories & Language
            if ($insertId) {
                Taxonomy::processFormSubmission('consultant', (int)$insertId, $_POST);
                if (!empty($_POST['language_code'])) {
                    Language::setContentLanguage('consultant', (int)$insertId, $_POST['language_code']);
                }
            }

            View::setFlash('success', 'Consultant added successfully.');
            header('Location: ' . View::url('/admin/consultants'));
            exit;
        }

        // GET — show empty form
        $specialties = $this->specialty->all('name ASC');

        View::render('admin/cpt/consultants/form', [
            'pageTitle'   => 'Add Consultant',
            'consultant'  => [],
            'specialties' => $specialties,
        ]);
    }

    /**
     * GET: Show edit consultant form pre-filled with existing data.
     * POST: Validate, update consultant, redirect.
     *
     * @param int $id Consultant ID
     */
    public function edit(int $id): void
    {
        $this->requireAuth();

        $consultant = $this->consultant->find($id);
        if (!$consultant) {
            View::setFlash('error', 'Consultant not found.');
            header('Location: ' . View::url('/admin/consultants'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
                View::setFlash('error', 'Invalid security token.');
                header('Location: ' . View::url('/admin/consultants/edit/' . $id));
                exit;
            }

            $rawBio = $_POST['bio'] ?? '';
            $rawQuals = $_POST['qualifications'] ?? '';
            $rawExp = $_POST['experience'] ?? '';
            $data = Sanitizer::cleanArray($_POST);
            $data['bio'] = $rawBio;
            $data['qualifications'] = $rawQuals;
            $data['experience'] = $rawExp;

            $rules = [
                'name'   => 'required',
                'status' => 'required',
            ];
            $errors = Sanitizer::validate($data, $rules);

            if (!empty($errors)) {
                View::setFlash('error', implode(' ', $errors));
                $specialties = $this->specialty->all('name ASC');
                View::render('admin/cpt/consultants/form', [
                    'pageTitle'   => 'Edit Consultant',
                    'consultant'  => array_merge($consultant, $data),
                    'specialties' => $specialties,
                ]);
                return;
            }

            if (empty($data['slug'])) {
                $data['slug'] = $this->consultant->generateSlug($data['name']);
            }

            // Handle photo upload (keep existing if none uploaded)
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $mediaManager = new MediaManager();
                $uploadResult = $mediaManager->upload($_FILES['photo']);
                if ($uploadResult) {
                    // Delete old photo if it exists
                    if (!empty($consultant['photo'])) {
                        $mediaManager->delete($consultant['photo']);
                    }
                    $data['photo'] = $uploadResult['path'];
                }
            } else {
                // Keep the existing photo
                $data['photo'] = $consultant['photo'];
            }

            $data['specialty_id'] = !empty($data['specialty_id']) ? (int) $data['specialty_id'] : null;
            $data['sort_order']   = (int) ($data['sort_order'] ?? 0);

            $consultantData = array_intersect_key($data, array_flip([
                'name', 'slug', 'photo', 'specialty_id', 'qualifications',
                'experience', 'bio', 'clinic_hours', 'contact_number',
                'email', 'booking_link', 'status', 'sort_order',
                'seo_title', 'seo_description',
            ]));

            $this->consultant->update($id, $consultantData);

            // Process Taxonomy Categories & Language
            Taxonomy::processFormSubmission('consultant', $id, $_POST);
            if (!empty($_POST['language_code'])) {
                Language::setContentLanguage('consultant', $id, $_POST['language_code']);
            }

            View::setFlash('success', 'Consultant updated successfully.');
            header('Location: ' . View::url('/admin/consultants'));
            exit;
        }

        // GET — show form with existing data
        $specialties = $this->specialty->all('name ASC');

        View::render('admin/cpt/consultants/form', [
            'pageTitle'   => 'Edit Consultant',
            'consultant'  => $consultant,
            'specialties' => $specialties,
        ]);
    }

    /**
     * Delete a consultant (POST only).
     *
     * @param int $id Consultant ID
     */
    public function delete(int $id): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . View::url('/admin/consultants'));
            exit;
        }

        if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
            View::setFlash('error', 'Invalid security token.');
            header('Location: ' . View::url('/admin/consultants'));
            exit;
        }

        $consultant = $this->consultant->find($id);
        if (!$consultant) {
            View::setFlash('error', 'Consultant not found.');
            header('Location: ' . View::url('/admin/consultants'));
            exit;
        }

        // Delete photo from disk if it exists
        if (!empty($consultant['photo'])) {
            $mediaManager = new MediaManager();
            $mediaManager->delete($consultant['photo']);
        }

        $this->consultant->delete($id);

        View::setFlash('success', 'Consultant deleted successfully.');
        header('Location: ' . View::url('/admin/consultants'));
        exit;
    }
}
