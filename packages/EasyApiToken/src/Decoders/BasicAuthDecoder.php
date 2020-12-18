<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Decoders;

use EonX\EasyApiToken\Interfaces\ApiTokenInterface;
use EonX\EasyApiToken\Tokens\BasicAuth;
use Symfony\Component\HttpFoundation\Request;

final class BasicAuthDecoder extends AbstractApiTokenDecoder
{
    public function decode(Request $request): ?ApiTokenInterface
    {
        $authorization = $this->getHeaderWithoutPrefix('Authorization', 'Basic', $request);

        if ($authorization === null) {
            // If Authorization doesn't start with Basic, return null
            return null;
        }

        $original = $authorization;
        $authorization = \explode(':', (string)\base64_decode($authorization, true));

        if (empty(\trim($authorization[0] ?? '')) || empty(\trim($authorization[1] ?? ''))) {
            // If Authorization doesn't contain a username AND a password, return null
            return null;
        }

        return new BasicAuth(\trim($authorization[0]), \trim($authorization[1]), $original);
    }
}
