<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Model.php';

/**
 * Media Model
 * 
 * Handles media file records including filtering by type and uploader.
 */
class Media extends Model
{
    protected string $table = 'media';

    protected array $fillable = [
        'filename',
        'original_name',
        'mime_type',
        'file_size',
        'path',
        'alt_text',
        'uploaded_by',
    ];

    /**
     * Get only image files from the media library.
     *
     * @return array
     */
    public function images(): array
    {
        $db = Database::getInstance();
        return $db->query(
            "SELECT * FROM {$this->table} WHERE mime_type LIKE :mime ORDER BY created_at DESC",
            ['mime' => 'image/%']
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get media files uploaded by a specific user.
     *
     * @param int $userId
     * @return array
     */
    public function byUploader(int $userId): array
    {
        return $this->where(
            'uploaded_by = :uploaded_by',
            ['uploaded_by' => $userId],
            'created_at DESC'
        );
    }
}
