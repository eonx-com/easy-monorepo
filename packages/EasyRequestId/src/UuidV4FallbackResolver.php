<?php

declare(strict_types=1);

namespace EonX\EasyRequestId;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRequestId\Interfaces\FallbackResolverInterface;

final class UuidV4FallbackResolver implements FallbackResolverInterface
{
    public function __construct(
        private RandomGeneratorInterface $randomGenerator,
    ) {
    }

    public function fallbackCorrelationId(): string
    {
        return $this->randomGenerator->uuidV4();
    }

    public function fallbackRequestId(): string
    {
        return $this->randomGenerator->uuidV4();
    }
}
