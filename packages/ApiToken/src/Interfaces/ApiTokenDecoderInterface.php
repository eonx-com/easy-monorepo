<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface ApiTokenDecoderInterface
{
    /**
     * Decode API token for given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return null|\StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface
     */
    public function decode(ServerRequestInterface $request): ?ApiTokenInterface;
}
