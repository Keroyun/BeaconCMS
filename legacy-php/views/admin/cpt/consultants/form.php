<?php
$isEdit = isset($consultant) && $consultant;
$pageTitle = $isEdit ? 'Edit Consultant' : 'Add New Consultant';
ob_start();
?>

<div class="content-header">
    <h1><i class="fa-solid fa-user-doctor"></i> <?php echo $pageTitle; ?></h1>
    <a href="<?php echo url('/admin/consultants'); ?>" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Consultants
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

<form method="POST" action="<?php echo url('/admin/consultants/' . ($isEdit ? 'edit/' . $consultant['id'] : 'create')); ?>" enctype="multipart/form-data" id="consultantForm">
    <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCSRF(); ?>">

    <div class="form-grid">
        <div class="form-main">
            <!-- Basic Info -->
            <div class="card">
                <div class="card-header"><h3><i class="fa-solid fa-id-card"></i> Basic Information</h3></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="name">Full Name <span class="required">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" value="<?php echo he($consultant['name'] ?? ''); ?>" required placeholder="e.g., Dr. Ahmad bin Hassan">
                        </div>
                        <div class="form-group col-6">
                            <label for="slug">Slug</label>
                            <input type="text" id="slug" name="slug" class="form-control" value="<?php echo he($consultant['slug'] ?? ''); ?>" placeholder="auto-generated">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="specialty_id">Specialty</label>
                        <select id="specialty_id" name="specialty_id" class="form-control">
                            <option value="">Select Specialty</option>
                            <?php if (!empty($specialties)):
                                foreach ($specialties as $specialty): ?>
                                    <option value="<?php echo $specialty['id']; ?>" <?php echo ($consultant['specialty_id'] ?? '') == $specialty['id'] ? 'selected' : ''; ?>>
                                        <?php echo he($specialty['name']); ?>
                                    </option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="qualifications">Qualifications</label>
                        <textarea id="qualifications" name="qualifications" class="form-control" rows="3" placeholder="e.g., MBBS (UM), MRCP (UK), FRCP (London)"><?php echo he($consultant['qualifications'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="experience">Experience</label>
                        <textarea id="experience" name="experience" class="form-control" rows="3" placeholder="Years of experience, previous positions..."><?php echo he($consultant['experience'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Bio -->
            <div class="card">
                <div class="card-header"><h3><i class="fa-solid fa-file-medical"></i> Biography</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="bio">Full Bio</label>
                        <textarea id="bio" name="bio" class="form-control wysiwyg" rows="10"><?php echo he($consultant['bio'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Contact & Clinic -->
            <div class="card">
                <div class="card-header"><h3><i class="fa-solid fa-clock"></i> Clinic & Contact</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="clinic_hours">Clinic Hours</label>
                        <textarea id="clinic_hours" name="clinic_hours" class="form-control" rows="3" placeholder="Mon-Fri: 9:00 AM - 5:00 PM&#10;Sat: 9:00 AM - 1:00 PM"><?php echo he($consultant['clinic_hours'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="contact_number">Contact Number</label>
                            <input type="text" id="contact_number" name="contact_number" class="form-control" value="<?php echo he($consultant['contact_number'] ?? ''); ?>" placeholder="+603-1234 5678">
                        </div>
                        <div class="form-group col-6">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo he($consultant['email'] ?? ''); ?>" placeholder="dr.ahmad@beaconhospital.com">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="booking_link">Booking Link</label>
                        <input type="url" id="booking_link" name="booking_link" class="form-control" value="<?php echo he($consultant['booking_link'] ?? ''); ?>" placeholder="https://booking.beaconhospital.com/dr-ahmad">
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
                    <div class="seo-preview">
                        <div class="seo-preview-title" id="seoPreviewTitle"><?php echo he($consultant['seo_title'] ?? $consultant['name'] ?? 'Doctor Name'); ?> | Beacon Hospital</div>
                        <div class="seo-preview-url"><?php echo he(SITE_URL ?? ''); ?>/doctors/<span id="seoPreviewSlug"><?php echo he($consultant['slug'] ?? 'doctor-slug'); ?></span></div>
                        <div class="seo-preview-desc" id="seoPreviewDesc"><?php echo he($consultant['seo_description'] ?? ''); ?></div>
                    </div>
                    <div class="form-group">
                        <label for="seo_title">SEO Title <small class="char-count">(<span id="seoTitleCount">0</span>/60)</small></label>
                        <input type="text" id="seo_title" name="seo_title" class="form-control" value="<?php echo he($consultant['seo_title'] ?? ''); ?>" maxlength="60">
                    </div>
                    <div class="form-group">
                        <label for="seo_description">Meta Description <small class="char-count">(<span id="seoDescCount">0</span>/160)</small></label>
                        <textarea id="seo_description" name="seo_description" class="form-control" rows="2" maxlength="160"><?php echo he($consultant['seo_description'] ?? ''); ?></textarea>
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
                            <option value="draft" <?php echo ($consultant['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo ($consultant['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control" value="<?php echo (int)($consultant['sort_order'] ?? 0); ?>" min="0">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa-solid fa-save"></i> <?php echo $isEdit ? 'Update Consultant' : 'Add Consultant'; ?>
                    </button>
                </div>
            </div>

            <!-- Language & Translation -->
            <div class="card">
                <div class="card-header"><h3><i class="fa-solid fa-globe"></i> Language</h3></div>
                <div class="card-body">
                    <?php 
                    $currentLang = isset($consultant['id']) ? Language::getContentLanguage('consultant', $consultant['id']) : Language::getDefault();
                    echo Language::renderAdminSelector('consultant', $consultant['id'] ?? null, $currentLang); 
                    ?>
                </div>
            </div>

            <!-- Categories / Taxonomies -->
            <div class="card">
                <div class="card-header"><h3><i class="fa-solid fa-tags"></i> Categories</h3></div>
                <div class="card-body">
                    <?php 
                        echo Taxonomy::renderCheckboxes('doctor_category', 'consultant', $consultant['id'] ?? null);
                        echo '<hr style="border-color:#2d3148;margin:1rem 0;">';
                        echo Taxonomy::renderCheckboxes('language_spoken', 'consultant', $consultant['id'] ?? null);
                    ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3>Photo</h3></div>
                <div class="card-body">
                    <div class="image-upload-zone">
                        <?php if (!empty($consultant['photo'])): ?>
                            <img src="<?php echo he(url('/' . $consultant['photo'])); ?>" class="preview-image" id="photoPreview" alt="Doctor photo">
                        <?php else: ?>
                            <div class="upload-placeholder" id="uploadPlaceholder">
                                <i class="fa-solid fa-camera"></i>
                                <p>Upload Photo</p>
                            </div>
                            <img src="" class="preview-image hidden" id="photoPreview" alt="Doctor photo preview">
                        <?php endif; ?>
                        <input type="file" id="photo" name="photo" accept="image/*" class="file-input">
                    </div>
                    <small class="form-hint">Recommended: 400×400px, square crop</small>
                </div>
            </div>
        </div>
    </div>
</form>

<?php
$content = ob_get_clean();
include dirname(dirname(__DIR__)) . '/admin/layout.php';
?>
