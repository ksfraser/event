<?php
declare(strict_types=1);

namespace FA\Contracts;

use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * Extended Listener Provider Interface
 * Extends PSR-14 with additional management methods
 */
interface ExtendedListenerProviderInterface extends ListenerProviderInterface
{
    /**
     * Register a listener for a specific event
     *
     * @param string $eventName The event name/class to listen for
     * @param callable $listener The listener callable
     * @param int $priority Priority (higher numbers = higher priority)
     */
    public function addListener(string $eventName, callable $listener, int $priority = 0): void;

    /**
     * Remove a listener for a specific event
     *
     * @param string $eventName The event name/class
     * @param callable $listener The listener to remove
     */
    public function removeListener(string $eventName, callable $listener): void;

    /**
     * Get all registered event names
     *
     * @return array<string> List of event names
     */
    public function getRegisteredEvents(): array;
}