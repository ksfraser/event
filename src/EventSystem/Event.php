<?php
declare(strict_types=1);

namespace Ksfraser\EventSystem;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Base Event class implementing PSR-14 StoppableEventInterface
 * All FA events should extend this class
 */
abstract class Event implements StoppableEventInterface
{
    private bool $propagationStopped = false;

    /**
     * Get the event name (defaults to class name)
     */
    public function getName(): string
    {
        return static::class;
    }

    /**
     * Check if event propagation should stop
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * Stop event propagation
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }
}