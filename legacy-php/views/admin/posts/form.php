<?php
$isEdit = isset($post) && $post;
$pageTitle = $isEdit ? 'Edit Post' : 'Add New Post';
ob_start();
?>

<div class="content-header">
    <h1><i class="fa-solid fa-file-pen"></i> <?php echo $pageTitle; ?></h1>
    <a href="<?php echo url('/admin/posts'); ?>" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Posts
    </a>
</div>

<?php if ($flash = View::flash('error')): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-exclamation-circle"></i> <?php echo he($flash); ?></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="error-list">
            <?php foreach ($errors as $error): ?>
                <li><?php echo he($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="<?php echo url('/admin/posts/' . ($isEdit ? 'edit/' . $post['id'] : 'create')); ?>" enctype="multipart/form-data" id="postForm">
    <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRF(); ?>">

    <div class="form-grid">
        <div class="form-main">
            <div class="card">
                <div class="card-header"><h3>Post Content</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="title">Title <span class="required">*</span></label>
                        <input type="text" id="title" name="title" class="form-control" value="<?php echo he($post['title'] ?? View::old('title', '')); ?>" required placeholder="Enter post title">
                    </div>
                    <div class="form-group">
                        <label for="slug">Slug</label>
                        <div class="input-group">
                            <span class="input-prefix"><?php echo he(SITE_URL ?? ''); ?>/blog/</span>
                            <input type="text" id="slug" name="slug" class="form-control" value="<?php echo he($post['slug'] ?? View::old('slug', '')); ?>" placeholder="auto-generated-from-title">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea id="content" name="content" class="form-control wysiwyg" rows="15"><?php echo he($post['content'] ?? View::old('content', '')); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="excerpt">Excerpt</label>
                        <textarea id="excerpt" name="excerpt" class="form-control" rows="3" placeholder="Brief summary of the post"><?php echo he($post['excerpt'] ?? View::old('excerpt', '')); ?></textarea>
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
                    <div class="seo-preview">
                        <div class="seo-preview-title" id="seoPreviewTitle"><?php echo he($post['seo_title'] ?? $post['title'] ?? 'Post Title'); ?> | Beacon Hospital</div>
                        <div class="seo-preview-url"><?php echo he(SITE_URL ?? 'https://beaconhospital.com'); ?>/blog/<span id="seoPreviewSlug"><?php echo he($post['slug'] ?? 'post-slug'); ?></span></div>
                        <div class="seo-preview-desc" id="seoPreviewDesc"><?php echo he($post['seo_description'] ?? 'Post description will appear here...'); ?></div>
                    </div>
                    <div class="form-group">
                        <label for="seo_title">SEO Title <small class="char-count">(<span id="seoTitleCount">0</span>/60)</small></label>
                        <input type="text" id="seo_title" name="seo_title" class="form-control" value="<?php echo he($post['seo_title'] ?? ''); ?>" maxlength="60" placeholder="Custom SEO title (defaults to post title)">
                    </div>
                    <div class="form-group">
                        <label for="seo_description">Meta Description <small class="char-count">(<span id="seoDescCount">0</span>/160)</small></label>
                        <textarea id="seo_description" name="seo_description" class="form-control" rows="2" maxlength="160" placeholder="Custom meta description for search engines"><?php echo he($post['seo_description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="seo_keywords">Keywords</label>
                        <input type="text" id="seo_keywords" name="seo_keywords" class="form-control" value="<?php echo he($post['seo_keywords'] ?? ''); ?>" placeholder="keyword1, keyword2, keyword3">
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
                            <option value="draft" <?php echo ($post['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo ($post['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="archived" <?php echo ($post['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa-solid fa-save"></i> <?php echo $isEdit ? 'Update Post' : 'Create Post'; ?>
                    </button>
                </div>
            </div>

            <!-- Language & Translation -->
            <div class="card">
                <div class="card-header"><h3><i class="fa-solid fa-globe"></i> Language</h3></div>
                <div class="card-body">
                    <?php 
                    $currentLang = isset($post['id']) ? Language::getContentLanguage('post', $post['id']) : Language::getDefault();
                    echo Language::renderAdminSelector('post', $post['id'] ?? null, $currentLang); 
                    ?>
                </div>
            </div>

            <!-- Categories -->
            <div class="card">
                <div class="card-header"><h3><i class="fa-solid fa-folder"></i> Categories</h3></div>
                <div class="card-body">
                    <?php 
                        echo Taxonomy::renderCheckboxes('post_category', 'post', $post['id'] ?? null);
                    ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3>Featured Image</h3></div>
                <div class="card-body">
                    <div class="image-upload-zone" id="featuredImageZone">
                        <?php if (!empty($post['featured_image'])): ?>
                            <img src="<?php echo he($post['featured_image']); ?>" class="preview-image" id="featuredPreview" alt="Featured image">
                            <button type="button" class="btn btn-sm btn-danger remove-image" onclick="removeImage()">
                                <i class="fa-solid fa-times"></i> Remove
                            </button>
                        <?php else: ?>
                            <div class="upload-placeholder" id="uploadPlaceholder">
                                <i class="fa-solid fa-cloud-upload-alt"></i>
                                <p>Click or drag to upload</p>
                            </div>
                            <img src="" class="preview-image hidden" id="featuredPreview" alt="Featured image preview">
                        <?php endif; ?>
                        <input type="file" id="featured_image" name="featured_image" accept="image/*" class="file-input">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layout.php';
?>
