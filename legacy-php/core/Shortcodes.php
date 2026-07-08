<?php
declare(strict_types=1);

/**
 * Shortcodes Parser Engine
 * Handles custom shortcodes like [other_consultants]
 */
class Shortcodes
{
    /**
     * Parse all known shortcodes in a block of content
     */
    public static function parse(string $content): string
    {
        // Parse Form shortcode (already exists in FormBuilder)
        if (class_exists('FormBuilder')) {
            $content = FormBuilder::parseShortcodes($content);
        }

        // Parse [other_consultants]
        $content = preg_replace_callback('/\[other_consultants([^\]]*)\]/i', [self::class, 'renderConsultants'], $content);

        // Parse [flipbook]
        $content = preg_replace_callback('/\[flipbook([^\]]*)\]/i', [self::class, 'renderFlipbook'], $content);

        return $content;
    }

    /**
     * Parse attributes from shortcode string (e.g. specialty="slug" limit="12")
     */
    private static function parseAttributes(string $attrString): array
    {
        $attributes = [];
        if (preg_match_all('/(\w+)\s*=\s*"([^"]*)"/i', $attrString, $matches)) {
            foreach ($matches[1] as $idx => $key) {
                $attributes[strtolower($key)] = $matches[2][$idx];
            }
        }
        return $attributes;
    }

    /**
     * Render the [other_consultants] shortcode
     */
    public static function renderConsultants(array $matches): string
    {
        $attrString = $matches[1] ?? '';
        $attrs = self::parseAttributes($attrString);

        $limit = isset($attrs['limit']) ? (int)$attrs['limit'] : -1; // -1 means all
        $specialties = isset($attrs['specialty']) ? array_filter(array_map('trim', explode(',', $attrs['specialty']))) : [];
        $includeIds = isset($attrs['include']) ? array_filter(array_map('intval', explode(',', $attrs['include']))) : [];

        $db = Database::getInstance();
        
        $sql = "SELECT c.*, s.slug as specialty_slug, s.name as specialty_name,
                (SELECT GROUP_CONCAT(cat.name) 
                 FROM category_items ci 
                 JOIN categories cat ON ci.category_id = cat.id 
                 WHERE ci.content_id = c.id AND ci.content_type = 'consultant' AND cat.type = 'doctor_category') as categories_list
                FROM consultants c
                LEFT JOIN specialties s ON c.specialty_id = s.id
                WHERE c.status = 'published'";
                
        $params = [];

        // Apply filters
        if (!empty($includeIds)) {
            $placeholders = implode(',', array_fill(0, count($includeIds), '?'));
            $sql .= " AND c.id IN ($placeholders)";
            $params = array_merge($params, $includeIds);
        } elseif (!empty($specialties)) {
            $placeholders = implode(',', array_fill(0, count($specialties), '?'));
            $sql .= " AND s.slug IN ($placeholders)";
            $params = array_merge($params, $specialties);
        }

        $consultants = $db->query($sql, $params);

        // Sorting Logic
        usort($consultants, function($a, $b) {
            // Group 1: Dato / Datuk
            $isADato = preg_match('/^(Dato|Datuk)\b/i', $a['name']) ? 1 : 0;
            $isBDato = preg_match('/^(Dato|Datuk)\b/i', $b['name']) ? 1 : 0;
            
            // Group 2: Resident
            $isAResident = (!empty($a['categories_list']) && stripos($a['categories_list'], 'Resident') !== false) ? 1 : 0;
            $isBResident = (!empty($b['categories_list']) && stripos($b['categories_list'], 'Resident') !== false) ? 1 : 0;

            if ($isADato !== $isBDato) {
                return $isBDato <=> $isADato; // 1 comes before 0
            }
            if ($isAResident !== $isBResident) {
                return $isBResident <=> $isAResident;
            }
            
            // Alphabetical fallback
            return strcmp($a['name'], $b['name']);
        });

        if ($limit > 0) {
            $consultants = array_slice($consultants, 0, $limit);
        }

        if (empty($consultants)) {
            return '<div class="alert alert-info">No consultants found.</div>';
        }

        // Render HTML output
        ob_start();
        echo '<div class="consultant-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">';
        foreach ($consultants as $c) {
            echo '<div class="consultant-card card p-3" style="text-align:center; border: 1px solid #e2e8f0; border-radius: 8px;">';
            
            $img = $c['photo'] ? htmlspecialchars(View::asset($c['photo'])) : 'https://ui-avatars.com/api/?name=' . urlencode($c['name']);
            echo '<img src="' . $img . '" alt="' . htmlspecialchars($c['name']) . '" style="width:120px; height:120px; border-radius:50%; object-fit:cover; margin:0 auto 15px auto;">';
            
            echo '<h4 style="margin:0 0 5px 0;">' . htmlspecialchars($c['name']) . '</h4>';
            echo '<p style="color:#64748b; font-size:0.9rem; margin:0 0 10px 0;">' . htmlspecialchars($c['specialty_name'] ?? 'Consultant') . '</p>';
            
            $link = View::url('/doctors/' . $c['slug']);
            echo '<a href="' . $link . '" class="btn btn-sm btn-outline-primary" style="display:inline-block; border:1px solid #6366f1; color:#6366f1; text-decoration:none; padding:5px 15px; border-radius:4px;">View Profile</a>';
            echo '</div>';
        }
        echo '</div>';

        return ob_get_clean();
    }

    /**
     * Render the [flipbook] shortcode using dFlip PDF library
     */
    public static function renderFlipbook(array $matches): string
    {
        $attrString = $matches[1] ?? '';
        $attrs = self::parseAttributes($attrString);

        $url = $attrs['url'] ?? '';
        if (empty($url)) {
            return '<div class="alert alert-danger">Flipbook: URL attribute is missing.</div>';
        }

        // Generate unique ID for this instance
        $flipbookId = 'flipbook_' . md5($url . uniqid());

        ob_start();
        ?>
        <div class="flipbook-container" style="margin-bottom: 30px;">
            <div class="_df_book" height="500" webgl="true" backgroundcolor="transparent"
                source="<?php echo View::he($url); ?>"
                id="<?php echo $flipbookId; ?>">
            </div>
            
            <!-- Load dFlip library only if it hasn't been loaded yet -->
            <script>
                if (typeof jQuery === 'undefined') {
                    document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"><\/script>');
                }
            </script>
            <script>
                if (typeof dFlip === 'undefined') {
                    document.write('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dflip/2.2.222/css/dflip.min.css">');
                    document.write('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dflip/2.2.222/css/themify-icons.min.css">');
                    document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/dflip/2.2.222/js/dflip.min.js"><\/script>');
                }
            </script>
        </div>
        <?php
        return ob_get_clean();
    }
}
