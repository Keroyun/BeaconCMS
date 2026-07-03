<?php
$isEdit = isset($snippet) && $snippet;
ob_start();
?>
<div class="content-header">
    <h1><i class="fa-solid fa-code"></i> <?php echo $pageTitle; ?></h1>
    <a href="<?php echo url('/admin/snippets'); ?>" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Snippets
    </a>
</div>

<?php if ($flash = View::flash('error')): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-exclamation-circle"></i> <?php echo he($flash); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo url('/admin/snippets/' . ($isEdit ? 'edit/' . $snippet['id'] : 'create')); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRF(); ?>">
            
            <div class="form-row">
                <div class="form-group col-8">
                    <label for="title">Snippet Name / Description <span class="required">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" value="<?php echo he($snippet['title'] ?? ''); ?>" required placeholder="e.g. Google Analytics">
                </div>
                <div class="form-group col-4">
                    <label for="location">Location <span class="required">*</span></label>
                    <select id="location" name="location" class="form-control" required>
                        <option value="header" <?php echo ($snippet['location'] ?? 'header') === 'header' ? 'selected' : ''; ?>>Header (Before &lt;/head&gt;)</option>
                        <option value="footer" <?php echo ($snippet['location'] ?? '') === 'footer' ? 'selected' : ''; ?>>Footer (Before &lt;/body&gt;)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="code_content">Code Content <span class="required">*</span></label>
                <textarea id="code_content" name="code_content" class="form-control" rows="12" style="font-family:monospace;" required placeholder="Paste your HTML/JS snippet here..."><?php echo htmlspecialchars($snippet['code_content'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                <small class="text-muted">Ensure you include the &lt;script&gt; or &lt;style&gt; tags if required.</small>
            </div>

            <div class="form-group">
                <label class="checkbox-item">
                    <input type="checkbox" name="is_active" value="1" <?php echo !isset($snippet) || $snippet['is_active'] ? 'checked' : ''; ?>>
                    <span>Active (inject into frontend)</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> <?php echo $isEdit ? 'Update Snippet' : 'Save Snippet'; ?></button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
include dirname(dirname(__DIR__)) . '/admin/layout.php';
?>
