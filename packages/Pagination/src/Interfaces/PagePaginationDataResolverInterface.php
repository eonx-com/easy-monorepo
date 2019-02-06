<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface PagePaginationDataResolverInterface
{
    /**
     * Resolve page pagination data for given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \StepTheFkUp\Pagination\Interfaces\PagePaginationDataInterface
     */
    public function resolve(ServerRequestInterface $request): PagePaginationDataInterface;
}