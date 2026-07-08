<?php
/**
 * BeaconCMS — Single Blog Post
 *
 * Variables:
 *   $post    — assoc array with post data
 *   $seoData — optional
 */

$post      = $post ?? [];
$seoData   = $seoData ?? [];
$pageTitle = $post['title'] ?? 'Blog Post';
$activeNav = 'blog';
$bodyClass = 'page-post-single';

$siteUrl = defined('SITE_URL') ? SITE_URL : '/';

ob_start();
?>

<!-- Page Banner -->
<section class="page-banner">
    <div class="container">
        <h1 class="page-banner__title"><?php echo htmlspecialchars($post['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h1>
    </div>
</section>

<section class="post-single">
    <div class="container container--narrow">

        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <ol class="breadcrumb__list">
                <li class="breadcrumb__item">
                    <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>" class="breadcrumb__link">Home</a>
                </li>
                <li class="breadcrumb__item">
                    <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>blog" class="breadcrumb__link">Blog</a>
                </li>
                <li class="breadcrumb__item">
                    <span class="breadcrumb__current"><?php echo htmlspecialchars($post['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                </li>
            </ol>
        </nav>

        <!-- Featured Image -->
        <?php if (!empty($post['featured_image'])): ?>
            <img class="post-single__image" src="<?php echo htmlspecialchars($post['featured_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($post['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <?php endif; ?>

        <!-- Title -->
        <h1 class="post-single__title"><?php echo htmlspecialchars($post['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h1>

        <!-- Meta -->
        <div class="post-single__meta">
            <?php if (!empty($post['author_name'])): ?>
                <span class="card__meta-item">👤 <?php echo htmlspecialchars($post['author_name'], ENT_QUOTES, 'UTF-8'); ?></span>
            <?php endif; ?>
            <?php if (!empty($post['created_at'])): ?>
                <span class="card__meta-item">📅 <?php echo htmlspecialchars(date('d M Y', strtotime($post['created_at'])), ENT_QUOTES, 'UTF-8'); ?></span>
            <?php endif; ?>
        </div>

        <!-- Content -->
        <div class="post-single__content">
            <?php echo $post['content'] ?? ''; /* HTML content, sanitized on save */ ?>
        </div>

        <!-- Tags / Categories -->
        <?php if (!empty($post['seo_keywords'])): ?>
            <div class="post-single__tags">
                <?php
                $tags = array_map('trim', explode(',', $post['seo_keywords']));
                foreach ($tags as $tag):
                    if (empty($tag)) continue;
                ?>
                    <span class="post-tag"><?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
