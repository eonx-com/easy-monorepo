<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stub\Entity;

use EonX\EasySecurity\Common\Entity\ProviderInterface;

final readonly class ProviderStub implements ProviderInterface
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
