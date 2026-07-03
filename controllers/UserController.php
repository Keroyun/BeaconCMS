<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/Sanitizer.php';
require_once __DIR__ . '/../models/User.php';

/**
 * UserController
 * 
 * Admin CRUD controller for user accounts.
 */
class UserController
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
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
     * List all users.
     */
    public function index(): void
    {
        $this->requireAuth();

        $users = $this->user->all('username ASC');

        View::render('admin/users/index', [
            'pageTitle' => 'Users',
            'users'     => $users,
        ]);
    }

    /**
     * GET: Show create user form.
     * POST: Validate input, create user, redirect.
     */
    public function create(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
                View::setFlash('error', 'Invalid security token.');
                header('Location: ' . View::url('/admin/users/create'));
                exit;
            }

            $data = Sanitizer::cleanArray($_POST);

            $rules = [
                'username' => 'required',
                'email'    => 'required|email',
                'password' => 'required|min:8',
                'role'     => 'required',
            ];
            $errors = Sanitizer::validate($data, $rules);

            if (!empty($errors)) {
                View::setFlash('error', implode(' ', $errors));
                View::render('admin/users/create', [
                    'pageTitle' => 'Create User',
                    'userData'  => $data,
                ]);
                return;
            }

            // Check for duplicate username
            $existing = $this->user->findByUsername($data['username']);
            if ($existing) {
                View::setFlash('error', 'Username already exists.');
                View::render('admin/users/create', [
                    'pageTitle' => 'Create User',
                    'userData'  => $data,
                ]);
                return;
            }

            // Check for duplicate email
            $existingEmail = $this->user->findByEmail($data['email']);
            if ($existingEmail) {
                View::setFlash('error', 'Email address already in use.');
                View::render('admin/users/create', [
                    'pageTitle' => 'Create User',
                    'userData'  => $data,
                ]);
                return;
            }

            $userData = [
                'username' => $data['username'],
                'email'    => $data['email'],
                'password' => $data['password'],  // User model hashes this
                'role'     => $data['role'],
                'avatar'   => $data['avatar'] ?? null,
            ];

            $this->user->create($userData);

            View::setFlash('success', 'User created successfully.');
            header('Location: ' . View::url('/admin/users'));
            exit;
        }

        // GET — show empty form
        View::render('admin/users/create', [
            'pageTitle' => 'Create User',
            'userData'  => [],
        ]);
    }

    /**
     * GET: Show edit user form.
     * POST: Validate input, update user, redirect.
     *
     * @param int $id User ID
     */
    public function edit(int $id): void
    {
        $this->requireAuth();

        $userData = $this->user->find($id);
        if (!$userData) {
            View::setFlash('error', 'User not found.');
            header('Location: ' . View::url('/admin/users'));
            exit;
        }

        require_once __DIR__ . '/../core/TOTP.php';

        if (isset($_GET['action']) && $_GET['action'] === 'generate_2fa') {
            $secret = TOTP::createSecret();
            $this->user->update($id, ['two_factor_secret' => $secret, 'two_factor_enabled' => 0]);
            View::setFlash('success', '2FA Secret generated. Please scan the QR code and enable it.');
            header('Location: ' . View::url('/admin/users/edit/' . $id));
            exit;
        }

        if (isset($_GET['action']) && $_GET['action'] === 'disable_2fa') {
            $this->user->update($id, ['two_factor_secret' => null, 'two_factor_enabled' => 0]);
            View::setFlash('success', '2FA has been disabled.');
            header('Location: ' . View::url('/admin/users/edit/' . $id));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
                View::setFlash('error', 'Invalid security token.');
                header('Location: ' . View::url('/admin/users/edit/' . $id));
                exit;
            }

            $data = Sanitizer::cleanArray($_POST);

            $rules = [
                'username' => 'required',
                'email'    => 'required|email',
                'role'     => 'required',
            ];
            $errors = Sanitizer::validate($data, $rules);

            if (!empty($errors)) {
                View::setFlash('error', implode(' ', $errors));
                View::render('admin/users/edit', [
                    'pageTitle' => 'Edit User',
                    'userData'  => array_merge($userData, $data),
                ]);
                return;
            }

            // Check for duplicate username (exclude current user)
            $existing = $this->user->findByUsername($data['username']);
            if ($existing && (int) $existing['id'] !== $id) {
                View::setFlash('error', 'Username already taken by another user.');
                View::render('admin/users/edit', [
                    'pageTitle' => 'Edit User',
                    'userData'  => array_merge($userData, $data),
                ]);
                return;
            }

            // Check for duplicate email (exclude current user)
            $existingEmail = $this->user->findByEmail($data['email']);
            if ($existingEmail && (int) $existingEmail['id'] !== $id) {
                View::setFlash('error', 'Email already in use by another user.');
                View::render('admin/users/edit', [
                    'pageTitle' => 'Edit User',
                    'userData'  => array_merge($userData, $data),
                ]);
                return;
            }

            $updateData = [
                'username' => $data['username'],
                'email'    => $data['email'],
                'role'     => $data['role'],
                'avatar'   => $data['avatar'] ?? null,
                'two_factor_enabled' => isset($_POST['two_factor_enabled']) ? 1 : 0
            ];

            if (!empty($data['password'])) {
                $updateData['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            }

            // Verify TOTP code if enabling for the first time
            if (isset($_POST['two_factor_enabled']) && !$userData['two_factor_enabled'] && !empty($userData['two_factor_secret'])) {
                $code = $_POST['totp_code'] ?? '';
                if (!TOTP::verifyCode($userData['two_factor_secret'], $code)) {
                    View::setFlash('error', 'Invalid 2FA Code. 2FA was not enabled.');
                    $updateData['two_factor_enabled'] = 0;
                }
            }

            $this->user->update($id, $updateData);

            View::setFlash('success', 'User updated successfully.');
            header('Location: ' . View::url('/admin/users'));
            exit;
        }

        // GET — show form with existing data
        View::render('admin/users/edit', [
            'pageTitle' => 'Edit User',
            'userData'  => $userData,
        ]);
    }

    /**
     * Delete a user (POST only).
     *
     * Prevents users from deleting their own account.
     *
     * @param int $id User ID
     */
    public function delete(int $id): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . View::url('/admin/users'));
            exit;
        }

        if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
            View::setFlash('error', 'Invalid security token.');
            header('Location: ' . View::url('/admin/users'));
            exit;
        }

        // Prevent self-deletion
        $currentUser = Auth::user();
        if ((int) $currentUser['id'] === $id) {
            View::setFlash('error', 'You cannot delete your own account.');
            header('Location: ' . View::url('/admin/users'));
            exit;
        }

        $userData = $this->user->find($id);
        if (!$userData) {
            View::setFlash('error', 'User not found.');
            header('Location: ' . View::url('/admin/users'));
            exit;
        }

        $this->user->delete($id);

        View::setFlash('success', 'User deleted successfully.');
        header('Location: ' . View::url('/admin/users'));
        exit;
    }
}
