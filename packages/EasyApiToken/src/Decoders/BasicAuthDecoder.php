<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Decoders;

use EonX\EasyApiToken\Interfaces\ApiTokenInterface;
use EonX\EasyApiToken\Tokens\BasicAuth;

final class BasicAuthDecoder extends AbstractApiTokenDecoder
{
    public function __construct(?string $name = null)
    {
        parent::__construct($name ?? self::NAME_BASIC);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request|\Psr\Http\Message\ServerRequestInterface $request
     */
    public function decode($request): ?ApiTokenInterface
    {
        $authorization = $this->getHeaderWithoutPrefix('Authorization', 'Basic', $request);

        if ($authorization === null) {
            return null; // If Authorization doesn't start with Basic, return null
        }

        $original = $authorization;
        $authorization = \explode(':', (string)\base64_decode($authorization, true));

        if (empty(\trim($authorization[0] ?? '')) || empty(\trim($authorization[1] ?? ''))) {
            return null; // If Authorization doesn't contain a username AND a password, return null
        }

        return new BasicAuth(\trim($authorization[0]), \trim($authorization[1]), $original);
    }
}
