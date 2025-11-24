<?php
declare(strict_types=1);

namespace FA\Events;

use FA\Events\Event;

/**
 * Database Pre-Void Event
 * Fired before a transaction is voided
 */
class DatabasePreVoidEvent extends Event
{
    private int $transactionType;
    private $transactionNumber;

    public function __construct(int $transactionType, $transactionNumber)
    {
        $this->transactionType = $transactionType;
        $this->transactionNumber = $transactionNumber;
    }

    public function getTransactionType(): int
    {
        return $this->transactionType;
    }

    public function getTransactionNumber()
    {
        return $this->transactionNumber;
    }
}