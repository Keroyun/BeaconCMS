<?php
$pageTitle = 'Promotions';
$seoData = $seoData ?? ['title' => 'Promotions', 'description' => 'Current promotions and special offers at Beacon Hospital'];
ob_start();
?>

<!-- Hero Banner -->
<section class="page-hero">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?php echo url('/'); ?>">Home</a>
            <span class="separator">/</span>
            <span>Promotions</span>
        </nav>
        <h1>Our Promotions</h1>
        <p>Discover our latest health packages and special offers</p>
    </div>
</section>

<!-- Active Promotions -->
<section class="section">
    <div class="container">
        <?php
        $activePromos = array_filter($promotions ?? [], function($p) {
            $now = date('Y-m-d');
            return $p['status'] === 'published' && (!$p['end_date'] || $p['end_date'] >= $now);
        });
        $expiredPromos = array_filter($promotions ?? [], function($p) {
            $now = date('Y-m-d');
            return $p['end_date'] && $p['end_date'] < $now;
        });
        ?>

        <?php if (!empty($activePromos)): ?>
            <h2 class="section-title">Active Promotions</h2>
            <div class="promotions-grid">
                <?php foreach ($activePromos as $promo): ?>
                    <article class="promo-card">
                        <div class="promo-image">
                            <?php if (!empty($promo['featured_image'])): ?>
                                <img src="<?php echo he(url('/' . $promo['featured_image'])); ?>" alt="<?php echo he($promo['title']); ?>" loading="lazy">
                            <?php else: ?>
                                <div class="promo-image-placeholder">
                                    <i class="fa-solid fa-tag"></i>
                                </div>
                            <?php endif; ?>
                            <?php if ($promo['end_date']): ?>
                                <span class="promo-badge">Ends <?php echo date('M j', strtotime($promo['end_date'])); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="promo-content">
                            <h3><a href="<?php echo url('/promotions/' . $promo['slug']); ?>"><?php echo he($promo['title']); ?></a></h3>
                            <p class="promo-date">
                                <i class="fa-solid fa-calendar"></i>
                                <?php echo date('M j, Y', strtotime($promo['start_date'] ?? $promo['created_at'])); ?>
                                <?php if ($promo['end_date']): ?>
                                    — <?php echo date('M j, Y', strtotime($promo['end_date'])); ?>
                                <?php else: ?>
                                    — Ongoing
                                <?php endif; ?>
                            </p>
                            <?php if (!empty($promo['description'])): ?>
                                <p class="promo-excerpt"><?php echo he(substr(strip_tags($promo['description']), 0, 150)); ?>...</p>
                            <?php endif; ?>
                            <a href="<?php echo url('/promotions/' . $promo['slug']); ?>" class="btn btn-primary">Learn More</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-section">
                <i class="fa-solid fa-tag"></i>
                <h3>No Active Promotions</h3>
                <p>Check back soon for new offers and health packages.</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($expiredPromos)): ?>
            <h2 class="section-title" style="margin-top: 3rem;">Past Promotions</h2>
            <div class="promotions-grid faded">
                <?php foreach ($expiredPromos as $promo): ?>
                    <article class="promo-card expired">
                        <div class="promo-image">
                            <?php if (!empty($promo['featured_image'])): ?>
                                <img src="<?php echo he(url('/' . $promo['featured_image'])); ?>" alt="<?php echo he($promo['title']); ?>" loading="lazy">
                            <?php endif; ?>
                            <span class="promo-badge expired-badge">Expired</span>
                        </div>
                        <div class="promo-content">
                            <h3><?php echo he($promo['title']); ?></h3>
                            <p class="promo-date">
                                <i class="fa-solid fa-calendar"></i>
                                Ended <?php echo date('M j, Y', strtotime($promo['end_date'])); ?>
                            </p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
