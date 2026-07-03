<?php
/**
 * BeaconCMS — Consultant Single (Doctor Profile)
 *
 * Variables:
 *   $consultant        — assoc array with doctor data
 *   $relatedConsultants — array of consultants from same specialty
 *   $seoData           — optional SEO data
 */

$consultant        = $consultant ?? [];
$relatedConsultants = $relatedConsultants ?? [];
$seoData           = $seoData ?? [];

$pageTitle = ($consultant['name'] ?? 'Doctor Profile');
$activeNav = 'consultants';
$bodyClass = 'page-consultant-single';

$siteUrl = defined('SITE_URL') ? SITE_URL : '/';

ob_start();
?>

<!-- Schema.org Markup -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Physician",
    "name": <?php echo json_encode($consultant['name'] ?? '', JSON_HEX_TAG | JSON_HEX_AMP); ?>,
    "image": <?php echo json_encode($consultant['photo'] ?? '', JSON_HEX_TAG | JSON_HEX_AMP); ?>,
    "medicalSpecialty": <?php echo json_encode($consultant['specialty_name'] ?? '', JSON_HEX_TAG | JSON_HEX_AMP); ?>,
    "description": <?php echo json_encode($consultant['qualifications'] ?? '', JSON_HEX_TAG | JSON_HEX_AMP); ?>,
    "telephone": <?php echo json_encode($consultant['contact_number'] ?? '', JSON_HEX_TAG | JSON_HEX_AMP); ?>,
    "email": <?php echo json_encode($consultant['email'] ?? '', JSON_HEX_TAG | JSON_HEX_AMP); ?>,
    "memberOf": {
        "@type": "Hospital",
        "name": <?php echo json_encode(defined('SITE_NAME') ? SITE_NAME : 'Beacon Hospital', JSON_HEX_TAG | JSON_HEX_AMP); ?>
    }
}
</script>

<!-- ========== Profile Hero ========== -->
<section class="profile-hero">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb" style="margin-bottom: 32px;">
            <ol class="breadcrumb__list">
                <li class="breadcrumb__item">
                    <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>" class="breadcrumb__link" style="color:rgba(255,255,255,0.7);">Home</a>
                </li>
                <li class="breadcrumb__item">
                    <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>consultants" class="breadcrumb__link" style="color:rgba(255,255,255,0.7);">Doctors</a>
                </li>
                <li class="breadcrumb__item">
                    <span class="breadcrumb__current" style="color:#fff;"><?php echo htmlspecialchars($consultant['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                </li>
            </ol>
        </nav>

        <div class="profile-hero__inner">
            <?php if (!empty($consultant['photo'])): ?>
                <img class="profile-hero__photo" src="<?php echo htmlspecialchars($consultant['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($consultant['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <?php else: ?>
                <div class="profile-hero__photo" style="background:#1e293b;display:flex;align-items:center;justify-content:center;font-size:4rem;color:#94a3b8;">👤</div>
            <?php endif; ?>

            <div class="profile-hero__info">
                <h1 class="profile-hero__name"><?php echo htmlspecialchars($consultant['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h1>
                <?php if (!empty($consultant['specialty_name'])): ?>
                    <span class="profile-hero__specialty"><?php echo htmlspecialchars($consultant['specialty_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
                <?php if (!empty($consultant['qualifications'])): ?>
                    <p class="profile-hero__qualifications"><?php echo htmlspecialchars($consultant['qualifications'], ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ========== Profile Content ========== -->
<section class="profile-content">
    <div class="container container--narrow">

        <!-- Tabs Navigation -->
        <div class="profile-tabs" role="tablist">
            <button class="profile-tab profile-tab--active" data-tab="panel-about" role="tab" aria-selected="true" id="tab-about">About</button>
            <button class="profile-tab" data-tab="panel-qualifications" role="tab" aria-selected="false" id="tab-qualifications">Qualifications &amp; Experience</button>
            <button class="profile-tab" data-tab="panel-hours" role="tab" aria-selected="false" id="tab-hours">Clinic Hours</button>
            <button class="profile-tab" data-tab="panel-contact" role="tab" aria-selected="false" id="tab-contact">Contact &amp; Booking</button>
        </div>

        <!-- Panel: About -->
        <div class="profile-panel profile-panel--active" id="panel-about" role="tabpanel" aria-labelledby="tab-about">
            <h3>About</h3>
            <?php if (!empty($consultant['bio'])): ?>
                <div class="post-single__content">
                    <?php echo $consultant['bio']; /* Bio is stored as HTML, already sanitized on save */ ?>
                </div>
            <?php else: ?>
                <p>No biography available at this time.</p>
            <?php endif; ?>
        </div>

        <!-- Panel: Qualifications & Experience -->
        <div class="profile-panel" id="panel-qualifications" role="tabpanel" aria-labelledby="tab-qualifications">
            <h3>Qualifications</h3>
            <?php if (!empty($consultant['qualifications'])): ?>
                <p><?php echo nl2br(htmlspecialchars($consultant['qualifications'], ENT_QUOTES, 'UTF-8')); ?></p>
            <?php else: ?>
                <p>No qualifications listed.</p>
            <?php endif; ?>

            <h3 style="margin-top:32px;">Experience</h3>
            <?php if (!empty($consultant['experience'])): ?>
                <div class="post-single__content">
                    <?php echo $consultant['experience']; /* Stored as HTML */ ?>
                </div>
            <?php else: ?>
                <p>No experience details available.</p>
            <?php endif; ?>
        </div>

        <!-- Panel: Clinic Hours -->
        <div class="profile-panel" id="panel-hours" role="tabpanel" aria-labelledby="tab-hours">
            <h3>Clinic Hours</h3>
            <?php if (!empty($consultant['clinic_hours'])): ?>
                <div class="post-single__content">
                    <?php echo $consultant['clinic_hours']; /* Stored as HTML or text */ ?>
                </div>
            <?php else: ?>
                <p>Please contact the hospital for clinic hours.</p>
            <?php endif; ?>
        </div>

        <!-- Panel: Contact & Booking -->
        <div class="profile-panel" id="panel-contact" role="tabpanel" aria-labelledby="tab-contact">
            <h3>Contact Information</h3>
            <div class="profile-contact-list">
                <?php if (!empty($consultant['contact_number'])): ?>
                    <div class="profile-contact-item">
                        <span>📞</span>
                        <span><strong>Phone:</strong> <a href="tel:<?php echo htmlspecialchars($consultant['contact_number'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($consultant['contact_number'], ENT_QUOTES, 'UTF-8'); ?></a></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($consultant['email'])): ?>
                    <div class="profile-contact-item">
                        <span>✉️</span>
                        <span><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($consultant['email'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($consultant['email'], ENT_QUOTES, 'UTF-8'); ?></a></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Booking CTA -->
            <div class="profile-booking">
                <h4 class="profile-booking__title">Book an Appointment</h4>
                <p class="profile-booking__text">Schedule a consultation with <?php echo htmlspecialchars($consultant['name'] ?? 'this doctor', ENT_QUOTES, 'UTF-8'); ?> today.</p>
                <?php if (!empty($consultant['booking_link'])): ?>
                    <a href="<?php echo htmlspecialchars($consultant['booking_link'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn--accent btn--lg" target="_blank" rel="noopener noreferrer">Book Now</a>
                <?php else: ?>
                    <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>contact" class="btn btn--accent btn--lg">Contact Us to Book</a>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<!-- ========== Related Doctors ========== -->
<?php if (!empty($relatedConsultants)): ?>
<section class="section section--alt">
    <div class="container">
        <div class="section__header reveal">
            <h2 class="section__title">Related Doctors</h2>
            <p class="section__subtitle">Other consultants in the same specialty.</p>
        </div>
        <div class="card-grid card-grid--4">
            <?php foreach (array_slice($relatedConsultants, 0, 4) as $index => $doc): ?>
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
                    <a href="<?php echo htmlspecialchars($siteUrl . 'consultants/' . ($doc['slug'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn--primary btn--sm">View Profile</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
