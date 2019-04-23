<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPagination\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface StartSizeDataResolverInterface
{
    /**
     * Resolve page pagination data for given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \LoyaltyCorp\EasyPagination\Interfaces\StartSizeDataInterface
     */
    public function resolve(ServerRequestInterface $request): StartSizeDataInterface;
}

\class_alias(
    StartSizeDataResolverInterface::class,
    'StepTheFkUp\EasyPagination\Interfaces\StartSizeDataResolverInterface',
    false
);
