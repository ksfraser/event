<?php
declare(strict_types=1);

namespace PluginSystem;

/**
 * Base Plugin Class
 *
 * Provides a foundation for creating plugins with common functionality
 */
abstract class BasePlugin implements PluginInterface
{
    protected string $name;
    protected string $version;
    protected string $description;
    protected string $author;
    protected string $minAppVersion;
    protected ?string $maxAppVersion;
    protected array $dependencies = [];
    protected array $hooks = [];
    protected array $adminMenuItems = [];
    protected array $settings = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->initializePlugin();
    }

    /**
     * Initialize plugin properties
     * Override this method in your plugin class
     */
    abstract protected function initializePlugin(): void;

    /**
     * Get the plugin name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the plugin version
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Get the plugin description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get the plugin author
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * Get minimum application version required
     */
    public function getMinimumAppVersion(): string
    {
        return $this->minAppVersion;
    }

    /**
     * Get maximum application version supported
     */
    public function getMaximumAppVersion(): ?string
    {
        return $this->maxAppVersion;
    }

    /**
     * Get plugin dependencies
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Get hooks this plugin registers
     */
    public function getHooks(): array
    {
        return $this->hooks;
    }

    /**
     * Get admin menu items this plugin adds
     */
    public function getAdminMenuItems(): array
    {
        return $this->adminMenuItems;
    }

    /**
     * Get settings this plugin provides
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * Register a hook
     */
    protected function registerHook(string $eventName, callable $handler, int $priority = 10): void
    {
        $this->hooks[$eventName] = $handler;
    }

    /**
     * Add an admin menu item
     */
    protected function addAdminMenuItem(string $title, string $url, string $access = 'SA_OPEN', int $order = 999): void
    {
        $this->adminMenuItems[] = [
            'title' => $title,
            'url' => $url,
            'access' => $access,
            'order' => $order
        ];
    }

    /**
     * Add a setting
     */
    protected function addSetting(string $key, string $label, string $type = 'text', $default = null, string $description = ''): void
    {
        $this->settings[$key] = [
            'label' => $label,
            'type' => $type,
            'default' => $default,
            'description' => $description
        ];
    }

    /**
     * Activate the plugin
     */
    public function activate(): bool
    {
        // Note: Hooks are now registered by PluginManager during activation
        // This prevents double registration and makes the system more flexible

        return $this->onActivate();
    }

    /**
     * Deactivate the plugin
     */
    public function deactivate(): bool
    {
        // Unregister hooks from the event system
        // Note: EventManager doesn't currently support removing specific listeners
        // This would need to be enhanced in the EventManager

        return $this->onDeactivate();
    }

    /**
     * Install the plugin
     */
    public function install(): bool
    {
        return $this->onInstall();
    }

    /**
     * Uninstall the plugin
     */
    public function uninstall(): bool
    {
        return $this->onUninstall();
    }

    /**
     * Upgrade the plugin
     */
    public function upgrade(string $oldVersion, string $newVersion): bool
    {
        return $this->onUpgrade($oldVersion, $newVersion);
    }

    /**
     * Called when plugin is activated
     * Override this method in your plugin class
     */
    protected function onActivate(): bool
    {
        return true;
    }

    /**
     * Called when plugin is deactivated
     * Override this method in your plugin class
     */
    protected function onDeactivate(): bool
    {
        return true;
    }

    /**
     * Called when plugin is installed
     * Override this method in your plugin class
     */
    protected function onInstall(): bool
    {
        return true;
    }

    /**
     * Called when plugin is uninstalled
     * Override this method in your plugin class
     */
    protected function onUninstall(): bool
    {
        return true;
    }

    /**
     * Called when plugin is upgraded
     * Override this method in your plugin class
     */
    protected function onUpgrade(string $oldVersion, string $newVersion): bool
    {
        return true;
    }

    /**
     * Get a plugin setting
     */
    protected function getSetting(string $key)
    {
        // TODO: Implement setting retrieval from database
        return $this->settings[$key]['default'] ?? null;
    }

    /**
     * Set a plugin setting
     */
    protected function setSetting(string $key, $value): bool
    {
        // TODO: Implement setting storage in database
        return true;
    }

    /**
     * Log a message
     */
    protected function log(string $message, string $level = 'info', array $context = []): void
    {
        // TODO: Implement logging to plugin_logs table
        error_log("{$this->name} [{$level}]: {$message}");
    }
}