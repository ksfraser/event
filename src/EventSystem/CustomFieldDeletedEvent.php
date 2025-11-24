<?php
declare(strict_types=1);

namespace FA\Events;

use FA\Events\Event;

/**
 * Custom Field Deleted Event
 * Fired when a custom field is deleted
 */
class CustomFieldDeletedEvent extends Event
{
    private int $fieldId;
    private string $entityType;
    private string $fieldName;

    public function __construct(int $fieldId, string $entityType, string $fieldName)
    {
        $this->fieldId = $fieldId;
        $this->entityType = $entityType;
        $this->fieldName = $fieldName;
    }

    public function getFieldId(): int
    {
        return $this->fieldId;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }
}