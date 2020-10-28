<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Interfaces;

use Symfony\Component\HttpFoundation\Request;

interface RequestIdResolverInterface extends ResolverInterface
{
    public function getRequestId(Request $request): ?string;
}
