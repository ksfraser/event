<?php
declare(strict_types=1);

namespace Ksfraser\EventSystem\Tests;

use Ksfraser\EventSystem\EventManager;
use PHPUnit\Framework\TestCase;

final class EventManagerTest extends TestCase
{
    public function testSingletonReturnsSameInstance(): void
    {
        $a = EventManager::getInstance();
        $b = EventManager::getInstance();
        $this->assertSame($a, $b);
    }

    public function testStaticConvenienceMethodsDoNotError(): void
    {
        $listener = function (): void {};

        // These are smoke tests: API should be callable.
        EventManager::on('evt', $listener);
        EventManager::off('evt', $listener);

        $event = new class {};
        $returned = EventManager::dispatchEvent($event);
        $this->assertSame($event, $returned);
    }

    public function testHasListenersReflectsDummyEventNameBehavior(): void
    {
        $mgr = EventManager::getInstance();

        $called = false;
        $listener = function () use (&$called): void { $called = true; };

        $mgr->addListener('evt', $listener);
        $this->assertTrue($mgr->hasListeners('evt'));

        $mgr->removeListener('evt', $listener);
        $this->assertFalse($mgr->hasListeners('evt'));
    }
}
