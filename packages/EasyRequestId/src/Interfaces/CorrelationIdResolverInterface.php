<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Interfaces;

use Symfony\Component\HttpFoundation\Request;

interface CorrelationIdResolverInterface extends ResolverInterface
{
    public function getCorrelationId(Request $request): ?string;
}
