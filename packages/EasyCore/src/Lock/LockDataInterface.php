<?php

declare(strict_types=1);

namespace EonX\EasyCore\Lock;

/**
 * @deprecated Since 2.4.31. Will be remove in 3.0. Use eonx-com/easy-lock package instead.
 */
interface LockDataInterface
{
    public function getResource(): string;

    public function getTtl(): ?float;
}
