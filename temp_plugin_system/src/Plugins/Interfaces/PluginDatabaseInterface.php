<?php
declare(strict_types=1);

namespace PluginSystem\Interfaces;

/**
 * Plugin Database Interface
 *
 * Defines database operations needed by the plugin system.
 * This allows the plugin system to be independent of specific database implementations.
 */
interface PluginDatabaseInterface
{
    /**
     * Execute a query
     *
     * @param string $sql SQL query
     * @param string|null $errorMsg Error message
     * @return mixed Query result
     */
    public function query(string $sql, ?string $errorMsg = null);

    /**
     * Fetch associative array from result
     *
     * @param mixed $result Query result
     * @return array|null Row data or null
     */
    public function fetchAssoc($result): ?array;

    /**
     * Escape a string for SQL
     *
     * @param string $value Value to escape
     * @return string Escaped value
     */
    public function escape(string $value): string;

    /**
     * Get table prefix
     *
     * @return string Table prefix
     */
    public function getTablePrefix(): string;

    /**
     * Get last insert ID
     *
     * @return string Last insert ID
     */
    public function insertId(): string;
}