<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/Sanitizer.php';
require_once __DIR__ . '/../core/FormBuilder.php';
require_once __DIR__ . '/../models/Form.php';
require_once __DIR__ . '/../models/FormEntry.php';
require_once __DIR__ . '/../models/FormConnector.php';
require_once __DIR__ . '/../models/Setting.php';

/**
 * Handles frontend form submissions.
 */
class FormSubmitController
{
    public function submit(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method not allowed.";
            exit;
        }

        if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
            $this->redirectBackWith('error', 'Invalid security token.', $_POST['form_id'] ?? 0);
            return;
        }

        $formId = (int)($_POST['form_id'] ?? 0);
        if (!$formId) {
            $this->redirectBackWith('error', 'Invalid form ID.', 0);
            return;
        }

        // Validate Captcha
        $settingModel = new Setting();
        $captchaProvider = $settingModel->get('captcha_provider');
        $secretKey = $settingModel->get('captcha_secret_key');

        if ($captchaProvider === 'turnstile' && $secretKey) {
            $token = $_POST['cf-turnstile-response'] ?? '';
            if (!$this->verifyCaptcha('https://challenges.cloudflare.com/turnstile/v0/siteverify', $secretKey, $token)) {
                $this->redirectBackWith('error', 'Captcha validation failed. Please try again.', $formId);
                return;
            }
        } elseif ($captchaProvider === 'recaptcha_v3' && $secretKey) {
            $token = $_POST['g-recaptcha-response'] ?? '';
            if (!$this->verifyCaptcha('https://www.google.com/recaptcha/api/siteverify', $secretKey, $token)) {
                $this->redirectBackWith('error', 'Captcha validation failed. Please try again.', $formId);
                return;
            }
        }

        // Process the submission via FormBuilder
        $success = FormBuilder::processSubmission($formId, $_POST);

        if ($success) {
            $this->redirectBackWith('success', 'Form submitted successfully! Thank you.', $formId);
        } else {
            $this->redirectBackWith('error', 'Failed to submit form. Please try again later.', $formId);
        }
    }

    private function redirectBackWith(string $type, string $message, int $formId): void
    {
        View::setFlash('form_' . $type . '_' . $formId, $message);
        
        // Redirect back to the page they came from
        $referer = $_SERVER['HTTP_REFERER'] ?? View::url('/');
        header('Location: ' . $referer);
        exit;
    }

    private function verifyCaptcha(string $url, string $secret, string $response): bool
    {
        if (empty($response)) return false;

        $data = [
            'secret' => $secret,
            'response' => $response,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result === false) {
            return false;
        }

        $json = json_decode($result, true);
        return !empty($json['success']);
    }
}
