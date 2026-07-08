<?php
$pageTitle = 'Our Specialties';
$seoData = $seoData ?? ['title' => 'Medical Specialties', 'description' => 'Explore our comprehensive range of medical specialties at Beacon Hospital'];
ob_start();
?>

<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?php echo url('/'); ?>">Home</a>
            <span class="separator">/</span>
            <span>Specialties</span>
        </nav>
        <h1>Our Medical Specialties</h1>
        <p>Comprehensive healthcare services across all major medical disciplines</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (empty($specialties)): ?>
            <div class="empty-section">
                <i class="fa-solid fa-hospital"></i>
                <h3>No Specialties Listed</h3>
                <p>Specialty information coming soon.</p>
            </div>
        <?php else: ?>
            <div class="specialties-grid">
                <?php foreach ($specialties as $specialty): ?>
                    <a href="<?php echo url('/specialties/' . $specialty['slug']); ?>" class="specialty-card-link">
                        <div class="specialty-card">
                            <div class="specialty-icon">
                                <i class="<?php echo he($specialty['icon'] ?? 'fa-solid fa-stethoscope'); ?>"></i>
                            </div>
                            <h3><?php echo he($specialty['name']); ?></h3>
                            <?php if (!empty($specialty['description'])): ?>
                                <p><?php echo he(substr($specialty['description'], 0, 120)); ?><?php echo strlen($specialty['description']) > 120 ? '...' : ''; ?></p>
                            <?php endif; ?>
                            <?php if (isset($specialty['consultant_count'])): ?>
                                <span class="specialty-count">
                                    <i class="fa-solid fa-user-doctor"></i>
                                    <?php echo (int)$specialty['consultant_count']; ?> Doctor<?php echo $specialty['consultant_count'] != 1 ? 's' : ''; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
