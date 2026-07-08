<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Model.php';

/**
 * Post Model
 * 
 * Handles blog post data operations including retrieval of published,
 * author-specific, and recent posts.
 */
class Post extends Model
{
    protected string $table = 'posts';

    protected array $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'status',
        'author_id',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'og_image',
    ];

    /**
     * Get all published posts ordered by creation date (newest first).
     *
     * @return array
     */
    public function published(): array
    {
        return $this->where(
            'status = :status',
            ['status' => 'published'],
            'created_at DESC'
        );
    }

    /**
     * Get posts by a specific author.
     *
     * @param int $authorId
     * @return array
     */
    public function byAuthor(int $authorId): array
    {
        return $this->where(
            'author_id = :author_id',
            ['author_id' => $authorId],
            'created_at DESC'
        );
    }

    /**
     * Get the most recent published posts.
     *
     * @param int $limit Maximum number of posts to return
     * @return array
     */
    public function recent(int $limit = 5): array
    {
        $db = Database::getInstance();
        return $db->query(
            "SELECT * FROM {$this->table} WHERE status = :status ORDER BY created_at DESC LIMIT :limit",
            ['status' => 'published', 'limit' => $limit]
        )->fetchAll(PDO::FETCH_ASSOC);
    }
}
