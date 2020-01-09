<?php
declare(strict_types=1);

namespace EonX\EasyEntityChange\DataTransferObjects;

/**
 * This DTO represents a single entity which has changed. Subclasses define the operation
 * that occurred on the entity.
 */
abstract class ChangedEntity
{
    /**
     * @phpstan-var class-string
     *
     * @var string
     */
    private $class;

    /**
     * @var mixed[]
     */
    private $ids;

    /**
     * Constructor
     *
     * @phpstan-param class-string $class
     *
     * @param string $class
     * @param mixed[] $ids
     */
    public function __construct(string $class, array $ids)
    {
        $this->class = $class;
        $this->ids = $ids;
    }

    /**
     * Returns the class of the entity who has been changed.
     *
     * @phpstan-return class-string
     *
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Returns an array of id properties returned by ClassMetadata.
     *
     * @return mixed[]
     */
    public function getIds(): array
    {
        return $this->ids;
    }
}
