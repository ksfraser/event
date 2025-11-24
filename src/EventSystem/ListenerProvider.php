<?php
declare(strict_types=1);

namespace Ksfraser\EventSystem;

use FA\Contracts\ExtendedListenerProviderInterface;

/**
 * Listener Provider for managing event listeners
 * Implements PSR-14 ListenerProviderInterface
 */
class ListenerProvider implements ExtendedListenerProviderInterface
{
    /**
     * @var array<string, array<callable>> Listeners indexed by event name
     */
    private array $listeners = [];

    /**
     * Register a listener for a specific event
     *
     * @param string $eventName The event name/class
     * @param callable $listener The listener callable
     * @param int $priority Priority (higher numbers = higher priority)
     */
    public function addListener(string $eventName, callable $listener, int $priority = 0): void
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }

        // Insert listener at the correct priority position
        $this->listeners[$eventName][] = [
            'listener' => $listener,
            'priority' => $priority
        ];

        // Sort by priority (higher priority first)
        usort($this->listeners[$eventName], function($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });
    }

    /**
     * Remove a listener for a specific event
     *
     * @param string $eventName The event name/class
     * @param callable $listener The listener to remove
     */
    public function removeListener(string $eventName, callable $listener): void
    {
        if (!isset($this->listeners[$eventName])) {
            return;
        }

        $this->listeners[$eventName] = array_filter(
            $this->listeners[$eventName],
            function($listenerData) use ($listener) {
                return $listenerData['listener'] !== $listener;
            }
        );
    }

    /**
     * Get all listeners for a given event
     *
     * @param object $event The event to get listeners for
     * @return iterable List of listener callables
     */
    public function getListenersForEvent(object $event): iterable
    {
        $eventName = $event instanceof \FA\Contracts\EventInterface
            ? $event->getName()
            : get_class($event);

        if (!isset($this->listeners[$eventName])) {
            return [];
        }

        return array_map(
            function($listenerData) {
                return $listenerData['listener'];
            },
            $this->listeners[$eventName]
        );
    }

    /**
     * Get all registered event names
     *
     * @return array<string> List of event names
     */
    public function getRegisteredEvents(): array
    {
        return array_keys($this->listeners);
    }
}