<?php
$pageTitle = '404 - Page Not Found';
$seoData = ['title' => 'Page Not Found', 'description' => 'The page you are looking for could not be found.'];
ob_start();
?>

<section class="error-page">
    <div class="container">
        <div class="error-content">
            <div class="error-code">404</div>
            <h1>Page Not Found</h1>
            <p>Sorry, the page you're looking for doesn't exist or has been moved.</p>
            <div class="error-actions">
                <a href="<?php echo url('/'); ?>" class="btn btn-primary">
                    <i class="fa-solid fa-home"></i> Go Home
                </a>
                <a href="<?php echo url('/doctors'); ?>" class="btn btn-outline">
                    <i class="fa-solid fa-user-doctor"></i> Find a Doctor
                </a>
                <a href="<?php echo url('/promotions'); ?>" class="btn btn-outline">
                    <i class="fa-solid fa-tag"></i> View Promotions
                </a>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
