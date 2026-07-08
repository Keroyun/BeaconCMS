<?php
declare(strict_types=1);

/**
 * Snippet Model (Header Footer Code Manager equivalent)
 */
class Snippet extends Model
{
    protected string $table = 'snippets';
    protected array $fillable = ['title', 'location', 'code_content', 'is_active'];

    public function getActiveSnippets(string $location): array
    {
        return $this->db->select($this->table, 'is_active = 1 AND location = ?', [$location]);
    }
}
