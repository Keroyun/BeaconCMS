<?php
/**
 * BeaconCMS Dynamic Sitemap Generator
 * Generates XML sitemap for search engines
 */

// Load config
if (!file_exists(__DIR__ . '/config.php')) {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/config.php';

header('Content-Type: application/xml; charset=utf-8');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit;
}

$siteUrl = rtrim(SITE_URL, '/');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Homepage -->
    <url>
        <loc><?php echo htmlspecialchars($siteUrl); ?>/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Doctors Listing -->
    <url>
        <loc><?php echo htmlspecialchars($siteUrl); ?>/doctors</loc>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>

    <!-- Specialties Listing -->
    <url>
        <loc><?php echo htmlspecialchars($siteUrl); ?>/specialties</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Promotions Listing -->
    <url>
        <loc><?php echo htmlspecialchars($siteUrl); ?>/promotions</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Blog Listing -->
    <url>
        <loc><?php echo htmlspecialchars($siteUrl); ?>/blog</loc>
        <changefreq>daily</changefreq>
        <priority>0.7</priority>
    </url>

    <!-- Published Posts -->
<?php
$stmt = $pdo->query("SELECT slug, updated_at FROM posts WHERE status = 'published' ORDER BY updated_at DESC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
?>
    <url>
        <loc><?php echo htmlspecialchars($siteUrl . '/blog/' . $row['slug']); ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($row['updated_at'])); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
<?php endwhile; ?>

    <!-- Published Pages -->
<?php
$stmt = $pdo->query("SELECT slug, updated_at FROM pages WHERE status = 'published' ORDER BY sort_order ASC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
?>
    <url>
        <loc><?php echo htmlspecialchars($siteUrl . '/page/' . $row['slug']); ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($row['updated_at'])); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
<?php endwhile; ?>

    <!-- Published Consultants -->
<?php
$stmt = $pdo->query("SELECT slug, updated_at FROM consultants WHERE status = 'published' ORDER BY sort_order ASC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
?>
    <url>
        <loc><?php echo htmlspecialchars($siteUrl . '/doctors/' . $row['slug']); ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($row['updated_at'])); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
<?php endwhile; ?>

    <!-- Published Promotions -->
<?php
$stmt = $pdo->query("SELECT slug, updated_at FROM promotions WHERE status = 'published' ORDER BY created_at DESC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
?>
    <url>
        <loc><?php echo htmlspecialchars($siteUrl . '/promotions/' . $row['slug']); ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($row['updated_at'])); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
<?php endwhile; ?>

    <!-- Published Specialties -->
<?php
$stmt = $pdo->query("SELECT slug, updated_at FROM specialties WHERE status = 'published' ORDER BY sort_order ASC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
?>
    <url>
        <loc><?php echo htmlspecialchars($siteUrl . '/specialties/' . $row['slug']); ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($row['updated_at'])); ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
<?php endwhile; ?>

</urlset>
