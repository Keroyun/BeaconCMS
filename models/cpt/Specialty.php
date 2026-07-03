<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Model.php';

/**
 * Specialty Model (Custom Post Type)
 * 
 * Handles medical specialty categories with consultant relationship counting.
 */
class Specialty extends Model
{
    protected string $table = 'specialties';

    protected array $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'status',
        'sort_order',
    ];

    /**
     * Get all published specialties ordered by sort_order.
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
     * Get all specialties with the count of consultants in each.
     *
     * Uses a LEFT JOIN and GROUP BY to include specialties even if they
     * have zero consultants.
     *
     * @return array
     */
    public function withConsultantCount(): array
    {
        $db = Database::getInstance();
        return $db->query(
            "SELECT s.*, COUNT(c.id) AS consultant_count
             FROM {$this->table} s
             LEFT JOIN consultants c ON s.id = c.specialty_id AND c.status = 'published'
             GROUP BY s.id
             ORDER BY s.sort_order ASC",
            []
        )->fetchAll(PDO::FETCH_ASSOC);
    }
}
