<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/MediaManager.php';
require_once __DIR__ . '/../models/Media.php';

/**
 * MediaController
 * 
 * Admin controller for the media library: listing, uploading, and deleting files.
 */
class MediaController
{
    private Media $media;
    private MediaManager $mediaManager;

    public function __construct()
    {
        $this->media = new Media();
        $this->mediaManager = new MediaManager();
    }

    /**
     * Ensure the user is authenticated; redirect to login if not.
     */
    private function requireAuth(): void
    {
        if (!Auth::check()) {
            header('Location: ' . View::url('/admin/login'));
            exit;
        }
    }

    /**
     * Display the media library grid view.
     */
    public function index(): void
    {
        $this->requireAuth();

        $mediaItems = $this->media->all('created_at DESC');

        View::render('admin/media/index', [
            'pageTitle'  => 'Media Library',
            'mediaItems' => $mediaItems,
        ]);
    }

    /**
     * Handle file upload via POST. Returns JSON response for AJAX usage.
     *
     * Delegates physical file handling to MediaManager, then stores metadata
     * in the database.
     */
    public function upload(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        // CSRF check
        if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
            return;
        }

        // Check that a file was submitted
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error.']);
            return;
        }

        // Delegate upload to MediaManager (returns path info or false)
        $uploadResult = $this->mediaManager->upload($_FILES['file']);

        if (!$uploadResult) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to upload file.']);
            return;
        }

        // Store file metadata in the database
        $user = Auth::user();
        $mediaData = [
            'filename'      => $uploadResult['filename'],
            'original_name' => $_FILES['file']['name'],
            'mime_type'     => $_FILES['file']['type'],
            'file_size'     => $_FILES['file']['size'],
            'path'          => $uploadResult['path'],
            'alt_text'      => Sanitizer::clean($_POST['alt_text'] ?? ''),
            'uploaded_by'   => $user['id'],
        ];

        $mediaId = $this->media->create($mediaData);

        // Generate thumbnail for image files
        if (str_starts_with($_FILES['file']['type'], 'image/')) {
            $this->mediaManager->createThumbnail($uploadResult['path'], 300, 300);
        }

        $mediaRecord = $this->media->find($mediaId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'File uploaded successfully.',
            'media'   => $mediaRecord,
        ]);
    }

    /**
     * Delete a media file from both disk and database (POST only).
     *
     * @param int $id Media record ID
     */
    public function delete(int $id): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . View::url('/admin/media'));
            exit;
        }

        if (!Auth::verifyCSRF($_POST['csrf_token'] ?? '')) {
            View::setFlash('error', 'Invalid security token.');
            header('Location: ' . View::url('/admin/media'));
            exit;
        }

        $mediaItem = $this->media->find($id);
        if (!$mediaItem) {
            View::setFlash('error', 'Media file not found.');
            header('Location: ' . View::url('/admin/media'));
            exit;
        }

        // Remove the physical file from disk
        $this->mediaManager->delete($mediaItem['path']);

        // Remove the database record
        $this->media->delete($id);

        View::setFlash('success', 'Media file deleted successfully.');
        header('Location: ' . View::url('/admin/media'));
        exit;
    }
}
