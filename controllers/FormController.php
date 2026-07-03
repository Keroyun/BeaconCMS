<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/Sanitizer.php';
require_once __DIR__ . '/../models/Form.php';
require_once __DIR__ . '/../models/FormEntry.php';
require_once __DIR__ . '/../models/FormConnector.php';

/**
 * FormController (Admin)
 * Manages form creation, field building, entries, and connectors.
 */
class FormController
{
    private Form $form;
    private FormEntry $entry;
    private FormConnector $connector;

    public function __construct()
    {
        $this->form = new Form();
        $this->entry = new FormEntry();
        $this->connector = new FormConnector();
    }

    private function requireAuth(): void
    {
        if (!Auth::check()) {
            header('Location: ' . View::url('/admin/login'));
            exit;
        }
    }

    // ── Forms List ──────────────────────────────────────────────────────────

    public function index(): void
    {
        $this->requireAuth();
        $forms = $this->form->all('created_at DESC');

        View::render('admin/forms/index', [
            'pageTitle' => 'Forms',
            'forms' => $forms,
        ]);
    }

    // ── Create & Edit Form (Basic Info) ─────────────────────────────────────

    public function create(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
                View::setFlash('error', 'Invalid token.');
                header('Location: ' . View::url('/admin/forms/create'));
                exit;
            }

            $title = Sanitizer::clean($_POST['title'] ?? '');
            if (empty($title)) {
                View::setFlash('error', 'Form title is required.');
                header('Location: ' . View::url('/admin/forms/create'));
                exit;
            }

            $shortcode = Sanitizer::slug($title . '-' . uniqid());
            
            $id = $this->form->create([
                'title' => $title,
                'shortcode' => $shortcode,
                'fields_json' => '[]',
                'settings_json' => '{}',
                'status' => 'active',
            ]);

            View::setFlash('success', 'Form created. Now add some fields.');
            header('Location: ' . View::url('/admin/forms/builder/' . $id));
            exit;
        }

        View::render('admin/forms/create', [
            'pageTitle' => 'Create New Form',
        ]);
    }

    public function delete(int $id): void
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
            $this->form->delete($id);
            View::setFlash('success', 'Form deleted.');
        }
        header('Location: ' . View::url('/admin/forms'));
        exit;
    }

    // ── Form Builder (Fields) ───────────────────────────────────────────────

    public function builder(int $id): void
    {
        $this->requireAuth();
        $form = $this->form->find($id);

        if (!$form) {
            View::setFlash('error', 'Form not found.');
            header('Location: ' . View::url('/admin/forms'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
                View::setFlash('error', 'Invalid token.');
            } else {
                // Save JSON fields
                $fieldsJson = $_POST['fields_json'] ?? '[]';
                // Basic JSON validation
                if (json_decode($fieldsJson) !== null) {
                    $this->form->update($id, ['fields_json' => $fieldsJson]);
                    View::setFlash('success', 'Form fields saved successfully.');
                } else {
                    View::setFlash('error', 'Invalid JSON format for fields.');
                }
            }
            header('Location: ' . View::url('/admin/forms/builder/' . $id));
            exit;
        }

        View::render('admin/forms/builder', [
            'pageTitle' => 'Form Builder: ' . $form['title'],
            'form' => $form,
        ]);
    }

    // ── Form Entries (Inbox) ────────────────────────────────────────────────

    public function entries(int $id): void
    {
        $this->requireAuth();
        $form = $this->form->find($id);

        if (!$form) {
            header('Location: ' . View::url('/admin/forms'));
            exit;
        }

        $entries = $this->entry->getEntriesForForm($id);

        View::render('admin/forms/entries', [
            'pageTitle' => 'Entries: ' . $form['title'],
            'form' => $form,
            'entries' => $entries,
        ]);
    }

    // ── Connectors (Zendesk etc) ────────────────────────────────────────────

    public function connectors(int $id): void
    {
        $this->requireAuth();
        $form = $this->form->find($id);

        if (!$form) {
            header('Location: ' . View::url('/admin/forms'));
            exit;
        }

        // Get active connectors
        $connectors = $this->connector->where('form_id = ?', [$id]);
        $activeConfig = [];
        foreach ($connectors as $c) {
            $activeConfig[$c['connector_type']] = [
                'id' => $c['id'],
                'config' => json_decode($c['config_json'], true),
                'is_active' => $c['is_active'],
            ];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
                $type = $_POST['connector_type'] ?? '';
                $isActive = !empty($_POST['is_active']) ? 1 : 0;
                
                // Get config data (everything starting with config_)
                $configData = [];
                foreach ($_POST as $k => $v) {
                    if (strpos($k, 'config_') === 0) {
                        $key = substr($k, 7);
                        $configData[$key] = Sanitizer::clean($v);
                    }
                }

                // Insert or update connector
                if (isset($activeConfig[$type])) {
                    $this->connector->update((int)$activeConfig[$type]['id'], [
                        'config_json' => json_encode($configData),
                        'is_active' => $isActive,
                    ]);
                } else {
                    $this->connector->create([
                        'form_id' => $id,
                        'connector_type' => $type,
                        'config_json' => json_encode($configData),
                        'is_active' => $isActive,
                    ]);
                }

                View::setFlash('success', 'Connector settings saved.');
            }
            header('Location: ' . View::url('/admin/forms/connectors/' . $id));
            exit;
        }

        // We only have Zendesk right now, but architecture allows more
        require_once __DIR__ . '/../core/connectors/ZendeskConnector.php';
        $availableConnectors = [
            'zendesk' => new ZendeskConnector()
        ];

        View::render('admin/forms/connectors', [
            'pageTitle' => 'Connectors: ' . $form['title'],
            'form' => $form,
            'availableConnectors' => $availableConnectors,
            'activeConfig' => $activeConfig,
        ]);
    }
}
