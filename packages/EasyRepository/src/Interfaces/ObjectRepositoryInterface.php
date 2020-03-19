<?php

declare(strict_types=1);

namespace EonX\EasyRepository\Interfaces;

interface ObjectRepositoryInterface
{
    /**
     * @return object[]
     */
    public function all(): array;

    /**
     * @param object|object[] $object
     */
    public function delete($object): void;

    /**
     * @param int|string $identifier
     *
     * @return null|object
     */
    public function find($identifier);

    /**
     * @param object|object[] $object The object or list of objects to save
     */
    public function save($object): void;
}
