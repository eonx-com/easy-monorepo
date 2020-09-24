<?php

declare(strict_types=1);

namespace EonX\EasyRequestId;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRequestId\Interfaces\FallbackResolverInterface;

final class UuidV4FallbackResolver implements FallbackResolverInterface
{
    /**
     * @var \EonX\EasyRandom\Interfaces\RandomGeneratorInterface
     */
    private $random;

    public function __construct(RandomGeneratorInterface $random)
    {
        $this->random = $random;
    }

    public function fallbackCorrelationId(): string
    {
        return $this->random->uuidV4();
    }

    public function fallbackRequestId(): string
    {
        return $this->random->uuidV4();
    }
}
