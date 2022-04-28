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
    public function delete(array|object $object): void;

    public function find(int|string $identifier): ?object;

    /**
     * @param object|object[] $object
     */
    public function save(array|object $object): void;
}
