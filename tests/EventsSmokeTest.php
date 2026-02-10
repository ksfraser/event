<?php
declare(strict_types=1);

namespace Ksfraser\EventSystem\Tests;

use Ksfraser\EventSystem\CustomFieldCreatedEvent;
use Ksfraser\EventSystem\CustomFieldDeletedEvent;
use Ksfraser\EventSystem\CustomFieldUpdatedEvent;
use Ksfraser\EventSystem\DatabasePostWriteEvent;
use Ksfraser\EventSystem\DatabasePreVoidEvent;
use Ksfraser\EventSystem\DatabasePreWriteEvent;
use Ksfraser\EventSystem\PluginActivatedEvent;
use Ksfraser\EventSystem\PluginDeactivatedEvent;
use Ksfraser\EventSystem\PluginInstalledEvent;
use Ksfraser\EventSystem\PluginUninstalledEvent;
use PHPUnit\Framework\TestCase;

final class EventsSmokeTest extends TestCase
{
    public function testEventDtosExposeData(): void
    {
        $created = new CustomFieldCreatedEvent(1, 'debtor', ['x' => 'y']);
        $this->assertSame(CustomFieldCreatedEvent::class, $created->getName());
        $this->assertFalse($created->isPropagationStopped());
        $created->stopPropagation();
        $this->assertTrue($created->isPropagationStopped());
        $this->assertSame(1, $created->getFieldId());
        $this->assertSame('debtor', $created->getEntityType());
        $this->assertSame(['x' => 'y'], $created->getFieldData());

        $deleted = new CustomFieldDeletedEvent(2, 'supplier', 'field');
        $this->assertSame(2, $deleted->getFieldId());
        $this->assertSame('supplier', $deleted->getEntityType());
        $this->assertSame('field', $deleted->getFieldName());

        $updated = new CustomFieldUpdatedEvent(3, 'stock', ['a' => 1]);
        $this->assertSame(3, $updated->getFieldId());
        $this->assertSame('stock', $updated->getEntityType());
        $this->assertSame(['a' => 1], $updated->getFieldData());

        $post = new DatabasePostWriteEvent(['k' => 'v'], 30);
        $this->assertSame(['k' => 'v'], $post->getData());
        $this->assertSame(30, $post->getTransactionType());

        $preVoid = new DatabasePreVoidEvent(10, 123);
        $this->assertSame(10, $preVoid->getTransactionType());
        $this->assertSame(123, $preVoid->getTransactionNumber());

        $data = ['foo' => 'bar'];
        $preWrite = new DatabasePreWriteEvent($data, 12);
        $this->assertSame(12, $preWrite->getTransactionType());
        $this->assertSame(['foo' => 'bar'], $preWrite->getData());
        $preWrite->setData(['baz' => 'qux']);
        $this->assertSame(['baz' => 'qux'], $preWrite->getData());

        $plugin = (object) ['name' => 'X'];
        $act = new PluginActivatedEvent('X', $plugin);
        $this->assertSame('X', $act->getPluginName());
        $this->assertSame($plugin, $act->getPlugin());

        $deact = new PluginDeactivatedEvent('X', $plugin);
        $this->assertSame('X', $deact->getPluginName());

        $inst = new PluginInstalledEvent('X', $plugin);
        $this->assertSame('X', $inst->getPluginName());

        $uninst = new PluginUninstalledEvent('X', $plugin);
        $this->assertSame('X', $uninst->getPluginName());
    }
}
