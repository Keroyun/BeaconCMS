<?php
declare(strict_types=1);

/**
 * Form Builder Engine
 * Handles rendering of forms from JSON schema and processing submissions.
 */
class FormBuilder
{
    /**
     * Render a form HTML by shortcode.
     */
    public static function renderForm(string $shortcode): string
    {
        $formModel = new Form();
        $form = $formModel->findByShortcode($shortcode);

        if (!$form || $form['status'] !== 'active') {
            return '<!-- Form not found or inactive -->';
        }

        $fields = json_decode($form['fields_json'], true) ?? [];
        if (empty($fields)) {
            return '<p>No fields defined for this form.</p>';
        }

        $html = '<div class="beacon-form-container">';
        $html .= '<form class="beacon-form" method="POST" action="' . htmlspecialchars(View::url('/form/submit')) . '" id="form_' . $form['shortcode'] . '">';
        $html .= '<input type="hidden" name="form_id" value="' . (int)$form['id'] . '">';
        $html .= '<input type="hidden" name="csrf_token" value="' . Auth::generateCSRF() . '">';
        
        // Output flash messages if they exist for this form
        $successFlash = View::flash('form_success_' . $form['id']);
        if ($successFlash) {
            $html .= '<div class="alert alert-success">' . htmlspecialchars($successFlash) . '</div>';
        }
        $errorFlash = View::flash('form_error_' . $form['id']);
        if ($errorFlash) {
            $html .= '<div class="alert alert-danger">' . htmlspecialchars($errorFlash) . '</div>';
        }

        // Render each field
        foreach ($fields as $field) {
            $html .= self::renderField($field);
        }

        // Render Captcha if enabled
        $settingModel = new Setting();
        $captchaProvider = $settingModel->get('captcha_provider');
        $siteKey = $settingModel->get('captcha_site_key');
        
        if ($captchaProvider === 'turnstile' && $siteKey) {
            $html .= '<div class="form-group cf-turnstile" data-sitekey="' . htmlspecialchars($siteKey) . '"></div>';
            $html .= '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';
        } elseif ($captchaProvider === 'recaptcha_v3' && $siteKey) {
            $html .= '<input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response-' . $form['id'] . '">';
            $html .= '<script src="https://www.google.com/recaptcha/api.js?render=' . htmlspecialchars($siteKey) . '"></script>';
            $html .= '<script>
                grecaptcha.ready(function() {
                    grecaptcha.execute("' . htmlspecialchars($siteKey) . '", {action: "submit"}).then(function(token) {
                        document.getElementById("g-recaptcha-response-' . $form['id'] . '").value = token;
                    });
                });
            </script>';
        }

        $html .= '<div class="form-group submit-group">';
        $html .= '<button type="submit" class="btn btn--primary btn--lg">Submit</button>';
        $html .= '</div>';

        $html .= '</form></div>';

        return $html;
    }

    /**
     * Render a single form field based on its JSON schema definition.
     */
    private static function renderField(array $field): string
    {
        $type = $field['type'] ?? 'text';
        $name = $field['name'] ?? 'field_' . uniqid();
        $label = $field['label'] ?? '';
        $required = !empty($field['required']) ? 'required' : '';
        $placeholder = $field['placeholder'] ?? '';
        
        $html = '<div class="form-group">';
        if ($label) {
            $html .= '<label for="' . htmlspecialchars($name) . '">' . htmlspecialchars($label);
            if ($required) $html .= ' <span class="required">*</span>';
            $html .= '</label>';
        }

        switch ($type) {
            case 'textarea':
                $html .= '<textarea id="' . htmlspecialchars($name) . '" name="' . htmlspecialchars($name) . '" class="form-control" rows="4" placeholder="' . htmlspecialchars($placeholder) . '" ' . $required . '></textarea>';
                break;
            case 'select':
                $options = $field['options'] ?? [];
                $html .= '<select id="' . htmlspecialchars($name) . '" name="' . htmlspecialchars($name) . '" class="form-control" ' . $required . '>';
                $html .= '<option value="">Select an option</option>';
                foreach ($options as $opt) {
                    $html .= '<option value="' . htmlspecialchars($opt) . '">' . htmlspecialchars($opt) . '</option>';
                }
                $html .= '</select>';
                break;
            case 'email':
                $html .= '<input type="email" id="' . htmlspecialchars($name) . '" name="' . htmlspecialchars($name) . '" class="form-control" placeholder="' . htmlspecialchars($placeholder) . '" ' . $required . '>';
                break;
            case 'text':
            default:
                $html .= '<input type="' . htmlspecialchars($type) . '" id="' . htmlspecialchars($name) . '" name="' . htmlspecialchars($name) . '" class="form-control" placeholder="' . htmlspecialchars($placeholder) . '" ' . $required . '>';
                break;
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Process a form submission.
     */
    public static function processSubmission(int $formId, array $postData): bool
    {
        $formModel = new Form();
        $form = $formModel->find($formId);

        if (!$form || $form['status'] !== 'active') {
            return false;
        }

        // Clean data (exclude CSRF and form_id)
        $cleanData = [];
        foreach ($postData as $key => $value) {
            if (!in_array($key, ['csrf_token', 'form_id'])) {
                $cleanData[$key] = Sanitizer::clean($value);
            }
        }

        // 1. Save entry to DB
        $entryModel = new FormEntry();
        $entryModel->create([
            'form_id' => $formId,
            'entry_data_json' => json_encode($cleanData),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]);

        // 2. Trigger Connectors
        $connectorModel = new FormConnector();
        $activeConnectors = $connectorModel->getActiveConnectorsForForm($formId);

        foreach ($activeConnectors as $connectorData) {
            $type = $connectorData['connector_type'];
            $config = json_decode($connectorData['config_json'], true) ?? [];

            // Instantiate connector
            $connectorClass = ucfirst($type) . 'Connector';
            $connectorFile = __DIR__ . '/connectors/' . $connectorClass . '.php';

            if (file_exists($connectorFile)) {
                require_once $connectorFile;
                if (class_exists($connectorClass)) {
                    /** @var ConnectorInterface $connectorInstance */
                    $connectorInstance = new $connectorClass();
                    
                    // Execute connector (run in background/log errors if fails, but don't stop submission)
                    try {
                        $connectorInstance->execute($cleanData, $config, $form);
                    } catch (\Exception $e) {
                        error_log("Connector $type failed for form {$formId}: " . $e->getMessage());
                    }
                }
            }
        }

        return true;
    }

    /**
     * Parse content and replace [beacon_form id="xxx"] shortcodes with HTML.
     */
    public static function parseShortcodes(string $content): string
    {
        return preg_replace_callback('/\[beacon_form\s+id="([^"]+)"\]/i', function($matches) {
            $shortcode = $matches[1];
            return self::renderForm($shortcode);
        }, $content);
    }
}
