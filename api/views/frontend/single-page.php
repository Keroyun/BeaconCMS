<?php
/**
 * BeaconCMS — Single Page
 *
 * Variables:
 *   $page    — assoc array with page data
 *   $seoData — optional
 */

$isFrontendHome = $isFrontendHome ?? false;
$page      = $page ?? [];
$seoData   = $seoData ?? [];
$pageTitle = $page['title'] ?? 'Page';
$bodyClass = 'page-single-page';

$siteUrl = defined('SITE_URL') ? SITE_URL : '/';

ob_start();
?>

<?php if (!$isFrontendHome): ?>
<!-- Page Banner -->
<section class="page-banner">
    <div class="container">
        <h1 class="page-banner__title"><?php echo htmlspecialchars($page['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h1>
    </div>
</section>
<?php endif; ?>

<section class="page-single">
    <div class="container container--narrow">

        <?php if (!$isFrontendHome): ?>
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <ol class="breadcrumb__list">
                <li class="breadcrumb__item">
                    <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>" class="breadcrumb__link">Home</a>
                </li>
                <li class="breadcrumb__item">
                    <span class="breadcrumb__current"><?php echo htmlspecialchars($page['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                </li>
            </ol>
        </nav>

        <h1 class="page-single__title"><?php echo htmlspecialchars($page['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h1>
        <?php endif; ?>

        <div class="page-single__content">
            <?php echo $page['content'] ?? ''; /* HTML content, sanitized on save */ ?>
        </div>

    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
