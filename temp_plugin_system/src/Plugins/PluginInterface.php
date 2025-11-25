<?php
declare(strict_types=1);

namespace PluginSystem;

/**
 * Plugin Interface
 *
 * Defines the contract that all plugins must implement
 */
interface PluginInterface
{
    /**
     * Get the plugin name
     */
    public function getName(): string;

    /**
     * Get the plugin version
     */
    public function getVersion(): string;

    /**
     * Get the plugin description
     */
    public function getDescription(): string;

    /**
     * Get the plugin author
     */
    public function getAuthor(): string;

    /**
     * Get minimum application version required
     */
    public function getMinimumAppVersion(): string;

    /**
     * Get maximum application version supported (optional)
     */
    public function getMaximumAppVersion(): ?string;

    /**
     * Get plugin dependencies
     *
     * @return array Array of plugin names this plugin depends on
     */
    public function getDependencies(): array;

    /**
     * Get hooks this plugin registers
     *
     * @return array Array of hook names and their handlers
     */
    public function getHooks(): array;

    /**
     * Get admin menu items this plugin adds
     *
     * @return array Array of menu items
     */
    public function getAdminMenuItems(): array;

    /**
     * Get settings this plugin provides
     *
     * @return array Array of setting definitions
     */
    public function getSettings(): array;

    /**
     * Activate the plugin
     *
     * @return bool True on success, false on failure
     */
    public function activate(): bool;

    /**
     * Deactivate the plugin
     *
     * @return bool True on success, false on failure
     */
    public function deactivate(): bool;

    /**
     * Install the plugin (run once during initial installation)
     *
     * @return bool True on success, false on failure
     */
    public function install(): bool;

    /**
     * Uninstall the plugin (run once during removal)
     *
     * @return bool True on success, false on failure
     */
    public function uninstall(): bool;

    /**
     * Upgrade the plugin from old version to new version
     *
     * @param string $oldVersion
     * @param string $newVersion
     * @return bool True on success, false on failure
     */
    public function upgrade(string $oldVersion, string $newVersion): bool;
}