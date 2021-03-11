<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces\Batch;

interface BatchInstantiatorInterface
{
    /**
     * @param mixed[] $data
     */
    public function instantiateFromArray(array $data): BatchInterface;
}
