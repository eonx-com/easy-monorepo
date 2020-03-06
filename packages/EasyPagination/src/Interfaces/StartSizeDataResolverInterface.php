<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface StartSizeDataResolverInterface
{
    public function resolve(ServerRequestInterface $request): StartSizeDataInterface;
}
