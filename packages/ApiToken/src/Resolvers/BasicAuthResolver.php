<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Resolvers;

use Psr\Http\Message\ServerRequestInterface;
use StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface;
use StepTheFkUp\ApiToken\Interfaces\ApiTokenResolverInterface;
use StepTheFkUp\ApiToken\Tokens\BasicAuthApiToken;
use StepTheFkUp\ApiToken\Traits\ApiTokenResolverTrait;

final class BasicAuthResolver implements ApiTokenResolverInterface
{
    use ApiTokenResolverTrait;

    /**
     * Resolve API token for given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return null|\StepTheFkUp\ApiToken\Interfaces\ApiTokenInterface
     */
    public function resolve(ServerRequestInterface $request): ?ApiTokenInterface
    {
        $authorization = $this->getHeaderWithoutPrefix('Authorization', 'Basic', $request);

        if ($authorization === null) {
            return null; // If Authorization doesn't start with Basic, return null
        }

        $authorization = \explode(':', \base64_decode($authorization));

        if (empty($authorization[0]) || empty($authorization[1])) {
            return null; // If Authorization doesn't contain a username AND a password, return null
        }

        return new BasicAuthApiToken(['username' => \trim($authorization[0]), 'password' => \trim($authorization[1])]);
    }
}