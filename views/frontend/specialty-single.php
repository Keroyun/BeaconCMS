<?php
$pageTitle = he($specialty['name'] ?? 'Specialty');
$seoData = $seoData ?? [];
ob_start();
?>

<section class="page-hero page-hero-sm">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?php echo url('/'); ?>">Home</a>
            <span class="separator">/</span>
            <a href="<?php echo url('/specialties'); ?>">Specialties</a>
            <span class="separator">/</span>
            <span><?php echo he($specialty['name']); ?></span>
        </nav>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="specialty-header">
            <div class="specialty-icon-large">
                <i class="<?php echo he($specialty['icon'] ?? 'fa-solid fa-stethoscope'); ?>"></i>
            </div>
            <div class="specialty-info">
                <h1><?php echo he($specialty['name']); ?></h1>
                <?php if (!empty($specialty['description'])): ?>
                    <p class="specialty-description"><?php echo he($specialty['description']); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($consultants)): ?>
            <h2 class="section-title">Our <?php echo he($specialty['name']); ?> Specialists</h2>
            <div class="doctors-grid">
                <?php foreach ($consultants as $doc): ?>
                    <div class="doctor-card">
                        <div class="doctor-photo">
                            <?php if (!empty($doc['photo'])): ?>
                                <img src="<?php echo he(url('/' . $doc['photo'])); ?>" alt="<?php echo he($doc['name']); ?>" loading="lazy">
                            <?php else: ?>
                                <div class="photo-placeholder">
                                    <i class="fa-solid fa-user-doctor"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="doctor-info">
                            <h3><a href="<?php echo url('/doctors/' . $doc['slug']); ?>"><?php echo he($doc['name']); ?></a></h3>
                            <p class="doctor-specialty"><?php echo he($specialty['name']); ?></p>
                            <?php if (!empty($doc['qualifications'])): ?>
                                <p class="doctor-qualifications"><?php echo he(substr($doc['qualifications'], 0, 100)); ?></p>
                            <?php endif; ?>
                            <a href="<?php echo url('/doctors/' . $doc['slug']); ?>" class="btn btn-outline">View Profile</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-section">
                <i class="fa-solid fa-user-doctor"></i>
                <h3>No Consultants Listed</h3>
                <p>Information about our <?php echo he($specialty['name']); ?> specialists coming soon.</p>
            </div>
        <?php endif; ?>

        <div class="back-link">
            <a href="<?php echo url('/specialties'); ?>"><i class="fa-solid fa-arrow-left"></i> Back to All Specialties</a>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
