<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Decoders;

use EonX\EasyApiToken\Interfaces\ApiTokenInterface;
use EonX\EasyApiToken\Tokens\ApiKey;

/**
 * @deprecated since 2.4. Will be removed in 3.0. Use EonX\EasyApiToken\Decoders\ApiKeyDecoder instead.
 */
final class ApiKeyAsBasicAuthUsernameDecoder extends AbstractApiTokenDecoder
{
    public function __construct(?string $name = null)
    {
        @\trigger_error(
            \sprintf(
                'Using %s is deprecated since 2.4 and will be removed in 3.0. Use %s instead',
                self::class,
                ApiKeyDecoder::class
            ),
            \E_USER_DEPRECATED
        );

        parent::__construct($name ?? self::NAME_USER_APIKEY);
    }

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
