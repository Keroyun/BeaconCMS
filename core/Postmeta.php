<?php
declare(strict_types=1);

/**
 * Postmeta Manager (ACF / WP Postmeta equivalent)
 * Allows attaching custom fields to any content type.
 */
class Postmeta
{
    /**
     * Get a specific meta value
     */
    public static function get(string $contentType, int $contentId, string $metaKey, $default = null)
    {
        $db = Database::getInstance();
        $row = $db->selectOne('postmeta', 'content_type = ? AND content_id = ? AND meta_key = ?', [$contentType, $contentId, $metaKey]);
        return $row ? $row['meta_value'] : $default;
    }

    /**
     * Set or update a meta value
     */
    public static function set(string $contentType, int $contentId, string $metaKey, string $metaValue): void
    {
        $db = Database::getInstance();
        
        $exists = $db->selectOne('postmeta', 'content_type = ? AND content_id = ? AND meta_key = ?', [$contentType, $contentId, $metaKey]);
        
        if ($exists) {
            $db->update('postmeta', ['meta_value' => $metaValue], 'id = ?', [(int)$exists['id']]);
        } else {
            $db->insert('postmeta', [
                'content_type' => $contentType,
                'content_id' => $contentId,
                'meta_key' => $metaKey,
                'meta_value' => $metaValue
            ]);
        }
    }

    /**
     * Get all meta data for a specific content item
     */
    public static function getAll(string $contentType, int $contentId): array
    {
        $db = Database::getInstance();
        $rows = $db->select('postmeta', 'content_type = ? AND content_id = ?', [$contentType, $contentId]);
        
        $meta = [];
        foreach ($rows as $row) {
            $meta[$row['meta_key']] = $row['meta_value'];
        }
        return $meta;
    }

    /**
     * Delete a meta value
     */
    public static function delete(string $contentType, int $contentId, string $metaKey): void
    {
        $db = Database::getInstance();
        $db->delete('postmeta', 'content_type = ? AND content_id = ? AND meta_key = ?', [$contentType, $contentId, $metaKey]);
    }
}
