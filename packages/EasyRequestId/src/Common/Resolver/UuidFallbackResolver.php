<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Common\Resolver;

use EonX\EasyRandom\Generator\RandomGeneratorInterface;

final class UuidFallbackResolver implements FallbackResolverInterface
{
    public function __construct(
        private RandomGeneratorInterface $randomGenerator,
    ) {
    }

    public function fallbackCorrelationId(): string
    {
        return $this->randomGenerator->uuid();
    }

    public function fallbackRequestId(): string
    {
        return $this->randomGenerator->uuid();
    }
}
