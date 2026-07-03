<?php
declare(strict_types=1);

/**
 * Language — Multi-language support (Polylang Pro-style)
 * 
 * Handles language detection, switching, content translation linking,
 * and URL routing with language prefixes.
 * 
 * URL structure: /en/doctors/dr-ahmad, /ms/doktor/dr-ahmad, /zh/doctors/dr-ahmad
 * Default language doesn't require prefix: /doctors/dr-ahmad = English
 */
class Language
{
    /** @var array Cached list of active languages */
    private static array $languages = [];

    /** @var string Current active language code */
    private static string $currentLang = 'en';

    /** @var string Default language code */
    private static string $defaultLang = 'en';

    /** @var bool Whether the system has been initialized */
    private static bool $initialized = false;

    /**
     * Initialize the language system.
     * Call this early in the request lifecycle (from index.php or Router).
     */
    public static function init(): void
    {
        if (self::$initialized) return;
        self::$initialized = true;

        self::loadLanguages();
        self::detectLanguage();
    }

    /**
     * Load active languages from the database.
     */
    private static function loadLanguages(): void
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->query(
                "SELECT * FROM languages WHERE is_active = 1 ORDER BY sort_order ASC, name ASC"
            );
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                self::$languages[$row['code']] = $row;
                if ($row['is_default']) {
                    self::$defaultLang = $row['code'];
                    self::$currentLang = $row['code'];
                }
            }
        } catch (\PDOException $e) {
            // Table might not exist yet (during install)
            self::$languages = [
                'en' => [
                    'id' => 1, 'code' => 'en', 'name' => 'English',
                    'native_name' => 'English', 'flag' => '🇬🇧',
                    'is_default' => 1, 'is_active' => 1, 'direction' => 'ltr',
                    'sort_order' => 0
                ]
            ];
        }
    }

    /**
     * Detect the current language from the URL prefix.
     * e.g., /ms/doctors → language = 'ms'
     */
    private static function detectLanguage(): void
    {
        $url = trim($_GET['url'] ?? '', '/');
        $segments = explode('/', $url);
        $firstSegment = $segments[0] ?? '';

        // Check if first URL segment is a language code
        if ($firstSegment && isset(self::$languages[$firstSegment])) {
            self::$currentLang = $firstSegment;
        } else {
            // No language prefix — use default or session/cookie preference
            self::$currentLang = $_COOKIE['bcms_lang'] ?? $_SESSION['bcms_lang'] ?? self::$defaultLang;
        }

        // Store in session
        $_SESSION['bcms_lang'] = self::$currentLang;
    }

    /**
     * Get the current language code.
     */
    public static function current(): string
    {
        if (!self::$initialized) self::init();
        return self::$currentLang;
    }

    /**
     * Get the default language code.
     */
    public static function getDefault(): string
    {
        if (!self::$initialized) self::init();
        return self::$defaultLang;
    }

    /**
     * Set the current language.
     */
    public static function setCurrent(string $code): void
    {
        if (isset(self::$languages[$code])) {
            self::$currentLang = $code;
            $_SESSION['bcms_lang'] = $code;
            setcookie('bcms_lang', $code, time() + (365 * 24 * 3600), '/');
        }
    }

    /**
     * Check if current language is the default.
     */
    public static function isDefault(): bool
    {
        return self::$currentLang === self::$defaultLang;
    }

    /**
     * Get all active languages.
     * @return array<string, array>
     */
    public static function getAll(): array
    {
        if (!self::$initialized) self::init();
        return self::$languages;
    }

    /**
     * Get a specific language by code.
     */
    public static function get(string $code): ?array
    {
        if (!self::$initialized) self::init();
        return self::$languages[$code] ?? null;
    }

    /**
     * Get current language info.
     */
    public static function getCurrentInfo(): array
    {
        return self::get(self::$currentLang) ?? self::$languages[self::$defaultLang];
    }

    /**
     * Get language direction (ltr/rtl).
     */
    public static function getDirection(): string
    {
        $lang = self::getCurrentInfo();
        return $lang['direction'] ?? 'ltr';
    }

    /**
     * Strip the language prefix from a URL for routing.
     * e.g., "ms/doctors/dr-ahmad" → "doctors/dr-ahmad"
     */
    public static function stripPrefix(string $url): string
    {
        $url = trim($url, '/');
        $segments = explode('/', $url);

        if (!empty($segments[0]) && isset(self::$languages[$segments[0]])) {
            array_shift($segments);
            return implode('/', $segments);
        }

        return $url;
    }

    /**
     * Generate a URL with language prefix.
     * Default language omits the prefix.
     */
    public static function url(string $path, ?string $langCode = null): string
    {
        $langCode = $langCode ?? self::$currentLang;
        $base = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
        $path = '/' . ltrim($path, '/');

        if ($langCode !== self::$defaultLang) {
            return $base . '/' . $langCode . $path;
        }

        return $base . $path;
    }

    // ── Translation Linking ─────────────────────────────────────────────────

    /**
     * Get translations of a content item.
     * Returns array of [language_code => content_id] for all translations.
     */
    public static function getTranslations(string $contentType, int $contentId): array
    {
        try {
            $db = Database::getInstance();

            // Find the translation group
            $stmt = $db->query(
                "SELECT translation_group FROM translations WHERE content_type = ? AND content_id = ?",
                [$contentType, $contentId]
            );
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row) return [];

            // Get all items in the same translation group
            $stmt = $db->query(
                "SELECT language_code, content_id FROM translations WHERE content_type = ? AND translation_group = ?",
                [$contentType, $row['translation_group']]
            );

            $translations = [];
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $t) {
                $translations[$t['language_code']] = (int)$t['content_id'];
            }

            return $translations;
        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Link two content items as translations of each other.
     */
    public static function linkTranslation(string $contentType, int $sourceId, string $sourceLang, int $targetId, string $targetLang): void
    {
        $db = Database::getInstance();

        // Check if source already has a translation group
        $stmt = $db->query(
            "SELECT translation_group FROM translations WHERE content_type = ? AND content_id = ?",
            [$contentType, $sourceId]
        );
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row) {
            $group = $row['translation_group'];
        } else {
            // Create new group (UUID-like)
            $group = bin2hex(random_bytes(16));
            $db->insert('translations', [
                'content_type' => $contentType,
                'content_id' => $sourceId,
                'language_code' => $sourceLang,
                'translation_group' => $group,
            ]);
        }

        // Insert or update target translation
        $stmt = $db->query(
            "SELECT id FROM translations WHERE content_type = ? AND content_id = ? AND language_code = ?",
            [$contentType, $targetId, $targetLang]
        );
        $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($existing) {
            $db->update('translations', 
                ['translation_group' => $group], 
                'id = ?', [(int)$existing['id']]
            );
        } else {
            $db->insert('translations', [
                'content_type' => $contentType,
                'content_id' => $targetId,
                'language_code' => $targetLang,
                'translation_group' => $group,
            ]);
        }
    }

    /**
     * Set the language for a content item (without linking to translations).
     */
    public static function setContentLanguage(string $contentType, int $contentId, string $langCode): void
    {
        $db = Database::getInstance();
        
        $stmt = $db->query(
            "SELECT id FROM translations WHERE content_type = ? AND content_id = ?",
            [$contentType, $contentId]
        );
        $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($existing) {
            $db->update('translations',
                ['language_code' => $langCode],
                'id = ?', [(int)$existing['id']]
            );
        } else {
            $group = bin2hex(random_bytes(16));
            $db->insert('translations', [
                'content_type' => $contentType,
                'content_id' => $contentId,
                'language_code' => $langCode,
                'translation_group' => $group,
            ]);
        }
    }

    /**
     * Get the language of a specific content item.
     */
    public static function getContentLanguage(string $contentType, int $contentId): string
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->query(
                "SELECT language_code FROM translations WHERE content_type = ? AND content_id = ?",
                [$contentType, $contentId]
            );
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row['language_code'] ?? self::$defaultLang;
        } catch (\PDOException $e) {
            return self::$defaultLang;
        }
    }

    /**
     * Render the language switcher HTML for the frontend.
     */
    public static function renderSwitcher(string $currentUrl = '', string $style = 'dropdown'): string
    {
        $languages = self::getAll();
        if (count($languages) <= 1) return '';

        $current = self::getCurrentInfo();
        $html = '';

        if ($style === 'dropdown') {
            $html .= '<div class="lang-switcher">';
            $html .= '<button class="lang-current" aria-label="Switch language">';
            $html .= '<span class="lang-flag">' . htmlspecialchars($current['flag'] ?? '🌐') . '</span>';
            $html .= '<span class="lang-code">' . htmlspecialchars(strtoupper($current['code'])) . '</span>';
            $html .= '<i class="fa-solid fa-chevron-down"></i>';
            $html .= '</button>';
            $html .= '<div class="lang-dropdown">';

            foreach ($languages as $lang) {
                $isActive = $lang['code'] === self::$currentLang ? ' active' : '';
                $langUrl = self::url($currentUrl ?: '/', $lang['code']);
                $html .= '<a href="' . htmlspecialchars($langUrl) . '" class="lang-option' . $isActive . '">';
                $html .= '<span class="lang-flag">' . htmlspecialchars($lang['flag'] ?? '🌐') . '</span>';
                $html .= '<span class="lang-name">' . htmlspecialchars($lang['native_name'] ?? $lang['name']) . '</span>';
                $html .= '</a>';
            }

            $html .= '</div></div>';
        } elseif ($style === 'inline') {
            $html .= '<div class="lang-switcher-inline">';
            foreach ($languages as $lang) {
                $isActive = $lang['code'] === self::$currentLang ? ' active' : '';
                $langUrl = self::url($currentUrl ?: '/', $lang['code']);
                $html .= '<a href="' . htmlspecialchars($langUrl) . '" class="lang-inline-btn' . $isActive . '">';
                $html .= htmlspecialchars(strtoupper($lang['code']));
                $html .= '</a>';
            }
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Render the admin language selector for content forms.
     */
    public static function renderAdminSelector(string $contentType, ?int $contentId = null, string $selectedLang = ''): string
    {
        $languages = self::getAll();
        if (count($languages) <= 1) return '';

        $selectedLang = $selectedLang ?: self::$defaultLang;
        $translations = $contentId ? self::getTranslations($contentType, $contentId) : [];

        $html = '<div class="admin-lang-selector">';
        $html .= '<label><i class="fa-solid fa-globe"></i> Language</label>';
        $html .= '<div class="lang-tabs">';

        foreach ($languages as $lang) {
            $isActive = $lang['code'] === $selectedLang ? ' active' : '';
            $hasTranslation = isset($translations[$lang['code']]) ? ' has-translation' : '';
            $html .= '<button type="button" class="lang-tab' . $isActive . $hasTranslation . '" ';
            $html .= 'data-lang="' . htmlspecialchars($lang['code']) . '">';
            $html .= '<span class="lang-flag">' . htmlspecialchars($lang['flag'] ?? '🌐') . '</span> ';
            $html .= htmlspecialchars($lang['code']);
            if (isset($translations[$lang['code']])) {
                $html .= ' <i class="fa-solid fa-check-circle" style="color:#10b981;font-size:0.7rem"></i>';
            }
            $html .= '</button>';
        }

        $html .= '</div>';
        $html .= '<input type="hidden" name="language_code" value="' . htmlspecialchars($selectedLang) . '" id="languageCode">';

        // Show translation links if editing existing content
        if ($contentId && !empty($translations)) {
            $html .= '<div class="translation-links">';
            $html .= '<small>Translations: ';
            foreach ($translations as $langCode => $transId) {
                if ($transId != $contentId) {
                    $editUrl = "/{$contentType}s/edit/{$transId}";
                    $langInfo = self::get($langCode);
                    $html .= '<a href="' . htmlspecialchars(url('/admin' . $editUrl)) . '">';
                    $html .= htmlspecialchars($langInfo['flag'] ?? '🌐') . ' ' . htmlspecialchars(strtoupper($langCode));
                    $html .= '</a> ';
                }
            }
            $html .= '</small></div>';
        }

        $html .= '</div>';

        return $html;
    }
}
