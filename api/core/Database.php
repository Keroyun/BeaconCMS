<?php
/**
 * Database — Singleton PDO Wrapper
 *
 * Provides a single shared PDO connection and convenience methods for
 * common database operations. Every query uses prepared statements.
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    // ── Constructor (private — use getInstance()) ───────────────────────────
    private function __construct()
    {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new RuntimeException('Database connection failed.');
        }
    }

    /** Prevent cloning of the singleton. */
    private function __clone() {}

    // ── Singleton accessor ──────────────────────────────────────────────────

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /** Return the raw PDO handle when needed. */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    // ── Core query ──────────────────────────────────────────────────────────

    /**
     * Execute an arbitrary SQL statement with bound parameters.
     *
     * @param string $sql    SQL with named/positional placeholders
     * @param array  $params Parameters to bind
     * @return PDOStatement
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // ── INSERT ──────────────────────────────────────────────────────────────

    /**
     * Insert a row into $table.
     *
     * @param string               $table
     * @param array<string,mixed>  $data  column => value
     * @return string              Last insert ID
     */
    public function insert(string $table, array $data): string
    {
        $columns      = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql  = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));

        return $this->pdo->lastInsertId();
    }

    // ── UPDATE ──────────────────────────────────────────────────────────────

    /**
     * Update rows in $table matching $where.
     *
     * @param string               $table
     * @param array<string,mixed>  $data        column => value pairs to set
     * @param string               $where       WHERE clause (e.g. "id = ?")
     * @param array                $whereParams Parameters for the WHERE clause
     * @return int                 Number of affected rows
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = ?";
        }
        $setClause = implode(', ', $setParts);

        $sql  = "UPDATE `{$table}` SET {$setClause} WHERE {$where}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_merge(array_values($data), $whereParams));

        return $stmt->rowCount();
    }

    // ── DELETE ──────────────────────────────────────────────────────────────

    /**
     * Delete rows from $table matching $where.
     *
     * @param string $table
     * @param string $where       WHERE clause
     * @param array  $whereParams Parameters for the WHERE clause
     * @return int   Number of affected rows
     */
    public function delete(string $table, string $where, array $whereParams = []): int
    {
        $sql  = "DELETE FROM `{$table}` WHERE {$where}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($whereParams);

        return $stmt->rowCount();
    }

    // ── SELECT (multiple rows) ──────────────────────────────────────────────

    /**
     * Select rows from $table.
     *
     * @param string $table
     * @param string $where  Optional WHERE clause (without the "WHERE" keyword)
     * @param array  $params Bound parameters
     * @param string $order  Optional ORDER BY clause (e.g. "created_at DESC")
     * @param string $limit  Optional LIMIT clause (e.g. "10" or "10 OFFSET 20")
     * @return array<int,array<string,mixed>>
     */
    public function select(
        string $table,
        string $where = '',
        array  $params = [],
        string $order = '',
        string $limit = ''
    ): array {
        $sql = "SELECT * FROM `{$table}`";

        if ($where !== '') {
            $sql .= " WHERE {$where}";
        }
        if ($order !== '') {
            $sql .= " ORDER BY {$order}";
        }
        if ($limit !== '') {
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    // ── SELECT ONE ──────────────────────────────────────────────────────────

    /**
     * Select a single row from $table.
     *
     * @param string $table
     * @param string $where  WHERE clause
     * @param array  $params Bound parameters
     * @return array<string,mixed>|null
     */
    public function selectOne(string $table, string $where, array $params = []): ?array
    {
        $sql  = "SELECT * FROM `{$table}` WHERE {$where} LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    // ── COUNT ───────────────────────────────────────────────────────────────

    /**
     * Count rows in $table.
     *
     * @param string $table
     * @param string $where  Optional WHERE clause
     * @param array  $params Bound parameters
     * @return int
     */
    public function count(string $table, string $where = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) AS cnt FROM `{$table}`";

        if ($where !== '') {
            $sql .= " WHERE {$where}";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }
}
