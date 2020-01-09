<?php
declare(strict_types=1);

namespace EonX\EasyEntityChange\DataTransferObjects;

/**
 * This DTO represents an entity that was deleted by Doctrine.
 */
final class DeletedEntity extends ChangedEntity
{
    /**
     * Stores an array of additional information that an application can add to the DTO before
     * the flush has finalised, so that information that may no longer be available can still be
     * processed by listener of the EntityChangeEvent.
     *
     * @var mixed[]
     */
    private $metadata;

    /**
     * Constructor
     *
     * @phpstan-param class-string $class
     *
     * @param string $class
     * @param mixed[] $ids
     * @param mixed[] $metadata
     */
    public function __construct(string $class, array $ids, array $metadata)
    {
        parent::__construct($class, $ids);

        $this->metadata = $metadata;
    }

    /**
     * @return mixed[]
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
