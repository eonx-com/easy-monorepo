<?php

declare(strict_types=1);

namespace EonX\EasyLock\Interfaces;

interface LockDataInterface
{
    public function getResource(): string;

    public function getTtl(): ?float;

    public function shouldRetry(): bool;
}
