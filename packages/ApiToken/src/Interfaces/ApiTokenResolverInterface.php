<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface ApiTokenResolverInterface
{
    /**
     * Resolve API token for given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return null|\StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface
     */
    public function resolve(ServerRequestInterface $request): ?ApiTokenInterface;
}