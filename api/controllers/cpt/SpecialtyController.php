<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/View.php';
require_once __DIR__ . '/../../core/Sanitizer.php';
require_once __DIR__ . '/../../models/cpt/Specialty.php';
require_once __DIR__ . '/../../core/Taxonomy.php';
require_once __DIR__ . '/../../core/Language.php';
require_once __DIR__ . '/../../models/cpt/Consultant.php';

/**
 * SpecialtyController (Admin CPT)
 * 
 * Admin CRUD controller for managing medical specialties.
 * Includes safeguard against deleting specialties that have consultants.
 */
class SpecialtyController
{
    private Specialty $specialty;
    private Consultant $consultant;

    public function __construct()
    {
        $this->specialty  = new Specialty();
        $this->consultant = new Consultant();
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
     * List all specialties with consultant count.
     */
    public function index(): void
    {
        $this->requireAuth();

        $specialties = $this->specialty->withConsultantCount();

        View::render('admin/cpt/specialties/index', [
            'pageTitle'   => 'Specialties',
            'specialties' => $specialties,
        ]);
    }

    /**
     * GET: Show create specialty form with icon picker.
     * POST: Validate input, create specialty, redirect.
     */
    public function create(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
                View::setFlash('error', 'Invalid security token.');
                header('Location: ' . View::url('/admin/specialties/create'));
                exit;
            }

            $rawDesc = $_POST['description'] ?? '';
            $data = Sanitizer::cleanArray($_POST);
            $data['description'] = $rawDesc;

            $rules = [
                'name'   => 'required',
                'status' => 'required',
            ];
            $errors = Sanitizer::validate($data, $rules);

            if (!empty($errors)) {
                View::setFlash('error', implode(' ', $errors));
                View::render('admin/cpt/specialties/form', [
                    'pageTitle'  => 'Add Specialty',
                    'specialty'  => $data,
                ]);
                return;
            }

            if (empty($data['slug'])) {
                $data['slug'] = $this->specialty->generateSlug($data['name']);
            }

            $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

            $specialtyData = array_intersect_key($data, array_flip([
                'name', 'slug', 'description', 'icon', 'status', 'sort_order',
            ]));

            $insertId = $this->specialty->create($specialtyData);

            if ($insertId) {
                Taxonomy::processFormSubmission('specialty', (int)$insertId, $_POST);
                if (!empty($_POST['language_code'])) {
                    Language::setContentLanguage('specialty', (int)$insertId, $_POST['language_code']);
                }
            }

            View::setFlash('success', 'Specialty created successfully.');
            header('Location: ' . View::url('/admin/specialties'));
            exit;
        }

        // GET — show empty form
        View::render('admin/cpt/specialties/form', [
            'pageTitle' => 'Add Specialty',
            'specialty' => [],
        ]);
    }

    /**
     * GET: Show edit specialty form pre-filled.
     * POST: Validate, update specialty, redirect.
     *
     * @param int $id Specialty ID
     */
    public function edit(int $id): void
    {
        $this->requireAuth();

        $specialty = $this->specialty->find($id);
        if (!$specialty) {
            View::setFlash('error', 'Specialty not found.');
            header('Location: ' . View::url('/admin/specialties'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
                View::setFlash('error', 'Invalid security token.');
                header('Location: ' . View::url('/admin/specialties/edit/' . $id));
                exit;
            }

            $rawDesc = $_POST['description'] ?? '';
            $data = Sanitizer::cleanArray($_POST);
            $data['description'] = $rawDesc;

            $rules = [
                'name'   => 'required',
                'status' => 'required',
            ];
            $errors = Sanitizer::validate($data, $rules);

            if (!empty($errors)) {
                View::setFlash('error', implode(' ', $errors));
                View::render('admin/cpt/specialties/form', [
                    'pageTitle' => 'Edit Specialty',
                    'specialty' => array_merge($specialty, $data),
                ]);
                return;
            }

            if (empty($data['slug'])) {
                $data['slug'] = $this->specialty->generateSlug($data['name']);
            }

            $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

            $specialtyData = array_intersect_key($data, array_flip([
                'name', 'slug', 'description', 'icon', 'status', 'sort_order',
            ]));

            $this->specialty->update($id, $specialtyData);

            Taxonomy::processFormSubmission('specialty', $id, $_POST);
            if (!empty($_POST['language_code'])) {
                Language::setContentLanguage('specialty', $id, $_POST['language_code']);
            }

            View::setFlash('success', 'Specialty updated successfully.');
            header('Location: ' . View::url('/admin/specialties'));
            exit;
        }

        // GET — show form with existing data
        View::render('admin/cpt/specialties/form', [
            'pageTitle' => 'Edit Specialty',
            'specialty' => $specialty,
        ]);
    }

    /**
     * Delete a specialty (POST only).
     *
     * Warns and prevents deletion if consultants are assigned to this specialty.
     *
     * @param int $id Specialty ID
     */
    public function delete(int $id): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . View::url('/admin/specialties'));
            exit;
        }

        if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
            View::setFlash('error', 'Invalid security token.');
            header('Location: ' . View::url('/admin/specialties'));
            exit;
        }

        $specialty = $this->specialty->find($id);
        if (!$specialty) {
            View::setFlash('error', 'Specialty not found.');
            header('Location: ' . View::url('/admin/specialties'));
            exit;
        }

        // Check if any consultants are assigned to this specialty
        $assignedConsultants = $this->consultant->bySpecialty($id);
        if (!empty($assignedConsultants)) {
            $count = count($assignedConsultants);
            View::setFlash(
                'error',
                "Cannot delete this specialty. {$count} consultant(s) are currently assigned to it. " .
                'Please reassign or remove them first.'
            );
            header('Location: ' . View::url('/admin/specialties'));
            exit;
        }

        $this->specialty->delete($id);

        View::setFlash('success', 'Specialty deleted successfully.');
        header('Location: ' . View::url('/admin/specialties'));
        exit;
    }
}
