<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Model.php';

/**
 * Consultant Model (Custom Post Type)
 * 
 * Handles consultant/doctor profile data with specialty relationships.
 */
class Consultant extends Model
{
    protected string $table = 'consultants';

    protected array $fillable = [
        'name',
        'slug',
        'photo',
        'specialty_id',
        'qualifications',
        'experience',
        'bio',
        'clinic_hours',
        'contact_number',
        'email',
        'booking_link',
        'status',
        'sort_order',
        'seo_title',
        'seo_description',
    ];

    /**
     * Get all published consultants ordered by sort_order.
     *
     * @return array
     */
    public function published(): array
    {
        return $this->where(
            'status = :status',
            ['status' => 'published'],
            'sort_order ASC'
        );
    }

    /**
     * Get consultants belonging to a specific specialty.
     *
     * @param int $specialtyId
     * @return array
     */
    public function bySpecialty(int $specialtyId): array
    {
        return $this->where(
            'specialty_id = :specialty_id AND status = :status',
            ['specialty_id' => $specialtyId, 'status' => 'published'],
            'sort_order ASC'
        );
    }

    /**
     * Get all consultants with their specialty name via JOIN.
     *
     * @return array
     */
    public function withSpecialty(): array
    {
        $db = Database::getInstance();
        return $db->query(
            "SELECT c.*, s.name AS specialty_name
             FROM {$this->table} c
             LEFT JOIN specialties s ON c.specialty_id = s.id
             ORDER BY c.sort_order ASC",
            []
        )->fetchAll(PDO::FETCH_ASSOC);
    }
}
