<?php
/**
 * Auth — Authentication & CSRF Protection
 *
 * Static helper class for login / logout, session inspection,
 * password hashing, and CSRF token management.
 */
class Auth
{
    // ── Login ───────────────────────────────────────────────────────────────

    /**
     * Authenticate a user by username and password.
     *
     * @return bool True on success.
     */
    public static function login(string $username, string $password): bool
    {
        $db   = Database::getInstance();
        $user = $db->selectOne('users', 'username = ?', [$username]);

        if ($user === null) {
            return false;
        }

        if (!self::verifyPassword($password, $user['password'])) {
            return false;
        }

        // Regenerate session ID to prevent fixation
        session_regenerate_id(true);

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'] ?? $user['username'];
        $_SESSION['logged_in'] = true;

        return true;
    }

    // ── Logout ──────────────────────────────────────────────────────────────

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $p['path'],
                $p['domain'],
                $p['secure'],
                $p['httponly']
            );
        }

        session_destroy();
    }

    // ── Session Inspection ──────────────────────────────────────────────────

    /** Check whether a user is currently logged in. */
    public static function check(): bool
    {
        return !empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Return current user data from the session.
     *
     * @return array{id:int,username:string,role:string,name:string}|null
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        return [
            'id'       => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role'     => $_SESSION['user_role'],
            'name'     => $_SESSION['user_name'],
        ];
    }

    /** Check whether the current user has the "admin" role. */
    public static function isAdmin(): bool
    {
        return self::check() && ($_SESSION['user_role'] ?? '') === 'admin';
    }

    // ── CSRF Protection ─────────────────────────────────────────────────────

    /**
     * Generate a CSRF token (or return the existing one for this request).
     * Store it in the session so verifyCSRF() can compare later.
     */
    public static function generateCSRF(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify a submitted CSRF token against the session value.
     * Consumes the token on success to prevent replay.
     */
    public static function verifyCSRF(string $token): bool
    {
        if (empty($_SESSION['csrf_token'])) {
            return false;
        }

        $valid = hash_equals($_SESSION['csrf_token'], $token);

        if ($valid) {
            // One-time use — regenerate on next form render
            unset($_SESSION['csrf_token']);
        }

        return $valid;
    }

    // ── Password Hashing ────────────────────────────────────────────────────

    /** Hash a plaintext password using bcrypt. */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /** Verify a plaintext password against a bcrypt hash. */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
