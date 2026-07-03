<?php
/**
 * BeaconCMS — Consultant Listing
 *
 * Variables:
 *   $consultants — array of consultant records
 *   $specialties — array of specialty records (for filter)
 */

$consultants = $consultants ?? [];
$specialties = $specialties ?? [];
$seoData     = $seoData ?? [];

$pageTitle = 'Our Consultants';
$activeNav = 'consultants';
$bodyClass = 'page-consultants';

$siteUrl = defined('SITE_URL') ? SITE_URL : '/';

ob_start();
?>

<!-- Page Banner -->
<section class="page-banner">
    <div class="container">
        <h1 class="page-banner__title">Our Consultants</h1>
        <p class="page-banner__subtitle">Expert medical professionals committed to your health and well-being.</p>
    </div>
</section>

<section class="section">
    <div class="container">

        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <ol class="breadcrumb__list">
                <li class="breadcrumb__item">
                    <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>" class="breadcrumb__link">Home</a>
                </li>
                <li class="breadcrumb__item">
                    <span class="breadcrumb__current">Doctors</span>
                </li>
            </ol>
        </nav>

        <!-- Filter Bar -->
        <?php if (!empty($specialties)): ?>
        <div class="filter-bar reveal">
            <label class="filter-bar__label" for="specialty-filter">Filter by Specialty:</label>
            <select class="filter-bar__select" id="specialty-filter">
                <option value="">All Specialties</option>
                <?php foreach ($specialties as $sp): ?>
                    <option value="<?php echo htmlspecialchars($sp['id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($sp['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <!-- Doctors Grid -->
        <?php if (!empty($consultants)): ?>
            <div class="card-grid card-grid--4">
                <?php foreach ($consultants as $index => $doc): ?>
                    <div class="doctor-card reveal reveal--delay-<?php echo ($index % 4) + 1; ?>" data-specialty-id="<?php echo htmlspecialchars($doc['specialty_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
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
        <?php else: ?>
            <div class="text-center" style="padding:60px 0;">
                <p class="text-muted" style="font-size:1.1rem;">No consultants found at this time.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
