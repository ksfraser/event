<?php
declare(strict_types=1);

namespace Ksfraser\EventSystem;

use FA\Contracts\ExtendedListenerProviderInterface;
use Ksfraser\EventSystem\EventDispatcher;
use Ksfraser\EventSystem\ListenerProvider;

/**
 * Event Manager Service
 *
 * Provides a centralized interface for dispatching events and managing listeners.
 * Implements the Service Locator pattern for easy access throughout the application.
 */
class EventManager
{
    private static ?EventManager $instance = null;
    private \Psr\EventDispatcher\EventDispatcherInterface $dispatcher;
    private ExtendedListenerProviderInterface $listenerProvider;

    /**
     * Get the singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor for singleton pattern
     */
    private function __construct()
    {
        $this->listenerProvider = new ListenerProvider();
        $this->dispatcher = new EventDispatcher($this->listenerProvider);
    }

    /**
     * Dispatch an event
     *
     * @param object $event The event to dispatch
     * @return object The processed event
     */
    public function dispatch(object $event): object
    {
        return $this->dispatcher->dispatch($event);
    }

    /**
     * Register an event listener
     *
     * @param string $eventName The event name/class to listen for
     * @param callable $listener The listener callable
     * @param int $priority Priority (higher = executed first)
     */
    public function addListener(string $eventName, callable $listener, int $priority = 0): void
    {
        $this->listenerProvider->addListener($eventName, $listener, $priority);
    }

    /**
     * Remove an event listener
     *
     * @param string $eventName The event name/class
     * @param callable $listener The listener to remove
     */
    public function removeListener(string $eventName, callable $listener): void
    {
        $this->listenerProvider->removeListener($eventName, $listener);
    }

    /**
     * Check if there are listeners for an event
     *
     * @param string $eventName The event name
     * @return bool True if listeners exist
     */
    public function hasListeners(string $eventName): bool
    {
        // Create a dummy event object to check for listeners
        $dummyEvent = new class implements \FA\Contracts\EventInterface {
            private string $name;
            public function __construct() { $this->name = ''; }
            public function getName(): string { return $this->name; }
            public function isPropagationStopped(): bool { return false; }
            public function stopPropagation(): void {}
        };

        $listeners = $this->listenerProvider->getListenersForEvent($dummyEvent);
        return !empty($listeners);
    }

    /**
     * Get all registered event names
     *
     * @return array List of event names
     */
    public function getRegisteredEvents(): array
    {
        // This method would require extending the interface
        // For now, return empty array
        return [];
    }

    /**
     * Static convenience methods for global access
     */

    /**
     * Dispatch an event (static method)
     */
    public static function dispatchEvent(object $event): object
    {
        return self::getInstance()->dispatch($event);
    }

    /**
     * Register an event listener (static method)
     */
    public static function on(string $eventName, callable $listener, int $priority = 0): void
    {
        self::getInstance()->addListener($eventName, $listener, $priority);
    }

    /**
     * Remove an event listener (static method)
     */
    public static function off(string $eventName, callable $listener): void
    {
        self::getInstance()->removeListener($eventName, $listener);
    }
}