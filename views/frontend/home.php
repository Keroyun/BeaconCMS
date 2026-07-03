<?php
/**
 * BeaconCMS — Homepage
 *
 * Variables:
 *   $specialties  — array of specialty objects/arrays
 *   $consultants  — array of featured consultants
 *   $promotions   — array of active promotions
 *   $posts        — array of recent blog posts
 */

$pageTitle = 'Home';
$seoData   = $seoData ?? [];
$activeNav = 'home';
$bodyClass = 'page-home';

$specialties = $specialties ?? [];
$consultants = $consultants ?? [];
$promotions  = $promotions  ?? [];
$posts       = $posts       ?? [];

$siteUrl = defined('SITE_URL') ? SITE_URL : '/';

ob_start();
?>

<!-- ========== Hero Section ========== -->
<section class="hero">
    <div class="hero__content">
        <div class="hero__badge">🏥 Trusted Healthcare Excellence</div>
        <h1 class="hero__title">Welcome to Beacon Hospital</h1>
        <p class="hero__subtitle">World-class medical care delivered with compassion and expertise. Your health is our priority — from preventive care to advanced treatments.</p>
        <div class="hero__actions">
            <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>consultants" class="btn btn--primary btn--lg">Find a Doctor</a>
            <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>promotions" class="btn btn--outline btn--lg">View Promotions</a>
        </div>
    </div>
</section>

<!-- ========== Our Specialties ========== -->
<?php if (!empty($specialties)): ?>
<section class="section section--alt">
    <div class="container">
        <div class="section__header reveal">
            <h2 class="section__title">Our Specialties</h2>
            <p class="section__subtitle">Comprehensive medical expertise across a wide range of specialties, all under one roof.</p>
        </div>
        <div class="card-grid card-grid--4">
            <?php foreach (array_slice($specialties, 0, 8) as $index => $specialty): ?>
                <div class="specialty-card reveal reveal--delay-<?php echo ($index % 4) + 1; ?>">
                    <div class="specialty-card__icon">
                        <?php echo htmlspecialchars($specialty['icon'] ?? '🩺', ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <h3 class="specialty-card__name">
                        <a href="<?php echo htmlspecialchars($siteUrl . 'specialties/' . ($specialty['slug'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars($specialty['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </h3>
                    <p class="specialty-card__description">
                        <?php echo htmlspecialchars($specialty['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4 reveal">
            <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>specialties" class="btn btn--outline-dark">View All Specialties</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ========== Featured Doctors ========== -->
<?php if (!empty($consultants)): ?>
<section class="section">
    <div class="container">
        <div class="section__header reveal">
            <h2 class="section__title">Our Consultants</h2>
            <p class="section__subtitle">Meet our team of experienced medical professionals dedicated to your well-being.</p>
        </div>
        <div class="card-grid card-grid--4">
            <?php foreach (array_slice($consultants, 0, 4) as $index => $doc): ?>
                <div class="doctor-card reveal reveal--delay-<?php echo ($index % 4) + 1; ?>">
                    <?php if (!empty($doc['photo'])): ?>
                        <img class="doctor-card__photo" src="<?php echo htmlspecialchars($doc['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($doc['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <?php else: ?>
                        <div class="doctor-card__photo" style="background:#e2e8f0;display:flex;align-items:center;justify-content:center;font-size:2rem;color:#94a3b8;">👤</div>
                    <?php endif; ?>
                    <h3 class="doctor-card__name">
                        <a href="<?php echo htmlspecialchars($siteUrl . 'consultants/' . ($doc['slug'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars($doc['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </h3>
                    <?php if (!empty($doc['specialty_name'])): ?>
                        <span class="doctor-card__specialty"><?php echo htmlspecialchars($doc['specialty_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($doc['qualifications'])): ?>
                        <p class="doctor-card__qualifications"><?php echo htmlspecialchars($doc['qualifications'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                    <a href="<?php echo htmlspecialchars($siteUrl . 'consultants/' . ($doc['slug'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn--primary btn--sm">View Profile</a>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4 reveal">
            <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>consultants" class="btn btn--outline-dark">View All Doctors</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ========== Latest Promotions ========== -->
<?php if (!empty($promotions)): ?>
<section class="section section--alt">
    <div class="container">
        <div class="section__header reveal">
            <h2 class="section__title">Latest Promotions</h2>
            <p class="section__subtitle">Take advantage of our latest health packages and promotional offers.</p>
        </div>
        <div class="card-grid card-grid--3">
            <?php foreach (array_slice($promotions, 0, 3) as $index => $promo): ?>
                <div class="promo-card reveal reveal--delay-<?php echo ($index % 3) + 1; ?>">
                    <div class="promo-card__image-wrap">
                        <?php if (!empty($promo['featured_image'])): ?>
                            <img class="promo-card__image" src="<?php echo htmlspecialchars($promo['featured_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($promo['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <?php else: ?>
                            <div class="promo-card__image" style="background:linear-gradient(135deg,#0e7490,#6366f1);display:flex;align-items:center;justify-content:center;color:#fff;font-size:2.5rem;">🎉</div>
                        <?php endif; ?>
                        <?php
                        $isExpired = !empty($promo['end_date']) && strtotime($promo['end_date']) < time();
                        ?>
                        <span class="promo-card__badge <?php echo $isExpired ? 'promo-card__badge--expired' : 'promo-card__badge--active'; ?>">
                            <?php echo $isExpired ? 'Expired' : 'Active'; ?>
                        </span>
                    </div>
                    <div class="promo-card__body">
                        <h3 class="promo-card__title">
                            <a href="<?php echo htmlspecialchars($siteUrl . 'promotions/' . ($promo['slug'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($promo['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </h3>
                        <?php if (!empty($promo['start_date']) || !empty($promo['end_date'])): ?>
                            <div class="promo-card__dates">
                                📅
                                <?php echo htmlspecialchars(date('d M Y', strtotime($promo['start_date'] ?? 'now')), ENT_QUOTES, 'UTF-8'); ?>
                                —
                                <?php echo htmlspecialchars(date('d M Y', strtotime($promo['end_date'] ?? 'now')), ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($promo['description'])): ?>
                            <p class="promo-card__excerpt"><?php echo htmlspecialchars(strip_tags(mb_substr($promo['description'], 0, 120)), ENT_QUOTES, 'UTF-8'); ?>…</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4 reveal">
            <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>promotions" class="btn btn--outline-dark">View All Promotions</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ========== Recent Blog Posts ========== -->
<?php if (!empty($posts)): ?>
<section class="section">
    <div class="container">
        <div class="section__header reveal">
            <h2 class="section__title">Health &amp; Wellness Blog</h2>
            <p class="section__subtitle">Stay informed with the latest health tips, news, and insights from our medical experts.</p>
        </div>
        <div class="card-grid card-grid--3">
            <?php foreach (array_slice($posts, 0, 3) as $index => $post): ?>
                <div class="card reveal reveal--delay-<?php echo ($index % 3) + 1; ?>">
                    <?php if (!empty($post['featured_image'])): ?>
                        <img class="card__image" src="<?php echo htmlspecialchars($post['featured_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($post['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <?php else: ?>
                        <div class="card__image" style="background:linear-gradient(135deg,#e2e8f0,#f1f5f9);display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:2rem;">📰</div>
                    <?php endif; ?>
                    <div class="card__body">
                        <h3 class="card__title">
                            <a href="<?php echo htmlspecialchars($siteUrl . 'blog/' . ($post['slug'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($post['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </h3>
                        <?php if (!empty($post['excerpt'])): ?>
                            <p class="card__excerpt"><?php echo htmlspecialchars($post['excerpt'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                        <div class="card__meta">
                            <span class="card__meta-item">📅 <?php echo htmlspecialchars(date('d M Y', strtotime($post['created_at'] ?? 'now')), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4 reveal">
            <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>blog" class="btn btn--outline-dark">Read More Articles</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
