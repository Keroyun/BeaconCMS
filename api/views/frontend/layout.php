<?php
/**
 * BeaconCMS — Frontend Layout
 *
 * Variables expected:
 *   $pageTitle  — string, page title
 *   $content    — string, buffered page content
 *   $seoData    — array (optional), passed to SEO::renderHead()
 *   $bodyClass  — string (optional), extra class on <body>
 */

$siteName = defined('SITE_NAME') ? SITE_NAME : 'Beacon Hospital';
$siteUrl  = defined('SITE_URL')  ? SITE_URL  : '/';
$pageTitle = isset($pageTitle) ? $pageTitle : $siteName;
$bodyClass = isset($bodyClass) ? ' ' . $bodyClass : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?> | <?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- SEO Tags -->
    <?php if (isset($seoData) && class_exists('SEO')): ?>
        <?php echo SEO::renderHead($seoData); ?>
    <?php endif; ?>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars((class_exists('View') ? View::asset('css/frontend.css') : $siteUrl . 'assets/css/frontend.css'), ENT_QUOTES, 'UTF-8'); ?>">

    <!-- Header Snippets (HFCM) -->
    <?php 
    if (class_exists('Snippet')) {
        $snippetModel = new Snippet();
        $headerSnippets = $snippetModel->getActiveSnippets('header');
        foreach ($headerSnippets as $s) {
            echo "\n<!-- Snippet: " . htmlspecialchars($s['title']) . " -->\n";
            echo $s['code_content'] . "\n";
        }
    }
    ?>
</head>
<body class="frontend<?php echo $bodyClass; ?>">

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobile-overlay"></div>

    <!-- ========== Navbar ========== -->
    <nav class="navbar" id="main-navbar">
        <div class="navbar__inner">
            <a href="<?php echo url('/'); ?>" class="navbar__logo">
                <span class="navbar__logo-icon">B</span>
                <span class="navbar__logo-text"><?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></span>
            </a>

            <div class="navbar__nav" id="nav-menu">
                <?php
                $currentLang = class_exists('Language') ? Language::current() : 'en';
                $settingModel = class_exists('Setting') ? new Setting() : null;
                $customMenu = $settingModel ? $settingModel->get('navbar_menu_' . $currentLang) : '';

                if (!empty($customMenu)) {
                    // Output user's custom HTML menu links directly (for the active language)
                    echo $customMenu;
                } else {
                    // Fallback to default navigation menu
                    ?>
                    <a href="<?php echo url('/'); ?>" class="navbar__link<?php echo (isset($activeNav) && $activeNav === 'home') ? ' navbar__link--active' : ''; ?>">Home</a>
                    <a href="<?php echo url('/doctors'); ?>" class="navbar__link<?php echo (isset($activeNav) && $activeNav === 'consultants') ? ' navbar__link--active' : ''; ?>">Doctors</a>
                    <a href="<?php echo url('/specialties'); ?>" class="navbar__link<?php echo (isset($activeNav) && $activeNav === 'specialties') ? ' navbar__link--active' : ''; ?>">Specialties</a>
                    <a href="<?php echo url('/promotions'); ?>" class="navbar__link<?php echo (isset($activeNav) && $activeNav === 'promotions') ? ' navbar__link--active' : ''; ?>">Promotions</a>
                    <a href="<?php echo url('/blog'); ?>" class="navbar__link<?php echo (isset($activeNav) && $activeNav === 'blog') ? ' navbar__link--active' : ''; ?>">Blog</a>
                    <a href="<?php echo url('/contact'); ?>" class="navbar__link<?php echo (isset($activeNav) && $activeNav === 'contact') ? ' navbar__link--active' : ''; ?>">Contact</a>
                    <?php
                }
                ?>
            </div>
            
            <div class="navbar__actions" style="display:flex;align-items:center;gap:15px;">
                <?php 
                    if (class_exists('Language')) {
                        echo Language::renderSwitcher($_SERVER['REQUEST_URI'] ?? '', 'dropdown');
                    }
                ?>
                <button class="hamburger" id="hamburger-btn" aria-label="Toggle navigation">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </nav>

    <!-- ========== Main Content ========== -->
    <main>
        <?php echo $content; ?>
    </main>

    <!-- ========== Footer ========== -->
    <footer class="footer">
        <div class="container">
            <div class="footer__grid">
                <!-- About Column -->
                <div class="footer__col">
                    <h4 class="footer__heading"><?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></h4>
                    <p class="footer__text">Your trusted healthcare partner, providing world-class medical services with compassion and excellence. We are committed to advancing health and delivering exceptional patient care.</p>
                    <div class="footer__social">
                        <a href="#" class="footer__social-link" aria-label="Facebook" title="Facebook">f</a>
                        <a href="#" class="footer__social-link" aria-label="Twitter" title="Twitter">𝕏</a>
                        <a href="#" class="footer__social-link" aria-label="Instagram" title="Instagram">ig</a>
                        <a href="#" class="footer__social-link" aria-label="LinkedIn" title="LinkedIn">in</a>
                    </div>
                </div>

                <!-- Quick Links Column -->
                <div class="footer__col">
                    <h4 class="footer__heading">Quick Links</h4>
                    <div class="footer__links">
                        <a href="<?php echo url('/doctors'); ?>" class="footer__link">Our Doctors</a>
                        <a href="<?php echo url('/specialties'); ?>" class="footer__link">Specialties</a>
                        <a href="<?php echo url('/promotions'); ?>" class="footer__link">Promotions</a>
                        <a href="<?php echo url('/blog'); ?>" class="footer__link">Health Blog</a>
                        <a href="<?php echo url('/contact'); ?>" class="footer__link">Contact Us</a>
                    </div>
                </div>

                <!-- Contact Column -->
                <div class="footer__col">
                    <h4 class="footer__heading">Contact Info</h4>
                    <div class="footer__contact-item">
                        <span class="footer__contact-icon">📍</span>
                        <span>1 Jalan 215, Section 51, 46050 Petaling Jaya, Selangor, Malaysia</span>
                    </div>
                    <div class="footer__contact-item">
                        <span class="footer__contact-icon">📞</span>
                        <span>+603-7787 2999</span>
                    </div>
                    <div class="footer__contact-item">
                        <span class="footer__contact-icon">✉️</span>
                        <span>info@beaconhospital.com.my</span>
                    </div>
                </div>
            </div>

            <div class="footer__bottom">
                &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?>. All rights reserved. Powered by BeaconCMS.
            </div>
        </div>
    </footer>

    <!-- Frontend Scripts -->
    <script src="<?php echo htmlspecialchars((class_exists('View') ? View::asset('js/frontend.js') : $siteUrl . 'assets/js/frontend.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>

    <!-- Footer Snippets (HFCM) -->
    <?php 
    if (class_exists('Snippet')) {
        $footerSnippets = $snippetModel->getActiveSnippets('footer');
        foreach ($footerSnippets as $s) {
            echo "\n<!-- Snippet: " . htmlspecialchars($s['title']) . " -->\n";
            echo $s['code_content'] . "\n";
        }
    }
    ?>
</body>
</html>
