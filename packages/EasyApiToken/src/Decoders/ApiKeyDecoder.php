<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Decoders;

use EonX\EasyApiToken\Interfaces\ApiTokenInterface;
use EonX\EasyApiToken\Tokens\ApiKey;

final class ApiKeyDecoder extends AbstractApiTokenDecoder
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request|\Psr\Http\Message\ServerRequestInterface $request
     */
    public function decode($request): ?ApiTokenInterface
    {
        $authorization = $this->getHeaderWithoutPrefix('Authorization', 'Basic', $request);

        if ($authorization === null) {
            // If Authorization doesn't start with Basic, return null
            return null;
        }

        $authorization = \explode(':', (string)\base64_decode($authorization, true));

        if (empty(\trim($authorization[0] ?? '')) === true || empty(\trim($authorization[1] ?? '')) === false) {
            // If Authorization doesn't contain ONLY a username, return null
            return null;
        }

        return new ApiKey(\trim($authorization[0]));
    }
}
