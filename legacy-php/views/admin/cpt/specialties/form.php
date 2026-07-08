<?php
$isEdit = isset($specialty) && $specialty;
$pageTitle = $isEdit ? 'Edit Specialty' : 'Add New Specialty';
ob_start();
?>

<div class="content-header">
    <h1><i class="fa-solid fa-hospital"></i> <?php echo $pageTitle; ?></h1>
    <a href="<?php echo url('/admin/specialties'); ?>" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Specialties
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="error-list">
            <?php foreach ($errors as $error): ?>
                <li><?php echo he($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="<?php echo url('/admin/specialties/' . ($isEdit ? 'edit/' . $specialty['id'] : 'create')); ?>">
    <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRF(); ?>">

    <div class="form-grid">
        <div class="form-main">
            <div class="card">
                <div class="card-header"><h3>Specialty Details</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo he($specialty['name'] ?? ''); ?>" required placeholder="e.g., Cardiology">
                    </div>
                    <div class="form-group">
                        <label for="slug">Slug</label>
                        <input type="text" id="slug" name="slug" class="form-control" value="<?php echo he($specialty['slug'] ?? ''); ?>" placeholder="auto-generated">
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4" placeholder="Brief description of this medical specialty"><?php echo he($specialty['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="icon">Icon Class <small>(Font Awesome)</small></label>
                        <div class="icon-input-group">
                            <span class="icon-preview" id="iconPreview">
                                <i class="<?php echo he($specialty['icon'] ?? 'fa-solid fa-stethoscope'); ?>"></i>
                            </span>
                            <input type="text" id="icon" name="icon" class="form-control" value="<?php echo he($specialty['icon'] ?? ''); ?>" placeholder="e.g., fa-solid fa-heart-pulse">
                        </div>
                        <small class="form-hint">
                            Common medical icons: fa-solid fa-heart-pulse, fa-solid fa-brain, fa-solid fa-bone, fa-solid fa-lungs, fa-solid fa-eye, fa-solid fa-baby, fa-solid fa-syringe, fa-solid fa-stethoscope
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-sidebar">
            <div class="card">
                <div class="card-header"><h3>Settings</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="draft" <?php echo ($specialty['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo ($specialty['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control" value="<?php echo (int)($specialty['sort_order'] ?? 0); ?>" min="0">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa-solid fa-save"></i> <?php echo $isEdit ? 'Update Specialty' : 'Add Specialty'; ?>
                    </button>
                </div>
            </div>

            <!-- Language & Translation -->
            <div class="card">
                <div class="card-header"><h3><i class="fa-solid fa-globe"></i> Language</h3></div>
                <div class="card-body">
                    <?php 
                    $currentLang = isset($specialty['id']) ? Language::getContentLanguage('specialty', $specialty['id']) : Language::getDefault();
                    echo Language::renderAdminSelector('specialty', $specialty['id'] ?? null, $currentLang); 
                    ?>
                </div>
            </div>

            <!-- Categories -->
            <div class="card">
                <div class="card-header"><h3><i class="fa-solid fa-folder"></i> Categories</h3></div>
                <div class="card-body">
                    <?php 
                        echo Taxonomy::renderCheckboxes('specialty_category', 'specialty', $specialty['id'] ?? null);
                    ?>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Live icon preview
document.getElementById('icon').addEventListener('input', function() {
    document.getElementById('iconPreview').innerHTML = '<i class="' + this.value + '"></i>';
});
</script>

<?php
$content = ob_get_clean();
include dirname(dirname(__DIR__)) . '/admin/layout.php';
?>
