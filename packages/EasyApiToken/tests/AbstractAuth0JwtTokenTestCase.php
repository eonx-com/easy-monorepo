<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tests;

use EonX\EasyApiToken\External\Auth0JwtDriver;
use EonX\EasyApiToken\External\Interfaces\JwtDriverInterface;

abstract class AbstractAuth0JwtTokenTestCase extends AbstractJwtTokenTestCase
{
    /**
     * @var string[]
     */
    protected static array $authorizedIss = ['http://localhost'];

    protected static string $key = 'key';

    /**
     * @var mixed[]
     */
    protected static array $tokenPayload = [
        'scopes' => [],
        'aud' => 'my-identifier',
    ];

    /**
     * @var string[]
     */
    protected static array $validAudiences = ['my-identifier'];

    /**
     * @param string[]|null $validAudiences
     * @param string[]|null $authorizedIss
     * @param string[]|null $allowedAlgos
     */
    protected function createAuth0JwtDriver(
        ?array $validAudiences = null,
        ?array $authorizedIss = null,
        ?string $key = null,
        ?string $audienceForEncode = null,
        ?array $allowedAlgos = null,
    ): JwtDriverInterface {
        return new Auth0JwtDriver(
            $validAudiences ?? static::$validAudiences,
            $authorizedIss ?? static::$authorizedIss,
            'example.com',
            $key ?? static::$key,
            $audienceForEncode,
            $allowedAlgos
        );
    }

    protected function createToken(): string
    {
        return $this->createAuth0JwtDriver(null, null, static::$key)->encode([]);
    }
}
