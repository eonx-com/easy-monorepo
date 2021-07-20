<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

use Symfony\Component\HttpFoundation\Request;

/**
 * @deprecated since 3.2, will be removed in 4.0.
 */
interface StartSizeDataResolverInterface
{
    public function resolve(Request $request): StartSizeDataInterface;
}
