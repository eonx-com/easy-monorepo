<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

use Symfony\Component\HttpFoundation\Request;

interface StartSizeDataResolverInterface
{
    public function resolve(Request $request): StartSizeDataInterface;
}
