<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

interface StartSizeDataResolverInterface
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request|\Psr\Http\Message\ServerRequestInterface $request
     */
    public function resolve($request): StartSizeDataInterface;
}
