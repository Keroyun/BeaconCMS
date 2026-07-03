<?php
$pageTitle = 'Settings';
ob_start();
$settings = $settings ?? [];
?>

<div class="content-header">
    <h1><i class="fa-solid fa-gear"></i> Settings</h1>
</div>

<?php if ($flash = View::flash('success')): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?php echo he($flash); ?></div>
<?php endif; ?>
<?php if ($flash = View::flash('error')): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-exclamation-circle"></i> <?php echo he($flash); ?></div>
<?php endif; ?>

<form method="POST" action="<?php echo url('/admin/settings'); ?>" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRF(); ?>">

    <!-- Settings Tabs -->
    <div class="settings-tabs">
        <button type="button" class="tab-btn active" data-tab="general"><i class="fa-solid fa-globe"></i> General</button>
        <button type="button" class="tab-btn" data-tab="seo"><i class="fa-solid fa-search"></i> SEO</button>
        <button type="button" class="tab-btn" data-tab="social"><i class="fa-solid fa-share-nodes"></i> Social</button>
        <button type="button" class="tab-btn" data-tab="footer"><i class="fa-solid fa-shoe-prints"></i> Footer</button>
    </div>

    <!-- General Tab -->
    <div class="tab-content active" id="tab-general">
        <div class="card">
            <div class="card-header"><h3>General Settings</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label for="site_name">Site Name</label>
                    <input type="text" id="site_name" name="site_name" class="form-control" value="<?php echo he($settings['site_name'] ?? 'Beacon Hospital'); ?>">
                </div>
                <div class="form-group">
                    <label for="site_description">Site Description</label>
                    <textarea id="site_description" name="site_description" class="form-control" rows="2"><?php echo he($settings['site_description'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="site_url">Site URL</label>
                    <input type="url" id="site_url" name="site_url" class="form-control" value="<?php echo he($settings['site_url'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="site_logo">Site Logo</label>
                    <div class="image-upload-zone small-zone">
                        <?php if (!empty($settings['site_logo'])): ?>
                            <img src="<?php echo he(url('/' . $settings['site_logo'])); ?>" class="preview-image" alt="Logo">
                        <?php endif; ?>
                        <input type="file" name="site_logo" accept="image/*" class="file-input">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="this.previousElementSibling.click()">
                            <i class="fa-solid fa-upload"></i> Upload Logo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SEO Tab -->
    <div class="tab-content" id="tab-seo">
        <div class="card">
            <div class="card-header"><h3>SEO Defaults</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label for="seo_title_format">Title Format</label>
                    <input type="text" id="seo_title_format" name="seo_title_format" class="form-control" value="<?php echo he($settings['seo_title_format'] ?? '{title} | {site_name}'); ?>">
                    <small class="form-hint">Use {title} for page title and {site_name} for site name</small>
                </div>
                <div class="form-group">
                    <label for="default_meta_description">Default Meta Description</label>
                    <textarea id="default_meta_description" name="default_meta_description" class="form-control" rows="2" maxlength="160"><?php echo he($settings['default_meta_description'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="google_analytics_id">Google Analytics ID</label>
                    <input type="text" id="google_analytics_id" name="google_analytics_id" class="form-control" value="<?php echo he($settings['google_analytics_id'] ?? ''); ?>" placeholder="G-XXXXXXXXXX">
                </div>
                <div class="form-group">
                    <label for="default_og_image">Default OG Image</label>
                    <div class="image-upload-zone small-zone">
                        <?php if (!empty($settings['default_og_image'])): ?>
                            <img src="<?php echo he(url('/' . $settings['default_og_image'])); ?>" class="preview-image" alt="OG Image">
                        <?php endif; ?>
                        <input type="file" name="default_og_image" accept="image/*" class="file-input">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="this.previousElementSibling.click()">
                            <i class="fa-solid fa-upload"></i> Upload
                        </button>
                    </div>
                    <small class="form-hint">Recommended: 1200×630 pixels</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Social Tab -->
    <div class="tab-content" id="tab-social">
        <div class="card">
            <div class="card-header"><h3>Social Media Links</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label for="social_facebook"><i class="fa-brands fa-facebook"></i> Facebook</label>
                    <input type="url" id="social_facebook" name="social_facebook" class="form-control" value="<?php echo he($settings['social_facebook'] ?? ''); ?>" placeholder="https://facebook.com/beaconhospital">
                </div>
                <div class="form-group">
                    <label for="social_twitter"><i class="fa-brands fa-twitter"></i> Twitter / X</label>
                    <input type="url" id="social_twitter" name="social_twitter" class="form-control" value="<?php echo he($settings['social_twitter'] ?? ''); ?>" placeholder="https://twitter.com/beaconhospital">
                </div>
                <div class="form-group">
                    <label for="social_instagram"><i class="fa-brands fa-instagram"></i> Instagram</label>
                    <input type="url" id="social_instagram" name="social_instagram" class="form-control" value="<?php echo he($settings['social_instagram'] ?? ''); ?>" placeholder="https://instagram.com/beaconhospital">
                </div>
                <div class="form-group">
                    <label for="social_linkedin"><i class="fa-brands fa-linkedin"></i> LinkedIn</label>
                    <input type="url" id="social_linkedin" name="social_linkedin" class="form-control" value="<?php echo he($settings['social_linkedin'] ?? ''); ?>" placeholder="https://linkedin.com/company/beaconhospital">
                </div>
                <div class="form-group">
                    <label for="social_youtube"><i class="fa-brands fa-youtube"></i> YouTube</label>
                    <input type="url" id="social_youtube" name="social_youtube" class="form-control" value="<?php echo he($settings['social_youtube'] ?? ''); ?>" placeholder="https://youtube.com/@beaconhospital">
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Tab -->
    <div class="tab-content" id="tab-footer">
        <div class="card">
            <div class="card-header"><h3>Footer Settings</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label for="footer_text">Footer Text</label>
                    <textarea id="footer_text" name="footer_text" class="form-control" rows="3"><?php echo he($settings['footer_text'] ?? '© 2026 Beacon Hospital. All rights reserved.'); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="footer_address">Address</label>
                    <textarea id="footer_address" name="footer_address" class="form-control" rows="2"><?php echo he($settings['footer_address'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="footer_phone">Phone</label>
                    <input type="text" id="footer_phone" name="footer_phone" class="form-control" value="<?php echo he($settings['footer_phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="footer_email">Email</label>
                    <input type="email" id="footer_email" name="footer_email" class="form-control" value="<?php echo he($settings['footer_email'] ?? ''); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-save"></i> Save All Settings
        </button>
    </div>
</form>

<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
