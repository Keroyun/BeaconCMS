<?php
declare(strict_types=1);

/**
 * Taxonomy — Category & Tag system for BeaconCMS
 * 
 * Supports multiple taxonomy types (categories, tags, custom)
 * that can be attached to any content type (posts, consultants, specialties, etc.)
 * 
 * Examples:
 * - Doctor Category: Resident, Visiting Consultant, Specialist
 * - Post Category: News, Health Tips, Research
 * - Language Spoken: English, Malay, Mandarin, Tamil
 * - Department: Inpatient, Outpatient
 */
class Taxonomy
{
    /**
     * Get all taxonomy types (e.g., 'doctor_category', 'post_category', 'language_spoken').
     */
    public static function getTypes(): array
    {
        return [
            'post_category' => [
                'label' => 'Post Categories',
                'singular' => 'Category',
                'content_type' => 'post',
                'icon' => 'fa-solid fa-folder',
            ],
            'doctor_category' => [
                'label' => 'Doctor Categories',
                'singular' => 'Category',
                'content_type' => 'consultant',
                'icon' => 'fa-solid fa-user-tag',
            ],
            'language_spoken' => [
                'label' => 'Languages Spoken',
                'singular' => 'Language',
                'content_type' => 'consultant',
                'icon' => 'fa-solid fa-language',
            ],
            'specialty_category' => [
                'label' => 'Specialty Categories',
                'singular' => 'Category',
                'content_type' => 'specialty',
                'icon' => 'fa-solid fa-tags',
            ],
        ];
    }

    /**
     * Get taxonomy type config by key.
     */
    public static function getType(string $type): ?array
    {
        return self::getTypes()[$type] ?? null;
    }

    /**
     * Get all categories for a taxonomy type.
     */
    public static function getCategories(string $taxonomyType, ?int $parentId = null): array
    {
        $db = Database::getInstance();

        if ($parentId !== null) {
            $stmt = $db->query(
                "SELECT * FROM categories WHERE taxonomy_type = ? AND parent_id = ? ORDER BY sort_order ASC, name ASC",
                [$taxonomyType, $parentId]
            );
        } else {
            $stmt = $db->query(
                "SELECT * FROM categories WHERE taxonomy_type = ? ORDER BY sort_order ASC, name ASC",
                [$taxonomyType]
            );
        }

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get a single category by ID.
     */
    public static function find(int $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM categories WHERE id = ?", [$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Get a single category by slug and type.
     */
    public static function findBySlug(string $slug, string $taxonomyType): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->query(
            "SELECT * FROM categories WHERE slug = ? AND taxonomy_type = ?",
            [$slug, $taxonomyType]
        );
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Create a new category.
     */
    public static function create(array $data): int
    {
        $db = Database::getInstance();

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Sanitizer::slug($data['name'] ?? 'untitled');
        }

        return (int)$db->insert('categories', [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? '',
            'taxonomy_type' => $data['taxonomy_type'],
            'parent_id' => $data['parent_id'] ?: null,
            'icon' => $data['icon'] ?? '',
            'color' => $data['color'] ?? '',
            'sort_order' => (int)($data['sort_order'] ?? 0),
        ]);
    }

    /**
     * Update a category.
     */
    public static function update(int $id, array $data): void
    {
        $db = Database::getInstance();

        $updateData = [];
        $allowed = ['name', 'slug', 'description', 'parent_id', 'icon', 'color', 'sort_order'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (!empty($updateData)) {
            $db->update('categories', $updateData, 'id = ?', [$id]);
        }
    }

    /**
     * Delete a category and its assignments.
     */
    public static function delete(int $id): void
    {
        $db = Database::getInstance();
        $db->delete('category_items', 'category_id = ?', [$id]);
        $db->delete('categories', 'id = ?', [$id]);
    }

    // ── Content Assignment ──────────────────────────────────────────────────

    /**
     * Assign categories to a content item.
     * Replaces all existing assignments of the given taxonomy type.
     */
    public static function assignToContent(string $contentType, int $contentId, string $taxonomyType, array $categoryIds): void
    {
        $db = Database::getInstance();

        // Remove existing assignments for this taxonomy type
        $db->query(
            "DELETE ci FROM category_items ci 
             INNER JOIN categories c ON ci.category_id = c.id 
             WHERE ci.content_type = ? AND ci.content_id = ? AND c.taxonomy_type = ?",
            [$contentType, $contentId, $taxonomyType]
        );

        // Insert new assignments
        foreach ($categoryIds as $catId) {
            $catId = (int)$catId;
            if ($catId > 0) {
                $db->insert('category_items', [
                    'category_id' => $catId,
                    'content_type' => $contentType,
                    'content_id' => $contentId,
                ]);
            }
        }
    }

    /**
     * Get categories assigned to a content item.
     */
    public static function getForContent(string $contentType, int $contentId, ?string $taxonomyType = null): array
    {
        $db = Database::getInstance();

        $sql = "SELECT c.* FROM categories c 
                INNER JOIN category_items ci ON c.id = ci.category_id 
                WHERE ci.content_type = ? AND ci.content_id = ?";
        $params = [$contentType, $contentId];

        if ($taxonomyType) {
            $sql .= " AND c.taxonomy_type = ?";
            $params[] = $taxonomyType;
        }

        $sql .= " ORDER BY c.sort_order ASC, c.name ASC";

        $stmt = $db->query($sql, $params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get content items that have a specific category.
     */
    public static function getContentByCategory(int $categoryId, string $contentType): array
    {
        $db = Database::getInstance();
        $stmt = $db->query(
            "SELECT content_id FROM category_items WHERE category_id = ? AND content_type = ?",
            [$categoryId, $contentType]
        );
        return array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'content_id');
    }

    /**
     * Get category count for a taxonomy type (with item counts).
     */
    public static function getCategoriesWithCount(string $taxonomyType): array
    {
        $db = Database::getInstance();
        $stmt = $db->query(
            "SELECT c.*, COUNT(ci.id) as item_count 
             FROM categories c 
             LEFT JOIN category_items ci ON c.id = ci.category_id 
             WHERE c.taxonomy_type = ? 
             GROUP BY c.id 
             ORDER BY c.sort_order ASC, c.name ASC",
            [$taxonomyType]
        );
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ── Admin UI Helpers ────────────────────────────────────────────────────

    /**
     * Render a checkbox list of categories for a form.
     */
    public static function renderCheckboxes(string $taxonomyType, string $contentType, ?int $contentId = null): string
    {
        $categories = self::getCategories($taxonomyType);
        $selected = $contentId ? self::getForContent($contentType, $contentId, $taxonomyType) : [];
        $selectedIds = array_column($selected, 'id');

        $typeInfo = self::getType($taxonomyType);
        $html = '<div class="taxonomy-checkboxes" data-type="' . htmlspecialchars($taxonomyType) . '">';
        $html .= '<label class="taxonomy-label"><i class="' . htmlspecialchars($typeInfo['icon'] ?? 'fa-solid fa-tag') . '"></i> ';
        $html .= htmlspecialchars($typeInfo['label'] ?? $taxonomyType) . '</label>';

        if (empty($categories)) {
            $html .= '<p class="text-muted" style="font-size:0.85rem">No ' . htmlspecialchars(strtolower($typeInfo['label'] ?? 'categories')) . ' yet. ';
            $html .= '<a href="' . htmlspecialchars(url('/admin/categories?type=' . $taxonomyType)) . '">Add some</a>.</p>';
        } else {
            $html .= '<div class="checkbox-list">';
            foreach ($categories as $cat) {
                $checked = in_array($cat['id'], $selectedIds) ? ' checked' : '';
                $html .= '<label class="checkbox-item">';
                $html .= '<input type="checkbox" name="categories[' . htmlspecialchars($taxonomyType) . '][]" ';
                $html .= 'value="' . (int)$cat['id'] . '"' . $checked . '>';
                if (!empty($cat['color'])) {
                    $html .= '<span class="cat-dot" style="background:' . htmlspecialchars($cat['color']) . '"></span>';
                }
                $html .= '<span>' . htmlspecialchars($cat['name']) . '</span>';
                $html .= '</label>';
            }
            $html .= '</div>';
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Render category badges for display.
     */
    public static function renderBadges(string $contentType, int $contentId, ?string $taxonomyType = null): string
    {
        $categories = self::getForContent($contentType, $contentId, $taxonomyType);
        if (empty($categories)) return '';

        $html = '<div class="category-badges">';
        foreach ($categories as $cat) {
            $style = !empty($cat['color']) ? ' style="background:' . htmlspecialchars($cat['color']) . '"' : '';
            $html .= '<span class="cat-badge"' . $style . '>' . htmlspecialchars($cat['name']) . '</span>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Process category form submissions.
     * Call this after creating/updating content.
     */
    public static function processFormSubmission(string $contentType, int $contentId, array $postData): void
    {
        $categories = $postData['categories'] ?? [];

        foreach (self::getTypes() as $type => $config) {
            if ($config['content_type'] === $contentType) {
                $catIds = $categories[$type] ?? [];
                self::assignToContent($contentType, $contentId, $type, $catIds);
            }
        }
    }
}
