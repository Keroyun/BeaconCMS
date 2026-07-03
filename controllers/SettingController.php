<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/Sanitizer.php';
require_once __DIR__ . '/../models/Setting.php';

/**
 * SettingController
 * 
 * Admin controller for site-wide settings management.
 * Handles a single form with multiple setting groups.
 */
class SettingController
{
    private Setting $setting;

    public function __construct()
    {
        $this->setting = new Setting();
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
     * GET: Display settings form with current values.
     * POST: Save all submitted settings and redirect with success message.
     *
     * Settings handled:
     *  - General: site_name, site_description, site_logo, footer_text
     *  - Social:  facebook_url, twitter_url, instagram_url, linkedin_url, youtube_url
     *  - SEO:     default_seo_title, default_seo_description, default_seo_keywords, google_analytics_id
     */
    public function index(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
                View::setFlash('error', 'Invalid security token.');
                header('Location: ' . View::url('/admin/settings'));
                exit;
            }

            $data = Sanitizer::cleanArray($_POST);

            // Define which settings belong to which group
            $settingsMap = [
                'general' => [
                    'site_name',
                    'site_description',
                    'site_logo',
                    'footer_text',
                ],
                'social' => [
                    'facebook_url',
                    'twitter_url',
                    'instagram_url',
                    'linkedin_url',
                    'youtube_url',
                ],
                'seo' => [
                    'default_seo_title',
                    'default_seo_description',
                    'default_seo_keywords',
                    'google_analytics_id',
                ],
                'smtp' => [
                    'smtp_host',
                    'smtp_port',
                    'smtp_username',
                    'smtp_password',
                    'smtp_encryption',
                    'smtp_from_email',
                    'smtp_from_name',
                ],
                'captcha' => [
                    'captcha_provider',
                    'captcha_site_key',
                    'captcha_secret_key',
                ],
            ];

            // Iterate and save each setting
            foreach ($settingsMap as $group => $keys) {
                foreach ($keys as $key) {
                    $value = $data[$key] ?? '';
                    $this->setting->set($key, $value, $group);
                }
            }

            View::setFlash('success', 'Settings saved successfully.');
            header('Location: ' . View::url('/admin/settings'));
            exit;
        }

        // GET — load current settings
        $settings = $this->setting->getAllAsArray();

        View::render('admin/settings/index', [
            'pageTitle' => 'Settings',
            'settings'  => $settings,
        ]);
    }
}
