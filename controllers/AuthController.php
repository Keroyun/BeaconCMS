<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/Sanitizer.php';

/**
 * AuthController
 * 
 * Handles admin login/logout authentication flow.
 */
class AuthController
{
    /**
     * Display login form (GET) or process login credentials (POST).
     *
     * On successful login, redirects to /admin dashboard.
     * On failure, re-renders login form with error message.
     */
    public function login(): void
    {
        // If already logged in, redirect to dashboard
        if (Auth::check()) {
            header('Location: ' . View::url('/admin'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verify CSRF token
            if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
                View::setFlash('error', 'Invalid security token. Please try again.');
                header('Location: ' . View::url('/admin/login'));
                exit;
            }

            $username = Sanitizer::clean($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            // Validate required fields
            if (empty($username) || empty($password)) {
                View::setFlash('error', 'Username and password are required.');
                View::render('admin/login', [
                    'pageTitle' => 'Login',
                    'username'  => $username,
                ]);
                return;
            }

            $userModel = new User();
            $user = $userModel->findByUsername($username);

            if ($user && password_verify($password, $user['password'])) {
                // Check 2FA
                if (!empty($user['two_factor_enabled']) && !empty($user['two_factor_secret'])) {
                    require_once __DIR__ . '/../core/TOTP.php';
                    $totpCode = $_POST['totp_code'] ?? '';
                    if (!TOTP::verifyCode($user['two_factor_secret'], $totpCode)) {
                        View::setFlash('error', 'Invalid 2FA code.');
                        header('Location: ' . View::url('/admin/login'));
                        exit;
                    }
                }

                if (Auth::login($username, $password)) {
                    View::setFlash('success', 'Welcome back!');
                    header('Location: ' . View::url('/admin'));
                    exit;
                }
            }

            // Login failed
            View::setFlash('error', 'Invalid username or password.');
            View::render('admin/login', [
                'pageTitle' => 'Login',
                'username'  => $username,
            ]);
            return;
        }

        // GET request — show login form
        View::render('admin/login', [
            'pageTitle' => 'Login',
        ]);
    }

    /**
     * Log the user out and redirect to the login page.
     */
    public function logout(): void
    {
        Auth::logout();
        View::setFlash('success', 'You have been logged out.');
        header('Location: ' . View::url('/admin/login'));
        exit;
    }
}
