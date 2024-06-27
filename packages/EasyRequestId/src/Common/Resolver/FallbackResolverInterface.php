<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Common\Resolver;

interface FallbackResolverInterface
{
    public function fallbackCorrelationId(): string;

    public function fallbackRequestId(): string;
}
