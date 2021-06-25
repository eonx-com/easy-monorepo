<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchObjectTransformerInterface
{
    public function instantiateForClass(?string $class = null): BatchObjectInterface;

    /**
     * @return mixed[]
     */
    public function transformToArray(BatchObjectInterface $batchObject): array;

    /**
     * @param mixed[] $data
     */
    public function transformToObject(array $data): BatchObjectInterface;
}
