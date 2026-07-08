<?php
/**
 * MediaManager — File Upload & Image Processing
 *
 * Handles file uploads, type/size validation, organised storage
 * (uploads/YYYY/MM/), thumbnail generation via GD, and deletion.
 */
class MediaManager
{
    /** Maximum upload size in bytes (default 5 MB). */
    private const MAX_SIZE = 5 * 1024 * 1024;

    /** Allowed MIME types. */
    private const ALLOWED_TYPES = [
        // Images
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        // Documents
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    // ── Upload ──────────────────────────────────────────────────────────────

    /**
     * Handle a file upload from $_FILES.
     *
     * @param  array $file  A single entry from $_FILES (e.g. $_FILES['image'])
     * @return array{filename:string,original_name:string,path:string,url:string,mime_type:string,size:int}
     * @throws RuntimeException on validation or I/O failure
     */
    public static function upload(array $file): array
    {
        // Basic upload-error check
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('File upload failed (error code: ' . ($file['error'] ?? 'unknown') . ').');
        }

        // Validate MIME type
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, self::ALLOWED_TYPES, true)) {
            throw new RuntimeException('File type not allowed: ' . $mimeType);
        }

        // Validate size
        if ($file['size'] > self::MAX_SIZE) {
            throw new RuntimeException('File exceeds the maximum upload size of ' . self::formatBytes(self::MAX_SIZE) . '.');
        }

        // Build target directory (uploads/YYYY/MM/)
        $uploadDir = self::getUploadPath();
        $fullDir   = BASE_PATH . '/' . $uploadDir;

        if (!is_dir($fullDir) && !mkdir($fullDir, 0755, true)) {
            throw new RuntimeException('Failed to create upload directory.');
        }

        // Sanitise filename and ensure uniqueness
        $originalName = basename($file['name']);
        $extension    = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $safeName     = self::sanitizeFilename(pathinfo($originalName, PATHINFO_FILENAME));
        $uniqueName   = $safeName . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $destination  = $fullDir . $uniqueName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Failed to move uploaded file.');
        }

        $relativePath = $uploadDir . $uniqueName;

        return [
            'filename'      => $uniqueName,
            'original_name' => $originalName,
            'path'          => $relativePath,
            'url'           => (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/' . $relativePath,
            'mime_type'     => $mimeType,
            'size'          => $file['size'],
        ];
    }

    // ── Delete ──────────────────────────────────────────────────────────────

    /**
     * Delete a file from disk given its path relative to BASE_PATH.
     */
    public static function delete(string $path): bool
    {
        $fullPath = BASE_PATH . '/' . ltrim($path, '/');

        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    // ── Thumbnail ───────────────────────────────────────────────────────────

    /**
     * Create a resized thumbnail of an image using GD.
     *
     * @param string $path   Relative path to the original image
     * @param int    $width  Desired width in pixels
     * @param int    $height Desired height in pixels (0 = auto-calculate)
     * @return string|null   Relative path to the thumbnail, or null on failure
     */
    public static function createThumbnail(string $path, int $width = 300, int $height = 0): ?string
    {
        $fullPath = BASE_PATH . '/' . ltrim($path, '/');

        if (!file_exists($fullPath)) {
            return null;
        }

        $info = getimagesize($fullPath);
        if ($info === false) {
            return null;
        }

        [$origW, $origH, $type] = $info;

        // Auto-calculate height maintaining aspect ratio
        if ($height <= 0) {
            $height = (int) round($origH * ($width / $origW));
        }

        // Create source image resource
        $source = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($fullPath),
            IMAGETYPE_PNG  => imagecreatefrompng($fullPath),
            IMAGETYPE_GIF  => imagecreatefromgif($fullPath),
            IMAGETYPE_WEBP => imagecreatefromwebp($fullPath),
            default        => null,
        };

        if ($source === null) {
            return null;
        }

        $thumb = imagecreatetruecolor($width, $height);

        // Preserve transparency for PNG / GIF / WEBP
        if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP], true)) {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
            imagefilledrectangle($thumb, 0, 0, $width, $height, $transparent);
        }

        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $width, $height, $origW, $origH);

        // Build thumb filename
        $ext       = pathinfo($fullPath, PATHINFO_EXTENSION);
        $basename  = pathinfo($fullPath, PATHINFO_FILENAME);
        $dir       = dirname($fullPath);
        $thumbName = $basename . "-{$width}x{$height}." . $ext;
        $thumbFull = $dir . '/' . $thumbName;

        $saved = match ($type) {
            IMAGETYPE_JPEG => imagejpeg($thumb, $thumbFull, 85),
            IMAGETYPE_PNG  => imagepng($thumb, $thumbFull, 8),
            IMAGETYPE_GIF  => imagegif($thumb, $thumbFull),
            IMAGETYPE_WEBP => imagewebp($thumb, $thumbFull, 85),
            default        => false,
        };

        imagedestroy($source);
        imagedestroy($thumb);

        if (!$saved) {
            return null;
        }

        // Return relative path
        return str_replace(BASE_PATH . '/', '', $thumbFull);
    }

    // ── Configuration Accessors ─────────────────────────────────────────────

    /** @return string[] */
    public static function getAllowedTypes(): array
    {
        return self::ALLOWED_TYPES;
    }

    /** Max upload size in bytes. */
    public static function getMaxSize(): int
    {
        return self::MAX_SIZE;
    }

    /**
     * Return the organised upload subdirectory for the current month.
     * e.g. "uploads/2026/07/"
     */
    public static function getUploadPath(): string
    {
        return 'uploads/' . date('Y') . '/' . date('m') . '/';
    }

    // ── Internal Helpers ────────────────────────────────────────────────────

    /**
     * Sanitise a filename: lowercase, strip non-alphanumeric, collapse hyphens.
     */
    private static function sanitizeFilename(string $name): string
    {
        $name = mb_strtolower($name);
        $name = preg_replace('/[^a-z0-9\-]/', '-', $name);
        $name = preg_replace('/-+/', '-', $name);
        return trim($name, '-');
    }

    /** Format bytes into a human-readable string. */
    private static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}
