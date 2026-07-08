<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Model.php';

/**
 * Page Model
 * 
 * Handles CMS page data operations including published pages
 * and navigation menu ordering.
 */
class Page extends Model
{
    protected string $table = 'pages';

    protected array $fillable = [
        'title',
        'slug',
        'content',
        'template',
        'status',
        'sort_order',
        'parent_id',
        'seo_title',
        'seo_description',
        'og_image',
    ];

    /**
     * Get all published pages.
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
     * Get published pages ordered by sort_order for navigation menus.
     *
     * @return array
     */
    public function menuPages(): array
    {
        return $this->where(
            'status = :status',
            ['status' => 'published'],
            'sort_order ASC'
        );
    }
}
