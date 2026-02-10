<?php
declare(strict_types=1);

namespace Ksfraser\EventSystem\Tests;

use Ksfraser\EventSystem\EventDispatcher;
use Ksfraser\EventSystem\ListenerProvider;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\StoppableEventInterface;

final class EventDispatcherTest extends TestCase
{
    public function testDispatchStopsPropagationWhenEventStops(): void
    {
        $provider = new ListenerProvider();
        $dispatcher = new EventDispatcher($provider);

        $calls = [];

        $provider->addListener('evt', function (object $event) use (&$calls): void {
            $calls[] = 'first';
            if ($event instanceof StoppableEventInterface) {
                $event->stopPropagation();
            }
        }, 10);
        $provider->addListener('evt', function () use (&$calls): void {
            $calls[] = 'second';
        }, 0);

        $event = new class implements StoppableEventInterface {
            private bool $stopped = false;
            public function isPropagationStopped(): bool { return $this->stopped; }
            public function stopPropagation(): void { $this->stopped = true; }
        };

        // ListenerProvider uses get_class for non-EventInterface events.
        $provider->addListener(get_class($event), function (object $ev) use (&$calls): void {
            $calls[] = 'class-first';
            if ($ev instanceof StoppableEventInterface) {
                $ev->stopPropagation();
            }
        }, 10);
        $provider->addListener(get_class($event), function () use (&$calls): void {
            $calls[] = 'class-second';
        }, 0);

        $dispatcher->dispatch($event);
        $this->assertSame(['class-first'], $calls);
    }
}
