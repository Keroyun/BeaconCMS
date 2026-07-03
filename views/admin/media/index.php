<?php
$pageTitle = 'Media Library';
ob_start();
?>

<div class="content-header">
    <h1><i class="fa-solid fa-images"></i> Media Library</h1>
</div>

<?php if ($flash = View::flash('success')): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?php echo he($flash); ?></div>
<?php endif; ?>
<?php if ($flash = View::flash('error')): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-exclamation-circle"></i> <?php echo he($flash); ?></div>
<?php endif; ?>

<!-- Upload Zone -->
<div class="card">
    <div class="card-body">
        <div class="dropzone" id="mediaDropzone">
            <div class="dropzone-content">
                <i class="fa-solid fa-cloud-upload-alt"></i>
                <h3>Drag & Drop Files Here</h3>
                <p>or click to browse</p>
                <input type="file" id="mediaFileInput" multiple accept="image/*,.pdf,.doc,.docx" class="file-input">
                <button type="button" class="btn btn-primary" onclick="document.getElementById('mediaFileInput').click()">
                    <i class="fa-solid fa-folder-open"></i> Browse Files
                </button>
            </div>
            <div class="upload-progress hidden" id="uploadProgress">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <p id="uploadStatus">Uploading...</p>
            </div>
        </div>
    </div>
</div>

<!-- Media Grid -->
<div class="card">
    <div class="card-header">
        <h3>Uploaded Files</h3>
        <span class="badge badge-info"><?php echo count($media ?? []); ?> files</span>
    </div>
    <div class="card-body">
        <?php if (empty($media)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-images"></i>
                <h3>No Media Files</h3>
                <p>Upload your first file using the drop zone above.</p>
            </div>
        <?php else: ?>
            <div class="media-grid">
                <?php foreach ($media as $item): ?>
                    <div class="media-item" id="media-<?php echo $item['id']; ?>">
                        <div class="media-preview">
                            <?php if (str_starts_with($item['mime_type'] ?? '', 'image/')): ?>
                                <img src="<?php echo he(url('/' . $item['path'])); ?>" alt="<?php echo he($item['alt_text'] ?? $item['original_name']); ?>" loading="lazy">
                            <?php else: ?>
                                <div class="file-icon">
                                    <i class="fa-solid fa-file-<?php echo match(true) {
                                        str_contains($item['mime_type'] ?? '', 'pdf') => 'pdf',
                                        str_contains($item['mime_type'] ?? '', 'word') => 'word',
                                        default => 'alt'
                                    }; ?>"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="media-info">
                            <p class="media-name" title="<?php echo he($item['original_name']); ?>"><?php echo he($item['original_name']); ?></p>
                            <small class="media-meta">
                                <?php echo $item['file_size'] ? round($item['file_size'] / 1024, 1) . ' KB' : ''; ?>
                                · <?php echo date('M j, Y', strtotime($item['created_at'])); ?>
                            </small>
                        </div>
                        <div class="media-actions">
                            <button type="button" class="btn btn-sm btn-info" onclick="copyMediaUrl('<?php echo he(url('/' . $item['path'])); ?>')" title="Copy URL">
                                <i class="fa-solid fa-link"></i>
                            </button>
                            <form method="POST" action="<?php echo url('/admin/media/delete/' . $item['id']); ?>" class="inline-form" onsubmit="return confirm('Delete this file permanently?');">
                                <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRF(); ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function copyMediaUrl(url) {
    navigator.clipboard.writeText(url).then(() => {
        showNotification('URL copied to clipboard!', 'success');
    });
}
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>
