<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface StartSizeDataResolverInterface
{
    /**
     * Resolve page pagination data for given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \EonX\EasyPagination\Interfaces\StartSizeDataInterface
     */
    public function resolve(ServerRequestInterface $request): StartSizeDataInterface;
}
