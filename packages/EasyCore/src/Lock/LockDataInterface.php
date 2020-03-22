<?php

declare(strict_types=1);

namespace EonX\EasyCore\Lock;

interface LockDataInterface
{
    public function getResource(): string;

    public function getTtl(): ?float;
}
