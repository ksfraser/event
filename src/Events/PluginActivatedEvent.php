<?php
declare(strict_types=1);

namespace EventSystem\Events;

use EventSystem\Events\Event;

/**
 * Plugin Activated Event
 * Fired when a plugin is successfully activated
 */
class PluginActivatedEvent extends Event
{
    private string $pluginName;
    private $plugin;

    public function __construct(string $pluginName, $plugin)
    {
        $this->pluginName = $pluginName;
        $this->plugin = $plugin;
    }

    public function getPluginName(): string
    {
        return $this->pluginName;
    }

    public function getPlugin()
    {
        return $this->plugin;
    }
}