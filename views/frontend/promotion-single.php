<?php
$pageTitle = he($promotion['title'] ?? 'Promotion');
$seoData = $seoData ?? [];
ob_start();
?>

<section class="page-hero page-hero-sm">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?php echo url('/'); ?>">Home</a>
            <span class="separator">/</span>
            <a href="<?php echo url('/promotions'); ?>">Promotions</a>
            <span class="separator">/</span>
            <span><?php echo he($promotion['title']); ?></span>
        </nav>
    </div>
</section>

<section class="section">
    <div class="container container-narrow">
        <article class="promotion-detail">
            <?php if (!empty($promotion['featured_image'])): ?>
                <div class="promotion-hero-image">
                    <img src="<?php echo he(url('/' . $promotion['featured_image'])); ?>" alt="<?php echo he($promotion['title']); ?>">
                </div>
            <?php endif; ?>

            <div class="promotion-header">
                <h1><?php echo he($promotion['title']); ?></h1>
                <div class="promotion-meta">
                    <span class="promotion-dates">
                        <i class="fa-solid fa-calendar"></i>
                        <?php if ($promotion['start_date']): ?>
                            <?php echo date('F j, Y', strtotime($promotion['start_date'])); ?>
                        <?php endif; ?>
                        <?php if ($promotion['end_date']): ?>
                            — <?php echo date('F j, Y', strtotime($promotion['end_date'])); ?>
                        <?php else: ?>
                            — Ongoing
                        <?php endif; ?>
                    </span>
                    <?php
                    $now = date('Y-m-d');
                    $isActive = !$promotion['end_date'] || $promotion['end_date'] >= $now;
                    ?>
                    <span class="promotion-status <?php echo $isActive ? 'status-active' : 'status-expired'; ?>">
                        <?php echo $isActive ? '● Active' : '● Expired'; ?>
                    </span>
                </div>
            </div>

            <div class="promotion-body content-body">
                <?php echo $promotion['description']; ?>
            </div>

            <?php if ($isActive): ?>
                <div class="promotion-cta">
                    <h3>Interested in this promotion?</h3>
                    <p>Contact us to learn more or book an appointment.</p>
                    <a href="<?php echo url('/page/contact'); ?>" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-phone"></i> Contact Us
                    </a>
                </div>
            <?php endif; ?>
        </article>

        <div class="back-link">
            <a href="<?php echo url('/promotions'); ?>"><i class="fa-solid fa-arrow-left"></i> Back to All Promotions</a>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
