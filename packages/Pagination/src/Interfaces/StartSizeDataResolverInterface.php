<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface StartSizeDataResolverInterface
{
    /**
     * Resolve page pagination data for given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \StepTheFkUp\Pagination\Interfaces\StartSizeDataInterface
     */
    public function resolve(ServerRequestInterface $request): StartSizeDataInterface;
}
