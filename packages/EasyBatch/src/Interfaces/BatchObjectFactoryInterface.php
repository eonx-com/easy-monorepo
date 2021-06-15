<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchObjectFactoryInterface
{
    /**
     * @param mixed[] $data
     */
    public function createFromArray(array $data): BatchObjectInterface;
}
