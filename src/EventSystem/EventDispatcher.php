<?php
declare(strict_types=1);

namespace Ksfraser\EventSystem;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * PSR-14 Compatible Event Dispatcher
 * Dispatches events to registered listeners
 */
class EventDispatcher implements EventDispatcherInterface
{
    private ListenerProviderInterface $listenerProvider;

    public function __construct(ListenerProviderInterface $listenerProvider)
    {
        $this->listenerProvider = $listenerProvider;
    }

    /**
     * Dispatch an event to all registered listeners
     *
     * @param object $event The event to dispatch
     * @return object The event after processing
     */
    public function dispatch(object $event): object
    {
        $listeners = $this->listenerProvider->getListenersForEvent($event);

        foreach ($listeners as $listener) {
            if ($event instanceof \Psr\EventDispatcher\StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }

            $listener($event);
        }

        return $event;
    }
}