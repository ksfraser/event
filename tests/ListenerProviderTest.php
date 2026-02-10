<?php
declare(strict_types=1);

namespace Ksfraser\EventSystem\Tests;

use Ksfraser\EventSystem\Contracts\EventInterface;
use Ksfraser\EventSystem\ListenerProvider;
use PHPUnit\Framework\TestCase;

final class ListenerProviderTest extends TestCase
{
    public function testAddListenerOrdersByPriority(): void
    {
        $provider = new ListenerProvider();

        $calls = [];
        $low = function () use (&$calls) { $calls[] = 'low'; };
        $high = function () use (&$calls) { $calls[] = 'high'; };

        $provider->addListener('evt', $low, 0);
        $provider->addListener('evt', $high, 10);

        $event = new class implements EventInterface {
            public function getName(): string { return 'evt'; }
            public function isPropagationStopped(): bool { return false; }
            public function stopPropagation(): void {}
        };

        $listeners = iterator_to_array($provider->getListenersForEvent($event));
        $this->assertCount(2, $listeners);

        $listeners[0]($event);
        $listeners[1]($event);

        $this->assertSame(['high', 'low'], $calls);
    }

    public function testRemoveListenerStopsBeingReturned(): void
    {
        $provider = new ListenerProvider();

        $listener = function (): void {};
        $provider->addListener('evt', $listener);
        $provider->removeListener('evt', $listener);

        $event = new class implements EventInterface {
            public function getName(): string { return 'evt'; }
            public function isPropagationStopped(): bool { return false; }
            public function stopPropagation(): void {}
        };

        $listeners = iterator_to_array($provider->getListenersForEvent($event));
        $this->assertSame([], $listeners);
    }

    public function testGetRegisteredEventsReturnsKeys(): void
    {
        $provider = new ListenerProvider();
        $provider->addListener('evt1', function (): void {});
        $provider->addListener('evt2', function (): void {});

        $events = $provider->getRegisteredEvents();
        sort($events);
        $this->assertSame(['evt1', 'evt2'], $events);
    }
}
