<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchObjectTransformerInterface
{
    public function instantiateForClass(?string $class = null): BatchObjectInterface;

    public function transformToArray(BatchObjectInterface $batchObject): array;

    public function transformToObject(array $data): BatchObjectInterface;
}
