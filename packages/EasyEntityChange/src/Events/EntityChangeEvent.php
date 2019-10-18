<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyEntityChange\Events;

use LoyaltyCorp\EasyEntityChange\Interfaces\EasyEntityChangeEventInterface;

/**
 * This event is fired with data about any entities
 * that have been updated, created or deleted where listeners can
 * pay attention to those events and handle reactions as required.
 */
final class EntityChangeEvent implements EasyEntityChangeEventInterface
{
    /**
     * This array contains any data that any subscribers to EntityDeleteDataEvent
     * returned. If you need data for a listener in this array, listen to the
     * above event and return the data you require.
     *
     * @var string[][]
     */
    private $deletes;

    /**
     * Stores a multi dimensional array of entity class name with all
     * ids of that class that were updated or created.
     *
     * The structure of this array is:
     * [
     *   ClassName => [
     *     spl_object_hash($object) => $objectId
     *   ]
     * ]
     *
     * Where $objectId is the value returned by Doctrine's
     * ClassMetadata#getIdentifierValues() which is an array of ids (containing
     * a single value for an entity unless it is a composite primary key)
     *
     * Pass the ids to EntityManager#find
     *
     * @var mixed[][]
     */
    private $updates;

    /**
     * Constructor.
     *
     * @param string[][] $deletes
     * @param mixed[][] $updates
     */
    public function __construct(array $deletes, array $updates)
    {
        $this->deletes = $deletes;
        $this->updates = $updates;
    }

    /**
     * Returns data about deleted entities.
     *
     * @return string[][]
     */
    public function getDeletes(): array
    {
        return $this->deletes;
    }

    /**
     * Returns a multidimensional array of entity ids that have been
     * updated or created.
     *
     * @return mixed[][]
     */
    public function getUpdates(): array
    {
        return $this->updates;
    }
}
