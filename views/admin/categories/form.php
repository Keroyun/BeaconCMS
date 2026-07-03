<?php
$isEdit = isset($category) && $category;
$pageTitle = $isEdit ? 'Edit ' . ($typeInfo['singular'] ?? 'Category') : 'Add New ' . ($typeInfo['singular'] ?? 'Category');
ob_start();
?>

<div class="content-header">
    <h1><i class="<?php echo he($typeInfo['icon'] ?? 'fa-solid fa-tag'); ?>"></i> <?php echo he($pageTitle); ?></h1>
    <a href="<?php echo url('/admin/categories?type=' . urlencode($taxonomyType)); ?>" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to <?php echo he($typeInfo['label'] ?? 'Categories'); ?>
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

<form method="POST" action="<?php echo url('/admin/categories/' . ($isEdit ? 'edit/' . $category['id'] : 'create?type=' . urlencode($taxonomyType))); ?>">
    <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRF(); ?>">

    <div class="form-grid">
        <div class="form-main">
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo he($category['name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="slug">Slug</label>
                        <input type="text" id="slug" name="slug" class="form-control" value="<?php echo he($category['slug'] ?? ''); ?>" placeholder="Leave blank to auto-generate from name">
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3"><?php echo he($category['description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-sidebar">
            <div class="card">
                <div class="card-header"><h3>Settings</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="color">Color / Badge</label>
                        <input type="color" id="color" name="color" class="form-control" style="height:40px" value="<?php echo he($category['color'] ?? '#6366f1'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control" value="<?php echo (int)($category['sort_order'] ?? 0); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa-solid fa-save"></i> <?php echo $isEdit ? 'Update' : 'Create'; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php
$content = ob_get_clean();
include dirname(dirname(__DIR__)) . '/admin/layout.php';
?>
