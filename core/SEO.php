<?php
/**
 * SEO — Search Engine Optimisation Engine
 *
 * Generates <head> HTML for meta tags, Open Graph, Twitter Cards,
 * and JSON-LD structured data (Schema.org).
 *
 * All output methods return HTML strings — the caller echoes them
 * inside the <head> section of the layout.
 */
class SEO
{
    // ── Combined Renderer ───────────────────────────────────────────────────

    /**
     * Render the complete set of SEO tags for a page.
     *
     * $data keys:
     *   title, description, url, image, type (article|webpage|physician|hospital),
     *   robots, canonical, published_time, modified_time, author,
     *   consultant (array for physician schema), siteName
     *
     * @param array<string,mixed> $data
     */
    public static function renderHead(array $data = []): string
    {
        $html  = self::getMetaTags($data);
        $html .= self::getOpenGraph($data);
        $html .= self::getTwitterCard($data);

        // Determine which Schema type to emit
        $type = $data['type'] ?? 'webpage';
        $html .= self::getSchemaMarkup($type, $data);

        return $html;
    }

    // ── Meta Tags ───────────────────────────────────────────────────────────

    /**
     * Standard HTML meta tags: <title>, description, canonical, robots.
     */
    public static function getMetaTags(array $data): string
    {
        $siteName   = self::siteName();
        $pageTitle  = $data['title'] ?? '';
        $fullTitle  = $pageTitle !== '' ? "{$pageTitle} | {$siteName}" : $siteName;
        $desc       = $data['description'] ?? self::siteDefault('meta_description', '');
        $canonical  = $data['canonical'] ?? $data['url'] ?? '';
        $robots     = $data['robots'] ?? 'index, follow';

        $html  = "    <title>" . self::esc($fullTitle) . "</title>\n";

        if ($desc !== '') {
            $html .= '    <meta name="description" content="' . self::esc($desc) . "\">\n";
        }

        $html .= '    <meta name="robots" content="' . self::esc($robots) . "\">\n";

        if ($canonical !== '') {
            $html .= '    <link rel="canonical" href="' . self::esc($canonical) . "\">\n";
        }

        return $html;
    }

    // ── Open Graph ──────────────────────────────────────────────────────────

    /**
     * Open Graph protocol tags for Facebook / LinkedIn / etc.
     */
    public static function getOpenGraph(array $data): string
    {
        $siteName = self::siteName();
        $title    = $data['title'] ?? $siteName;
        $desc     = $data['description'] ?? self::siteDefault('meta_description', '');
        $url      = $data['url'] ?? '';
        $image    = $data['image'] ?? self::siteDefault('og_image', '');
        $type     = match ($data['type'] ?? 'webpage') {
            'article'   => 'article',
            'physician' => 'profile',
            default     => 'website',
        };

        $html  = '    <meta property="og:type" content="' . $type . "\">\n";
        $html .= '    <meta property="og:title" content="' . self::esc($title) . "\">\n";
        $html .= '    <meta property="og:site_name" content="' . self::esc($siteName) . "\">\n";

        if ($desc !== '') {
            $html .= '    <meta property="og:description" content="' . self::esc($desc) . "\">\n";
        }
        if ($url !== '') {
            $html .= '    <meta property="og:url" content="' . self::esc($url) . "\">\n";
        }
        if ($image !== '') {
            $html .= '    <meta property="og:image" content="' . self::esc($image) . "\">\n";
        }

        // Article-specific
        if ($type === 'article') {
            if (!empty($data['published_time'])) {
                $html .= '    <meta property="article:published_time" content="' . self::esc($data['published_time']) . "\">\n";
            }
            if (!empty($data['modified_time'])) {
                $html .= '    <meta property="article:modified_time" content="' . self::esc($data['modified_time']) . "\">\n";
            }
            if (!empty($data['author'])) {
                $html .= '    <meta property="article:author" content="' . self::esc($data['author']) . "\">\n";
            }
        }

        return $html;
    }

    // ── Twitter Card ────────────────────────────────────────────────────────

    /**
     * Twitter Card meta tags.
     */
    public static function getTwitterCard(array $data): string
    {
        $title = $data['title'] ?? self::siteName();
        $desc  = $data['description'] ?? self::siteDefault('meta_description', '');
        $image = $data['image'] ?? self::siteDefault('og_image', '');
        $card  = !empty($image) ? 'summary_large_image' : 'summary';

        $html  = '    <meta name="twitter:card" content="' . $card . "\">\n";
        $html .= '    <meta name="twitter:title" content="' . self::esc($title) . "\">\n";

        if ($desc !== '') {
            $html .= '    <meta name="twitter:description" content="' . self::esc($desc) . "\">\n";
        }
        if ($image !== '') {
            $html .= '    <meta name="twitter:image" content="' . self::esc($image) . "\">\n";
        }

        $twitterHandle = self::siteDefault('twitter_handle', '');
        if ($twitterHandle !== '') {
            $html .= '    <meta name="twitter:site" content="' . self::esc($twitterHandle) . "\">\n";
        }

        return $html;
    }

    // ── JSON-LD Schema Markup ───────────────────────────────────────────────

    /**
     * Return a <script type="application/ld+json"> block for the given type.
     *
     * @param string $type  One of: article, webpage, physician, hospital
     * @param array  $data  Page / entity data
     */
    public static function getSchemaMarkup(string $type, array $data): string
    {
        $schema = match ($type) {
            'article'   => self::articleSchema($data),
            'physician' => self::getDoctorSchema($data['consultant'] ?? $data),
            'hospital'  => self::hospitalSchema($data),
            default     => self::webpageSchema($data),
        };

        if (empty($schema)) {
            return '';
        }

        $json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return "    <script type=\"application/ld+json\">\n{$json}\n    </script>\n";
    }

    // ── Specific Schema Builders ────────────────────────────────────────────

    /**
     * WebPage schema (generic).
     */
    private static function webpageSchema(array $data): array
    {
        return [
            '@context'    => 'https://schema.org',
            '@type'       => 'WebPage',
            'name'        => $data['title'] ?? self::siteName(),
            'description' => $data['description'] ?? '',
            'url'         => $data['url'] ?? '',
            'publisher'   => self::organisationSnippet(),
        ];
    }

    /**
     * Article schema (blog posts).
     */
    private static function articleSchema(array $data): array
    {
        $schema = [
            '@context'      => 'https://schema.org',
            '@type'         => 'Article',
            'headline'      => $data['title'] ?? '',
            'description'   => $data['description'] ?? '',
            'url'           => $data['url'] ?? '',
            'publisher'     => self::organisationSnippet(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id'   => $data['url'] ?? '',
            ],
        ];

        if (!empty($data['image'])) {
            $schema['image'] = $data['image'];
        }
        if (!empty($data['published_time'])) {
            $schema['datePublished'] = $data['published_time'];
        }
        if (!empty($data['modified_time'])) {
            $schema['dateModified'] = $data['modified_time'];
        }
        if (!empty($data['author'])) {
            $schema['author'] = [
                '@type' => 'Person',
                'name'  => $data['author'],
            ];
        }

        return $schema;
    }

    /**
     * Physician schema for consultant profiles.
     *
     * @param array $consultant  Row from the consultants table
     */
    public static function getDoctorSchema(array $consultant): array
    {
        $schema = [
            '@context'       => 'https://schema.org',
            '@type'          => 'Physician',
            'name'           => $consultant['name'] ?? '',
            'description'    => $consultant['bio'] ?? $consultant['description'] ?? '',
            'url'            => $consultant['url'] ?? '',
            'worksFor'       => self::organisationSnippet(),
        ];

        if (!empty($consultant['photo'])) {
            $schema['image'] = $consultant['photo'];
        }

        if (!empty($consultant['qualification'])) {
            $schema['qualifications'] = $consultant['qualification'];
        }

        if (!empty($consultant['specialty_name'])) {
            $schema['medicalSpecialty'] = $consultant['specialty_name'];
        }

        if (!empty($consultant['email'])) {
            $schema['email'] = $consultant['email'];
        }

        if (!empty($consultant['phone'])) {
            $schema['telephone'] = $consultant['phone'];
        }

        return $schema;
    }

    /**
     * Hospital / medical organisation schema (home page).
     */
    private static function hospitalSchema(array $data): array
    {
        $siteName = self::siteName();

        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Hospital',
            'name'        => $siteName,
            'description' => $data['description'] ?? self::siteDefault('meta_description', ''),
            'url'         => $data['url'] ?? (defined('SITE_URL') ? SITE_URL : ''),
        ];

        $logo = self::siteDefault('logo', '');
        if ($logo !== '') {
            $schema['logo'] = $logo;
        }

        $address = self::siteDefault('address', '');
        if ($address !== '') {
            $schema['address'] = [
                '@type'          => 'PostalAddress',
                'streetAddress'  => $address,
            ];
        }

        $phone = self::siteDefault('phone', '');
        if ($phone !== '') {
            $schema['telephone'] = $phone;
        }

        return $schema;
    }

    // ── Internal Helpers ────────────────────────────────────────────────────

    /**
     * Build a reusable Organization snippet for publisher / worksFor.
     */
    private static function organisationSnippet(): array
    {
        $org = [
            '@type' => 'Organization',
            'name'  => self::siteName(),
        ];

        $logo = self::siteDefault('logo', '');
        if ($logo !== '') {
            $org['logo'] = $logo;
        }

        $siteUrl = defined('SITE_URL') ? SITE_URL : '';
        if ($siteUrl !== '') {
            $org['url'] = $siteUrl;
        }

        return $org;
    }

    /**
     * Return the configured site name, falling back to the SITE_NAME constant.
     */
    private static function siteName(): string
    {
        return defined('SITE_NAME') ? SITE_NAME : 'Beacon Hospital';
    }

    /**
     * Attempt to read a site-wide setting from the database.
     * Falls back to $default if the settings table is unavailable.
     */
    private static function siteDefault(string $key, string $default): string
    {
        static $cache = [];

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        try {
            $db  = Database::getInstance();
            $row = $db->selectOne('settings', '`key` = ?', [$key]);
            $cache[$key] = $row['value'] ?? $default;
        } catch (\Throwable) {
            $cache[$key] = $default;
        }

        return $cache[$key];
    }

    /** HTML-attribute-safe escaping. */
    private static function esc(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}
