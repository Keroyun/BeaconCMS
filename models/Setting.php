<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Model.php';

/**
 * Setting Model
 * 
 * Handles site settings stored as key-value pairs with optional grouping.
 */
class Setting extends Model
{
    protected string $table = 'settings';

    protected array $fillable = [
        'setting_key',
        'setting_value',
        'setting_group',
    ];

    /**
     * Get a single setting value by its key.
     *
     * @param string $key     The setting key to look up
     * @param mixed  $default Value to return if key is not found
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $db = Database::getInstance();
        $row = $db->selectOne($this->table, 'setting_key = :key', ['key' => $key]);

        return $row ? $row['setting_value'] : $default;
    }

    /**
     * Set a setting value. Creates the record if it doesn't exist, updates if it does.
     *
     * @param string $key
     * @param string $value
     * @param string $group
     * @return void
     */
    public function set(string $key, string $value, string $group = 'general'): void
    {
        $db = Database::getInstance();
        $existing = $db->selectOne($this->table, 'setting_key = :key', ['key' => $key]);

        if ($existing) {
            $db->update(
                $this->table,
                ['setting_value' => $value, 'setting_group' => $group],
                'setting_key = :key',
                ['key' => $key]
            );
        } else {
            $db->insert($this->table, [
                'setting_key'   => $key,
                'setting_value' => $value,
                'setting_group' => $group,
            ]);
        }
    }

    /**
     * Get all settings belonging to a specific group.
     *
     * @param string $group
     * @return array Array of setting rows
     */
    public function getGroup(string $group): array
    {
        return $this->where(
            'setting_group = :group',
            ['group' => $group],
            'setting_key ASC'
        );
    }

    /**
     * Get all settings as a flat associative array (key => value).
     *
     * @return array
     */
    public function getAllAsArray(): array
    {
        $rows = $this->all('setting_key ASC');
        $settings = [];

        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }
}
