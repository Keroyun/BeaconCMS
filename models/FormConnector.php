<?php
declare(strict_types=1);

/**
 * FormConnector Model
 */
class FormConnector extends Model
{
    protected string $table = 'form_connectors';
    protected array $fillable = ['form_id', 'connector_type', 'config_json', 'is_active'];

    public function getActiveConnectorsForForm(int $formId): array
    {
        return $this->db->select($this->table, 'form_id = ? AND is_active = 1', [$formId]);
    }
}
