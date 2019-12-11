<?php
declare(strict_types=1);

namespace EonX\EasyEntityChange\Events;

use EonX\EasyEntityChange\Interfaces\EasyEntityChangeEventInterface;

/**
 * This event is fired when deletes occurred inside a flush, and
 * is an event that allows listeners to convert data
 * about the deleted entities into values that can be asynchronously
 * dispatched to be handled by the queue workers.
 *
 * For example, a search index system will add details to each deleted
 * entity that includes the required "external id" to remove from search
 * that is not the same as the entity primary ids.
 */
final class EntityDeleteDataEvent implements EasyEntityChangeEventInterface
{
    /**
     * @var object[]
     */
    private $deletes;

    /**
     * Constructor.
     *
     * @param object[] $deletes
     */
    public function __construct(array $deletes)
    {
        $this->deletes = $deletes;
    }

    /**
     * @return object[]
     */
    public function getDeletes(): array
    {
        return $this->deletes;
    }
}
