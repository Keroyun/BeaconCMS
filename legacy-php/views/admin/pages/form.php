<?php
$isEdit = isset($page) && $page;
$pageTitle = $isEdit ? 'Edit Page' : 'Add New Page';
ob_start();
?>

<div class="content-header">
    <h1><i class="fa-solid fa-file"></i> <?php echo $pageTitle; ?></h1>
    <a href="<?php echo url('/admin/pages'); ?>" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Pages
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

<form method="POST" action="<?php echo url('/admin/pages/' . ($isEdit ? 'edit/' . $page['id'] : 'create')); ?>" id="pageForm">
    <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRF(); ?>">

    <div class="form-grid">
        <div class="form-main">
            <div class="card">
                <div class="card-header"><h3>Page Content</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="title">Title <span class="required">*</span></label>
                        <input type="text" id="title" name="title" class="form-control" value="<?php echo he($page['title'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="slug">Slug</label>
                        <input type="text" id="slug" name="slug" class="form-control" value="<?php echo he($page['slug'] ?? ''); ?>" placeholder="auto-generated-from-title">
                    </div>
                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea id="content" name="content" class="form-control wysiwyg" rows="15"><?php echo he($page['content'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- SEO Section -->
            <div class="card seo-card">
                <div class="card-header collapsible" data-toggle="seo-section">
                    <h3><i class="fa-solid fa-search"></i> SEO Settings</h3>
                    <i class="fa-solid fa-chevron-down toggle-icon"></i>
                </div>
                <div class="card-body seo-section" id="seo-section">
                    <div class="form-group">
                        <label for="seo_title">SEO Title</label>
                        <input type="text" id="seo_title" name="seo_title" class="form-control" value="<?php echo he($page['seo_title'] ?? ''); ?>" maxlength="60">
                    </div>
                    <div class="form-group">
                        <label for="seo_description">Meta Description</label>
                        <textarea id="seo_description" name="seo_description" class="form-control" rows="2" maxlength="160"><?php echo he($page['seo_description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-sidebar">
            <div class="card">
                <div class="card-header"><h3>Page Settings</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="draft" <?php echo ($page['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo ($page['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="template">Template</label>
                        <select id="template" name="template" class="form-control">
                            <option value="default" <?php echo ($page['template'] ?? '') === 'default' ? 'selected' : ''; ?>>Default</option>
                            <option value="full-width" <?php echo ($page['template'] ?? '') === 'full-width' ? 'selected' : ''; ?>>Full Width</option>
                            <option value="sidebar" <?php echo ($page['template'] ?? '') === 'sidebar' ? 'selected' : ''; ?>>With Sidebar</option>
                            <option value="contact" <?php echo ($page['template'] ?? '') === 'contact' ? 'selected' : ''; ?>>Contact Page</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="parent_id">Parent Page</label>
                        <select id="parent_id" name="parent_id" class="form-control">
                            <option value="">None (Top Level)</option>
                            <?php if (!empty($allPages)):
                                foreach ($allPages as $p):
                                    if ($isEdit && $p['id'] == $page['id']) continue;
                            ?>
                                <option value="<?php echo $p['id']; ?>" <?php echo ($page['parent_id'] ?? '') == $p['id'] ? 'selected' : ''; ?>>
                                    <?php echo he($p['title']); ?>
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control" value="<?php echo (int)($page['sort_order'] ?? 0); ?>" min="0">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa-solid fa-save"></i> <?php echo $isEdit ? 'Update Page' : 'Create Page'; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>
