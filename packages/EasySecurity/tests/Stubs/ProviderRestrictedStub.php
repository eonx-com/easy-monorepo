<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasySecurity\Interfaces\ProviderRestrictedInterface;

final class ProviderRestrictedStub implements ProviderRestrictedInterface
{
    public function __construct(
        private int|string $providerId,
    ) {
    }

    public function getRestrictedProviderUniqueId(): int|string
    {
        return $this->providerId;
    }
}
