<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stub\Entity;

use EonX\EasySecurity\SymfonySecurity\Voter\ProviderRestrictedInterface;

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
