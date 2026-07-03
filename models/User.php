<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Model.php';

/**
 * User Model
 * 
 * Handles user account data with automatic password hashing on creation.
 */
class User extends Model
{
    protected string $table = 'users';

    protected array $fillable = [
        'username',
        'email',
        'password',
        'role',
        'avatar',
    ];

    /**
     * Create a new user with bcrypt-hashed password.
     *
     * Overrides the parent create() to ensure the password field is always
     * hashed before being stored in the database.
     *
     * @param array $data
     * @return string The new user ID (from lastInsertId)
     */
    public function create(array $data): string
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        return parent::create($data);
    }

    /**
     * Find a user by their username.
     *
     * @param string $username
     * @return array|null
     */
    public function findByUsername(string $username): ?array
    {
        $db = Database::getInstance();
        return $db->selectOne($this->table, 'username = :username', ['username' => $username]);
    }

    /**
     * Find a user by their email address.
     *
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        $db = Database::getInstance();
        return $db->selectOne($this->table, 'email = :email', ['email' => $email]);
    }
}
