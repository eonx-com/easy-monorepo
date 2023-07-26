<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasySecurity\Interfaces\ProviderInterface;

final class ProviderInterfaceStub implements ProviderInterface
{
    public function __construct(
        private int|string $providerId,
    ) {
    }

    public function getUniqueId(): int|string
    {
        return $this->providerId;
    }
}
