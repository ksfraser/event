<?php
declare(strict_types=1);

namespace FA\Events;

use FA\Events\Event;

/**
 * Database Pre-Write Event
 * Fired before a transaction is written to the database
 */
class DatabasePreWriteEvent extends Event
{
    private $data;
    private int $transactionType;

    public function __construct(&$data, int $transactionType)
    {
        $this->data = &$data;
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

    public function setData($data): void
    {
        $this->data = $data;
    }
}