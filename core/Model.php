<?php
/**
 * Model — Base Model
 *
 * Provides common CRUD operations that all resource models inherit.
 * Child classes set $table and $fillable to control behaviour.
 */
class Model
{
    /** Database table name. Override in child classes. */
    protected string $table = '';

    /** Columns that may be mass-assigned. Override in child classes. */
    protected array $fillable = [];

    /** @var Database Convenience reference */
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── Read ────────────────────────────────────────────────────────────────

    /**
     * Fetch all rows, optionally ordered.
     *
     * @return array<int,array<string,mixed>>
     */
    public function all(string $order = 'created_at DESC'): array
    {
        return $this->db->select($this->table, '', [], $order);
    }

    /**
     * Find a single row by primary key.
     */
    public function find(int $id): ?array
    {
        return $this->db->selectOne($this->table, 'id = ?', [$id]);
    }

    /**
     * Find a single row by its slug column.
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->db->selectOne($this->table, 'slug = ?', [$slug]);
    }

    /**
     * Find rows matching arbitrary conditions.
     *
     * @param string $conditions  WHERE clause (e.g. "status = ? AND type = ?")
     * @param array  $params      Bound parameters
     * @param string $order       ORDER BY clause
     * @return array<int,array<string,mixed>>
     */
    public function where(string $conditions, array $params = [], string $order = 'created_at DESC'): array
    {
        return $this->db->select($this->table, $conditions, $params, $order);
    }

    // ── Create ──────────────────────────────────────────────────────────────

    /**
     * Insert a new record (only $fillable columns are used).
     * Automatically generates a slug if the record has a title or name field.
     *
     * @return string  Last insert ID
     */
    public function create(array $data): string
    {
        $filtered = $this->filterFillable($data);

        // Auto-generate slug when absent
        if (in_array('slug', $this->fillable, true) && empty($filtered['slug'])) {
            $source = $filtered['title'] ?? $filtered['name'] ?? '';
            if ($source !== '') {
                $filtered['slug'] = $this->generateSlug($source);
            }
        }

        // Timestamps
        $now = date('Y-m-d H:i:s');
        $filtered['created_at'] = $now;
        $filtered['updated_at'] = $now;

        return $this->db->insert($this->table, $filtered);
    }

    // ── Update ──────────────────────────────────────────────────────────────

    /**
     * Update a record by ID (only $fillable columns are used).
     *
     * @return int Affected rows
     */
    public function update(int $id, array $data): int
    {
        $filtered = $this->filterFillable($data);
        $filtered['updated_at'] = date('Y-m-d H:i:s');

        return $this->db->update($this->table, $filtered, 'id = ?', [$id]);
    }

    // ── Delete ──────────────────────────────────────────────────────────────

    /**
     * Delete a record by ID.
     *
     * @return int Affected rows
     */
    public function delete(int $id): int
    {
        return $this->db->delete($this->table, 'id = ?', [$id]);
    }

    // ── Counting ────────────────────────────────────────────────────────────

    /**
     * Count rows matching an optional condition.
     */
    public function count(string $where = '', array $params = []): int
    {
        return $this->db->count($this->table, $where, $params);
    }

    // ── Pagination ──────────────────────────────────────────────────────────

    /**
     * Return a paginated result set.
     *
     * @return array{data:array,total:int,page:int,perPage:int,totalPages:int}
     */
    public function paginate(
        int    $page    = 1,
        int    $perPage = 10,
        string $where   = '',
        array  $params  = [],
        string $order   = 'created_at DESC'
    ): array {
        $page  = max(1, $page);
        $total = $this->db->count($this->table, $where, $params);
        $totalPages = (int) ceil($total / $perPage);

        $offset = ($page - 1) * $perPage;
        $data   = $this->db->select(
            $this->table,
            $where,
            $params,
            $order,
            "{$perPage} OFFSET {$offset}"
        );

        return [
            'data'       => $data,
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => $totalPages,
        ];
    }

    // ── Slug Generation ─────────────────────────────────────────────────────

    /**
     * Generate a unique, URL-safe slug from a title/name string.
     * Appends a numeric suffix (-2, -3, …) if the slug already exists.
     */
    public function generateSlug(string $title): string
    {
        $slug     = Sanitizer::slug($title);
        $original = $slug;
        $counter  = 1;

        while ($this->db->count($this->table, 'slug = ?', [$slug]) > 0) {
            $counter++;
            $slug = $original . '-' . $counter;
        }

        return $slug;
    }

    // ── Internals ───────────────────────────────────────────────────────────

    /**
     * Return only the keys from $data that appear in $fillable.
     */
    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }
}
