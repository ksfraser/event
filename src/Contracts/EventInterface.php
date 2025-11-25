<?php
declare(strict_types=1);

namespace EventSystem\Contracts;

/**
 * PSR-14 Event Interface
 * Defines the basic structure for events
 */
interface EventInterface
{
    /**
     * Get event name
     */
    public function getName(): string;

    /**
     * Check if event propagation should stop
     */
    public function isPropagationStopped(): bool;

    /**
     * Stop event propagation
     */
    public function stopPropagation(): void;
}