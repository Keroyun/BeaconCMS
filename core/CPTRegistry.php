<?php
/**
 * CPTRegistry — Custom Post Type Registry
 *
 * Central registry for all custom post types (CPTs).
 * Each CPT maps to a database table of the same name (plural)
 * and gains admin CRUD routes automatically via the Router.
 */
class CPTRegistry
{
    /** @var array<string,array> Registered CPT definitions keyed by name. */
    private static array $types = [];

    // ── Bootstrap ───────────────────────────────────────────────────────────

    /**
     * Register the built-in CPTs shipped with BeaconCMS.
     * Called once from index.php during bootstrap.
     */
    public static function init(): void
    {
        self::register('consultant', [
            'label'       => 'Consultants',
            'label_singular' => 'Consultant',
            'icon'        => 'fa-user-md',
            'slug'        => 'doctors',
            'table'       => 'consultants',
            'controller'  => 'ConsultantController',
            'fields'      => [
                'name', 'slug', 'title', 'specialty_id', 'qualification',
                'bio', 'photo', 'email', 'phone',
                'meta_title', 'meta_description', 'status',
            ],
        ]);

        self::register('promotion', [
            'label'       => 'Promotions',
            'label_singular' => 'Promotion',
            'icon'        => 'fa-bullhorn',
            'slug'        => 'promotions',
            'table'       => 'promotions',
            'controller'  => 'PromotionController',
            'fields'      => [
                'title', 'slug', 'content', 'excerpt', 'image',
                'start_date', 'end_date',
                'meta_title', 'meta_description', 'status',
            ],
        ]);

        self::register('specialty', [
            'label'       => 'Specialties',
            'label_singular' => 'Specialty',
            'icon'        => 'fa-hospital',
            'slug'        => 'specialties',
            'table'       => 'specialties',
            'controller'  => 'SpecialtyController',
            'fields'      => [
                'name', 'slug', 'description', 'icon', 'image',
                'meta_title', 'meta_description', 'status',
            ],
        ]);
    }

    // ── Registration ────────────────────────────────────────────────────────

    /**
     * Register (or overwrite) a custom post type.
     *
     * @param string $name   Machine name (e.g. "consultant")
     * @param array  $config Configuration array
     */
    public static function register(string $name, array $config): void
    {
        // Merge sensible defaults
        $defaults = [
            'label'            => ucfirst($name) . 's',
            'label_singular'   => ucfirst($name),
            'icon'             => 'fa-file',
            'slug'             => $name . 's',
            'table'            => $name . 's',
            'controller'       => ucfirst($name) . 'Controller',
            'fields'           => [],
        ];

        self::$types[$name] = array_merge($defaults, $config);
    }

    // ── Accessors ───────────────────────────────────────────────────────────

    /**
     * Retrieve a single CPT config by name.
     *
     * @return array|null
     */
    public static function get(string $name): ?array
    {
        return self::$types[$name] ?? null;
    }

    /**
     * Retrieve all registered CPTs.
     *
     * @return array<string,array>
     */
    public static function getAll(): array
    {
        return self::$types;
    }

    /**
     * Build an array of admin sidebar menu items from registered CPTs.
     *
     * @return array<int,array{label:string,icon:string,url:string}>
     */
    public static function getMenuItems(): array
    {
        $items = [];

        foreach (self::$types as $name => $config) {
            $items[] = [
                'label' => $config['label'],
                'icon'  => $config['icon'],
                'url'   => '/admin/' . $name . 's',   // plural route segment
                'name'  => $name,
            ];
        }

        return $items;
    }
}
