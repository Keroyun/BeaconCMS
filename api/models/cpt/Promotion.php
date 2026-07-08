<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Model.php';

/**
 * Promotion Model (Custom Post Type)
 * 
 * Handles promotional content with date-range-based status filtering
 * (active, upcoming, expired).
 */
class Promotion extends Model
{
    protected string $table = 'promotions';

    protected array $fillable = [
        'title',
        'slug',
        'description',
        'featured_image',
        'start_date',
        'end_date',
        'status',
        'seo_title',
        'seo_description',
    ];

    /**
     * Get currently active promotions.
     *
     * A promotion is active when it is published, its start_date is in the past
     * or today, and its end_date is in the future/today or null (no expiry).
     *
     * @return array
     */
    public function active(): array
    {
        $db = Database::getInstance();
        $now = date('Y-m-d');
        return $db->query(
            "SELECT * FROM {$this->table}
             WHERE status = :status
               AND start_date <= :now_start
               AND (end_date >= :now_end OR end_date IS NULL)
             ORDER BY start_date ASC",
            [
                'status'    => 'published',
                'now_start' => $now,
                'now_end'   => $now,
            ]
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get upcoming promotions (published, start_date in the future).
     *
     * @return array
     */
    public function upcoming(): array
    {
        $db = Database::getInstance();
        $now = date('Y-m-d');
        return $db->query(
            "SELECT * FROM {$this->table}
             WHERE status = :status
               AND start_date > :now
             ORDER BY start_date ASC",
            [
                'status' => 'published',
                'now'    => $now,
            ]
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get expired promotions (published, end_date in the past).
     *
     * @return array
     */
    public function expired(): array
    {
        $db = Database::getInstance();
        $now = date('Y-m-d');
        return $db->query(
            "SELECT * FROM {$this->table}
             WHERE status = :status
               AND end_date < :now
             ORDER BY end_date DESC",
            [
                'status' => 'published',
                'now'    => $now,
            ]
        )->fetchAll(PDO::FETCH_ASSOC);
    }
}
