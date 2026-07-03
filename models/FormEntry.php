<?php
declare(strict_types=1);

/**
 * FormEntry Model
 */
class FormEntry extends Model
{
    protected string $table = 'form_entries';
    protected array $fillable = ['form_id', 'entry_data_json', 'ip_address', 'user_agent'];

    public function getEntriesForForm(int $formId): array
    {
        return $this->db->select($this->table, 'form_id = ?', [$formId], 'created_at DESC');
    }
}
