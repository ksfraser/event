<?php
declare(strict_types=1);

namespace FA\Events;

use FA\Events\Event;

/**
 * Custom Field Updated Event
 * Fired when a custom field is updated
 */
class CustomFieldUpdatedEvent extends Event
{
    private int $fieldId;
    private string $entityType;
    private array $fieldData;

    public function __construct(int $fieldId, string $entityType, array $fieldData)
    {
        $this->fieldId = $fieldId;
        $this->entityType = $entityType;
        $this->fieldData = $fieldData;
    }

    public function getFieldId(): int
    {
        return $this->fieldId;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function getFieldData(): array
    {
        return $this->fieldData;
    }
}