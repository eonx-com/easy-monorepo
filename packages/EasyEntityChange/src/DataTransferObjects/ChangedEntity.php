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
     * @param mixed[] $ids
     *
     * @phpstan-param class-string $class
     */
    public function __construct(string $class, array $ids)
    {
        $this->class = $class;
        $this->ids = $ids;
    }

    /**
     * @phpstan-return class-string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return mixed[]
     */
    public function getIds(): array
    {
        return $this->ids;
    }
}
