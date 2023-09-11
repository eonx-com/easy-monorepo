<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Interfaces;

interface FallbackResolverInterface
{
    public function fallbackCorrelationId(): string;

    public function fallbackRequestId(): string;
}
