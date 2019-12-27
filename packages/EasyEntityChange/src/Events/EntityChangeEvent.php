<?php
declare(strict_types=1);

namespace EonX\EasyEntityChange\Events;

use EonX\EasyEntityChange\Interfaces\EasyEntityChangeEventInterface;

/**
 * This event is fired with data about any entities
 * that have been updated, created or deleted where listeners can
 * pay attention to those events and handle reactions as required.
 */
final class EntityChangeEvent implements EasyEntityChangeEventInterface
{
    /**
     * Contains an array of ChangedEntity DTO objects that indicate any
     * changes that may have occurred during the flush process.
     *
     * @var \EonX\EasyEntityChange\DataTransferObjects\ChangedEntity[]
     */
    private $changes;

    /**
     * Constructor
     *
     * @param \EonX\EasyEntityChange\DataTransferObjects\ChangedEntity[] $changes
     */
    public function __construct(array $changes)
    {
        $this->changes = $changes;
    }

    /**
     * @return \EonX\EasyEntityChange\DataTransferObjects\ChangedEntity[]
     */
    public function getChanges(): array
    {
        return $this->changes;
    }
}
