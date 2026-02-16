<?php
declare(strict_types=1);

namespace Ksfraser\Event;

use Ksfraser\Event\Contracts\EventInterface;
use Ksfraser\Event\Contracts\ExtendedListenerProviderInterface;

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
        $eventName = $event instanceof EventInterface
            ? $event->getName()
            : get_class($event);

        $relevantListeners = [];
        
        // 1. Exact match listeners
        if (isset($this->listeners[$eventName])) {
             foreach ($this->listeners[$eventName] as $listenerData) {
                 $relevantListeners[] = $listenerData;
             }
        }

        // 2. Single Wildcard (*) listeners
        if (isset($this->listeners['*'])) {
             foreach ($this->listeners['*'] as $listenerData) {
                 $relevantListeners[] = $listenerData;
             }
        }

        // 3. Double Wildcard (**) listeners
        if (isset($this->listeners['**'])) {
             foreach ($this->listeners['**'] as $listenerData) {
                 $relevantListeners[] = $listenerData;
             }
        }

        // Sort combined list by priority
        usort($relevantListeners, function($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });

        // Return just the callables
        return array_map(
            function($listenerData) {
                return $listenerData['listener'];
            },
            $relevantListeners
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
