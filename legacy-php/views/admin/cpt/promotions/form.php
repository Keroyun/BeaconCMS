<?php
$isEdit = isset($promotion) && $promotion;
$pageTitle = $isEdit ? 'Edit Promotion' : 'Add New Promotion';
ob_start();
?>

<div class="content-header">
    <h1><i class="fa-solid fa-bullhorn"></i> <?php echo $pageTitle; ?></h1>
    <a href="<?php echo url('/admin/promotions'); ?>" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Promotions
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

<form method="POST" action="<?php echo url('/admin/promotions/' . ($isEdit ? 'edit/' . $promotion['id'] : 'create')); ?>" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRF(); ?>">

    <div class="form-grid">
        <div class="form-main">
            <div class="card">
                <div class="card-header"><h3>Promotion Details</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="title">Title <span class="required">*</span></label>
                        <input type="text" id="title" name="title" class="form-control" value="<?php echo he($promotion['title'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="slug">Slug</label>
                        <input type="text" id="slug" name="slug" class="form-control" value="<?php echo he($promotion['slug'] ?? ''); ?>" placeholder="auto-generated">
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control wysiwyg" rows="10"><?php echo he($promotion['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="start_date"><i class="fa-solid fa-calendar-plus"></i> Start Date</label>
                            <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo he($promotion['start_date'] ?? ''); ?>">
                        </div>
                        <div class="form-group col-6">
                            <label for="end_date"><i class="fa-solid fa-calendar-minus"></i> End Date</label>
                            <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo he($promotion['end_date'] ?? ''); ?>">
                            <small class="form-hint">Leave blank for ongoing promotions</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO -->
            <div class="card seo-card">
                <div class="card-header collapsible" data-toggle="seo-section">
                    <h3><i class="fa-solid fa-search"></i> SEO Settings</h3>
                    <i class="fa-solid fa-chevron-down toggle-icon"></i>
                </div>
                <div class="card-body seo-section" id="seo-section">
                    <div class="form-group">
                        <label for="seo_title">SEO Title</label>
                        <input type="text" id="seo_title" name="seo_title" class="form-control" value="<?php echo he($promotion['seo_title'] ?? ''); ?>" maxlength="60">
                    </div>
                    <div class="form-group">
                        <label for="seo_description">Meta Description</label>
                        <textarea id="seo_description" name="seo_description" class="form-control" rows="2" maxlength="160"><?php echo he($promotion['seo_description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-sidebar">
            <div class="card">
                <div class="card-header"><h3>Publish</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="draft" <?php echo ($promotion['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo ($promotion['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa-solid fa-save"></i> <?php echo $isEdit ? 'Update' : 'Create'; ?> Promotion
                    </button>
                </div>
            </div>

            <!-- Media -->
            <div class="card">
                <div class="card-header"><h3>Featured Image</h3></div>
                <div class="card-body">
                    <div class="image-upload-zone">
                        <?php if (!empty($promotion['featured_image'])): ?>
                            <img src="<?php echo he(View::asset($promotion['featured_image'])); ?>" alt="Featured" class="preview-image" style="max-width:100%; height:auto; margin-bottom:10px;">
                        <?php endif; ?>
                        <input type="file" id="featured_image" name="featured_image" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><h3>Home Banner Image</h3></div>
                <div class="card-body">
                    <p class="text-muted" style="font-size:0.85rem;">Upload a specific banner to be shown on the Homepage if this promotion is featured.</p>
                    <div class="image-upload-zone">
                        <?php 
                        $homeBanner = isset($promotion['id']) ? Postmeta::get('promotion', $promotion['id'], 'home_banner_image') : null;
                        if (!empty($homeBanner)): 
                        ?>
                            <img src="<?php echo he(View::asset($homeBanner)); ?>" alt="Home Banner" class="preview-image" style="max-width:100%; height:auto; margin-bottom:10px;">
                        <?php endif; ?>
                        <input type="file" id="home_banner_image" name="home_banner_image" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php
$content = ob_get_clean();
include dirname(dirname(__DIR__)) . '/admin/layout.php';
?>
