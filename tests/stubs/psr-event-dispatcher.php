<?php
declare(strict_types=1);

namespace Psr\EventDispatcher;

if (!interface_exists(ListenerProviderInterface::class)) {
    interface ListenerProviderInterface
    {
        public function getListenersForEvent(object $event): iterable;
    }
}

if (!interface_exists(EventDispatcherInterface::class)) {
    interface EventDispatcherInterface
    {
        public function dispatch(object $event): object;
    }
}

if (!interface_exists(StoppableEventInterface::class)) {
    interface StoppableEventInterface
    {
        public function isPropagationStopped(): bool;
    }
}
