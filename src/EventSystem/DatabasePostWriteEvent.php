<?php
declare(strict_types=1);

namespace FA\Events;

use FA\Events\Event;

/**
 * Database Post-Write Event
 * Fired after a transaction has been written to the database
 */
class DatabasePostWriteEvent extends Event
{
    private $data;
    private int $transactionType;

    public function __construct($data, int $transactionType)
    {
        $this->data = $data;
        $this->transactionType = $transactionType;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getTransactionType(): int
    {
        return $this->transactionType;
    }
}