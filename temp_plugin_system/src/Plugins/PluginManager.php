<?php
declare(strict_types=1);

namespace PluginSystem;

use EventSystem\EventManager;
use EventSystem\Events\PluginActivatedEvent;
use EventSystem\Events\PluginDeactivatedEvent;
use EventSystem\Events\PluginInstalledEvent;
use EventSystem\Events\PluginUninstalledEvent;
use PluginSystem\Interfaces\PluginDatabaseInterface;
use PluginSystem\Interfaces\PluginEventDispatcherInterface;
use PluginSystem\PluginInterface;

/**
 * Plugin Manager
 *
 * Manages plugin lifecycle, loading, activation, and dependencies
 */
class PluginManager
{
    private static ?PluginManager $instance = null;
    private array $loadedPlugins = [];
    private array $activePlugins = [];
    private array $pluginRegistry = [];
    private PluginDatabaseInterface $db;
    private PluginEventDispatcherInterface $eventDispatcher;

    /**
     * Get singleton instance
     */
    public static function getInstance(?PluginDatabaseInterface $db = null, ?PluginEventDispatcherInterface $eventDispatcher = null): PluginManager
    {
        if (self::$instance === null) {
            if ($db === null) {
                // Default to FA database adapter if none provided
                $db = new \FA\Plugins\Database\FADatabaseAdapter();
            }
            if ($eventDispatcher === null) {
                // Default to FA event dispatcher if none provided
                $eventDispatcher = new \FA\Plugins\EventDispatcher\FAEventDispatcherAdapter();
            }
            self::$instance = new self($db, $eventDispatcher);
        }
        return self::$instance;
    }

    /**
     * Private constructor for singleton
     */
    private function __construct(PluginDatabaseInterface $db, PluginEventDispatcherInterface $eventDispatcher)
    {
        $this->db = $db;
        $this->eventDispatcher = $eventDispatcher;

        // Only load from database if functions are available
        if ($this->areDatabaseFunctionsAvailable()) {
            $this->loadPluginRegistry();
            $this->loadActivePlugins();
        }
    }

    /**
     * Check if database functions are available
     */
    private function areDatabaseFunctionsAvailable(): bool
    {
        try {
            // Try to call a database method to see if it works
            $this->db->query("SELECT 1", null);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Load plugin registry from database
     */
    private function loadPluginRegistry(): void
    {
        // In test environment, skip database operations
        if (!$this->areDatabaseFunctionsAvailable()) {
            return;
        }

        $sql = "SELECT * FROM " . $this->db->getTablePrefix() . "plugin_registry ORDER BY name";
        $result = $this->db->query($sql);

        while ($row = $this->db->fetchAssoc($result)) {
            $this->pluginRegistry[$row['name']] = $row;
        }
    }

    /**
     * Load active plugins from database
     */
    private function loadActivePlugins(): void
    {
        // In test environment, skip database operations
        if (!$this->areDatabaseFunctionsAvailable()) {
            return;
        }

        $sql = "SELECT plugin_name FROM " . $this->db->getTablePrefix() . "active_plugins ORDER BY plugin_name";
        $result = $this->db->query($sql);

        while ($row = $this->db->fetchAssoc($result)) {
            $this->activePlugins[] = $row['plugin_name'];
        }
    }

    public function registerPlugin(PluginInterface $plugin): bool
    {
        $pluginName = $plugin->getName();

        // Check if plugin is already registered
        if (isset($this->pluginRegistry[$pluginName])) {
            return $this->updatePluginRegistration($plugin);
        }

        // In test environment, skip database operations
        if (!$this->areDatabaseFunctionsAvailable()) {
            $data = [
                'name' => $pluginName,
                'version' => $plugin->getVersion(),
                'description' => $plugin->getDescription(),
                'author' => $plugin->getAuthor(),
                'min_app_version' => $plugin->getMinimumAppVersion(),
                'max_app_version' => $plugin->getMaximumAppVersion(),
                'dependencies' => json_encode($plugin->getDependencies()),
                'hooks' => json_encode($plugin->getHooks()),
                'admin_menu_items' => json_encode($plugin->getAdminMenuItems()),
                'settings' => json_encode($plugin->getSettings()),
                'installed' => 0,
                'active' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->pluginRegistry[$pluginName] = $data;
            $this->loadedPlugins[$pluginName] = $plugin;
            return true;
        }

        // Register new plugin
        $sql = "INSERT INTO " . $this->db->getTablePrefix() . "plugin_registry (name, version, description, author, min_app_version, max_app_version, dependencies, hooks, admin_menu_items, settings, installed, active, created_at, updated_at) VALUES (" .
            $this->db->escape($pluginName) . ", " .
            $this->db->escape($plugin->getVersion()) . ", " .
            $this->db->escape($plugin->getDescription()) . ", " .
            $this->db->escape($plugin->getAuthor()) . ", " .
            $this->db->escape($plugin->getMinimumAppVersion()) . ", " .
            $this->db->escape($plugin->getMaximumAppVersion()) . ", " .
            $this->db->escape(json_encode($plugin->getDependencies())) . ", " .
            $this->db->escape(json_encode($plugin->getHooks())) . ", " .
            $this->db->escape(json_encode($plugin->getAdminMenuItems())) . ", " .
            $this->db->escape(json_encode($plugin->getSettings())) . ", " .
            "0, 0, " .
            $this->db->escape(date('Y-m-d H:i:s')) . ", " .
            $this->db->escape(date('Y-m-d H:i:s')) . ")";

        $result = $this->db->query($sql);

        if ($result) {
            $data = [
                'name' => $pluginName,
                'version' => $plugin->getVersion(),
                'description' => $plugin->getDescription(),
                'author' => $plugin->getAuthor(),
                'min_app_version' => $plugin->getMinimumAppVersion(),
                'max_app_version' => $plugin->getMaximumAppVersion(),
                'dependencies' => json_encode($plugin->getDependencies()),
                'hooks' => json_encode($plugin->getHooks()),
                'admin_menu_items' => json_encode($plugin->getAdminMenuItems()),
                'settings' => json_encode($plugin->getSettings()),
                'installed' => 0,
                'active' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->pluginRegistry[$pluginName] = $data;
            $this->loadedPlugins[$pluginName] = $plugin;
            return true;
        }

        return false;
    }

    /**
     * Update existing plugin registration
     */
    private function updatePluginRegistration(PluginInterface $plugin): bool
    {
        $pluginName = $plugin->getName();

        // In test environment, skip database operations
        if (!$this->areDatabaseFunctionsAvailable()) {
            $this->pluginRegistry[$pluginName]['version'] = $plugin->getVersion();
            $this->pluginRegistry[$pluginName]['description'] = $plugin->getDescription();
            $this->pluginRegistry[$pluginName]['author'] = $plugin->getAuthor();
            $this->pluginRegistry[$pluginName]['min_app_version'] = $plugin->getMinimumAppVersion();
            $this->pluginRegistry[$pluginName]['max_app_version'] = $plugin->getMaximumAppVersion();
            $this->pluginRegistry[$pluginName]['dependencies'] = json_encode($plugin->getDependencies());
            $this->pluginRegistry[$pluginName]['hooks'] = json_encode($plugin->getHooks());
            $this->pluginRegistry[$pluginName]['admin_menu_items'] = json_encode($plugin->getAdminMenuItems());
            $this->pluginRegistry[$pluginName]['settings'] = json_encode($plugin->getSettings());
            $this->pluginRegistry[$pluginName]['updated_at'] = date('Y-m-d H:i:s');
            $this->loadedPlugins[$pluginName] = $plugin;
            return true;
        }

        $sql = "UPDATE " . $this->db->getTablePrefix() . "plugin_registry SET " .
            "version = " . $this->db->escape($plugin->getVersion()) . ", " .
            "description = " . $this->db->escape($plugin->getDescription()) . ", " .
            "author = " . $this->db->escape($plugin->getAuthor()) . ", " .
            "min_app_version = " . $this->db->escape($plugin->getMinimumAppVersion()) . ", " .
            "max_app_version = " . $this->db->escape($plugin->getMaximumAppVersion()) . ", " .
            "dependencies = " . $this->db->escape(json_encode($plugin->getDependencies())) . ", " .
            "hooks = " . $this->db->escape(json_encode($plugin->getHooks())) . ", " .
            "admin_menu_items = " . $this->db->escape(json_encode($plugin->getAdminMenuItems())) . ", " .
            "settings = " . $this->db->escape(json_encode($plugin->getSettings())) . ", " .
            "updated_at = " . $this->db->escape(date('Y-m-d H:i:s')) . " " .
            "WHERE name = " . $this->db->escape($pluginName);

        $result = $this->db->query($sql);

        if ($result) {
            $this->pluginRegistry[$pluginName]['version'] = $plugin->getVersion();
            $this->pluginRegistry[$pluginName]['description'] = $plugin->getDescription();
            $this->pluginRegistry[$pluginName]['author'] = $plugin->getAuthor();
            $this->pluginRegistry[$pluginName]['min_app_version'] = $plugin->getMinimumAppVersion();
            $this->pluginRegistry[$pluginName]['max_app_version'] = $plugin->getMaximumAppVersion();
            $this->pluginRegistry[$pluginName]['dependencies'] = json_encode($plugin->getDependencies());
            $this->pluginRegistry[$pluginName]['hooks'] = json_encode($plugin->getHooks());
            $this->pluginRegistry[$pluginName]['admin_menu_items'] = json_encode($plugin->getAdminMenuItems());
            $this->pluginRegistry[$pluginName]['settings'] = json_encode($plugin->getSettings());
            $this->pluginRegistry[$pluginName]['updated_at'] = date('Y-m-d H:i:s');
            $this->loadedPlugins[$pluginName] = $plugin;
            return true;
        }

        return false;
    }

    public function installPlugin(string $pluginName): bool
    {
        if (!isset($this->loadedPlugins[$pluginName])) {
            return false;
        }

        $plugin = $this->loadedPlugins[$pluginName];

        // Check dependencies
        if (!$this->checkDependencies($plugin)) {
            return false;
        }

        // Run installation
        if (!$plugin->install()) {
            return false;
        }

        // In test environment, skip database operations
        if (!$this->areDatabaseFunctionsAvailable()) {
            $this->pluginRegistry[$pluginName]['installed'] = 1;
            $this->pluginRegistry[$pluginName]['installed_at'] = date('Y-m-d H:i:s');
            return true;
        }

        // Update registry
        $sql = "UPDATE " . $this->db->getTablePrefix() . "plugin_registry SET " .
            "installed = 1, " .
            "installed_at = " . $this->db->escape(date('Y-m-d H:i:s')) . ", " .
            "updated_at = " . $this->db->escape(date('Y-m-d H:i:s')) . " " .
            "WHERE name = " . $this->db->escape($pluginName);

        $result = $this->db->query($sql);

        if ($result) {
            $this->pluginRegistry[$pluginName]['installed'] = 1;
            $this->pluginRegistry[$pluginName]['installed_at'] = date('Y-m-d H:i:s');

            // Dispatch event
            $this->eventDispatcher->dispatch(new PluginInstalledEvent($pluginName, $plugin));

            return true;
        }

        return false;
    }

    public function activatePlugin(string $pluginName): bool
    {
        if (!isset($this->loadedPlugins[$pluginName])) {
            return false;
        }

        $plugin = $this->loadedPlugins[$pluginName];

        // Check if plugin is installed
        if (!$this->pluginRegistry[$pluginName]['installed']) {
            return false;
        }

        // Check dependencies
        if (!$this->checkDependencies($plugin)) {
            return false;
        }

        // Run activation
        if (!$plugin->activate()) {
            return false;
        }

        // Register hooks
        $this->registerPluginHooks($plugin);

        // In test environment, skip database operations
        if (!$this->areDatabaseFunctionsAvailable()) {
            if (!in_array($pluginName, $this->activePlugins)) {
                $this->activePlugins[] = $pluginName;
            }
            $this->pluginRegistry[$pluginName]['active'] = 1;
            $this->pluginRegistry[$pluginName]['activated_at'] = date('Y-m-d H:i:s');
            return true;
        }

        // Add to active plugins
        if (!in_array($pluginName, $this->activePlugins)) {
            $this->activePlugins[] = $pluginName;

            // Update database
            $sql = "INSERT INTO " . $this->db->getTablePrefix() . "active_plugins (plugin_name, activated_at) VALUES (" .
                $this->db->escape($pluginName) . ", " .
                $this->db->escape(date('Y-m-d H:i:s')) . ")";
            $this->db->query($sql);
        }

        // Update registry
        $sql = "UPDATE " . $this->db->getTablePrefix() . "plugin_registry SET " .
            "active = 1, " .
            "activated_at = " . $this->db->escape(date('Y-m-d H:i:s')) . ", " .
            "updated_at = " . $this->db->escape(date('Y-m-d H:i:s')) . " " .
            "WHERE name = " . $this->db->escape($pluginName);

        $result = $this->db->query($sql);

        if ($result) {
            $this->pluginRegistry[$pluginName]['active'] = 1;
            $this->pluginRegistry[$pluginName]['activated_at'] = date('Y-m-d H:i:s');

            // Dispatch event
            $this->eventDispatcher->dispatch(new PluginActivatedEvent($pluginName, $plugin));

            return true;
        }

        return false;
    }

    public function deactivatePlugin(string $pluginName): bool
    {
        if (!isset($this->loadedPlugins[$pluginName])) {
            return false;
        }

        $plugin = $this->loadedPlugins[$pluginName];

        // Run deactivation
        if (!$plugin->deactivate()) {
            return false;
        }

        // Unregister hooks
        $this->unregisterPluginHooks($plugin);

        // Remove from active plugins
        if (($key = array_search($pluginName, $this->activePlugins)) !== false) {
            unset($this->activePlugins[$key]);

            // In test environment, skip database operations
            if (!$this->areDatabaseFunctionsAvailable()) {
                $this->pluginRegistry[$pluginName]['active'] = 0;
                $this->pluginRegistry[$pluginName]['deactivated_at'] = date('Y-m-d H:i:s');
                return true;
            }

            // Update database
            $sql = "DELETE FROM " . $this->db->getTablePrefix() . "active_plugins WHERE plugin_name = " . $this->db->escape($pluginName);
            $this->db->query($sql);
        }

        // Update registry
        $sql = "UPDATE " . $this->db->getTablePrefix() . "plugin_registry SET " .
            "active = 0, " .
            "deactivated_at = " . $this->db->escape(date('Y-m-d H:i:s')) . ", " .
            "updated_at = " . $this->db->escape(date('Y-m-d H:i:s')) . " " .
            "WHERE name = " . $this->db->escape($pluginName);

        $result = $this->db->query($sql);

        if ($result) {
            $this->pluginRegistry[$pluginName]['active'] = 0;
            $this->pluginRegistry[$pluginName]['deactivated_at'] = date('Y-m-d H:i:s');

            // Dispatch event
            $this->eventDispatcher->dispatch(new PluginDeactivatedEvent($pluginName, $plugin));

            return true;
        }

        return false;
    }

    /**
     * Uninstall a plugin
     *
     * @param string $pluginName
     * @return bool
     */
    public function uninstallPlugin(string $pluginName): bool
    {
        if (!isset($this->loadedPlugins[$pluginName])) {
            return false;
        }

        $plugin = $this->loadedPlugins[$pluginName];

        // Deactivate first if active
        if ($this->isPluginActive($pluginName)) {
            $this->deactivatePlugin($pluginName);
        }

        // Run uninstallation
        if (!$plugin->uninstall()) {
            return false;
        }

        // In test environment, skip database operations
        if (!$this->areDatabaseFunctionsAvailable()) {
            unset($this->loadedPlugins[$pluginName]);
            unset($this->pluginRegistry[$pluginName]);
            return true;
        }

        // Remove from registry
        $sql = "DELETE FROM " . $this->db->getTablePrefix() . "plugin_registry WHERE name = " . $this->db->escape($pluginName);
        $result = $this->db->query($sql);

        if ($result) {
            unset($this->pluginRegistry[$pluginName]);
            unset($this->loadedPlugins[$pluginName]);

            // Dispatch event
            $this->eventDispatcher->dispatch(new PluginUninstalledEvent($pluginName, $plugin));

            return true;
        }

        return false;
    }

    /**
     * Check if plugin dependencies are satisfied
     */
    private function checkDependencies(PluginInterface $plugin): bool
    {
        $dependencies = $plugin->getDependencies();

        foreach ($dependencies as $dependency) {
            if (!$this->isPluginActive($dependency)) {
                error_log("Plugin {$plugin->getName()} requires dependency: {$dependency}");
                return false;
            }
        }

        return true;
    }

    /**
     * Register plugin hooks with the event system
     */
    private function registerPluginHooks(PluginInterface $plugin): void
    {
        $hooks = $plugin->getHooks();

        foreach ($hooks as $eventName => $handler) {
            if (is_callable($handler)) {
                $this->eventDispatcher->on($eventName, $handler);
            } elseif (is_array($handler) && count($handler) === 2) {
                // Handler specified as [class, method]
                $callable = [$plugin, $handler[1]];
                if (is_callable($callable)) {
                    $this->eventDispatcher->on($eventName, $callable);
                }
            }
        }
    }

    /**
     * Unregister plugin hooks from the event system
     */
    private function unregisterPluginHooks(PluginInterface $plugin): void
    {
        $hooks = $plugin->getHooks();

        foreach ($hooks as $eventName => $handler) {
            // Note: EventManager doesn't currently support removing specific listeners
            // This would need to be enhanced in the EventManager
            // For now, we'll rely on plugin deactivation to stop hook execution
        }
    }

    /**
     * Check if a plugin is active
     */
    public function isPluginActive(string $pluginName): bool
    {
        return in_array($pluginName, $this->activePlugins);
    }

    /**
     * Check if a plugin is installed
     */
    public function isPluginInstalled(string $pluginName): bool
    {
        return isset($this->pluginRegistry[$pluginName]) &&
               $this->pluginRegistry[$pluginName]['installed'];
    }

    /**
     * Get all loaded plugins
     */
    public function getLoadedPlugins(): array
    {
        return $this->loadedPlugins;
    }

    /**
     * Get all active plugins
     */
    public function getActivePlugins(): array
    {
        return $this->activePlugins;
    }

    /**
     * Get plugin registry
     */
    public function getPluginRegistry(): array
    {
        return $this->pluginRegistry;
    }

    /**
     * Get a specific plugin instance
     */
    public function getPlugin(string $pluginName): ?PluginInterface
    {
        return $this->loadedPlugins[$pluginName] ?? null;
    }

    /**
     * Load plugins from filesystem
     *
     * @param string $pluginsDir Directory containing plugin files
     */
    public function loadPluginsFromDirectory(string $pluginsDir): void
    {
        if (!is_dir($pluginsDir)) {
            return;
        }

        $pluginFiles = glob($pluginsDir . '/*.php');

        foreach ($pluginFiles as $pluginFile) {
            $this->loadPluginFromFile($pluginFile);
        }
    }

    /**
     * Load a single plugin from file
     */
    private function loadPluginFromFile(string $pluginFile): void
    {
        if (!file_exists($pluginFile)) {
            return;
        }

        try {
            // Include the plugin file
            include_once $pluginFile;

            // Try to find the plugin class (assuming it matches filename)
            $className = basename($pluginFile, '.php');
            $fullClassName = "FA\\Plugins\\{$className}";

            if (class_exists($fullClassName)) {
                $plugin = new $fullClassName();

                if ($plugin instanceof PluginInterface) {
                    $this->registerPlugin($plugin);
                }
            }
        } catch (\Exception $e) {
            error_log("Failed to load plugin from {$pluginFile}: " . $e->getMessage());
        }
    }
}