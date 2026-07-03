<?php
declare(strict_types=1);

/**
 * Form Model
 */
class Form extends Model
{
    protected string $table = 'forms';
    protected array $fillable = ['title', 'shortcode', 'fields_json', 'settings_json', 'status'];

    public function findByShortcode(string $shortcode): ?array
    {
        return $this->db->selectOne($this->table, 'shortcode = ?', [$shortcode]);
    }
}
