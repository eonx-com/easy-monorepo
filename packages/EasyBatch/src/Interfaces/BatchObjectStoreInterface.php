<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchObjectStoreInterface
{
    /**
     * @param int|string $id
     *
     * @return null|mixed[]
     */
    public function find($id): ?array;

    /**
     * @param int|string $id
     */
    public function has($id): bool;

    /**
     * @param mixed[] $data
     */
    public function persist(array $data): void;

    /**
     * @param int|string $id
     * @param mixed[] $data
     */
    public function update($id, array $data): void;
}
