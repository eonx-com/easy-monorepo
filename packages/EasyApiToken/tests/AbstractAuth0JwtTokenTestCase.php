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
    protected static $authorizedIss = ['http://localhost'];

    /**
     * @var string
     */
    protected static $key = 'key';

    /**
     * @var mixed[]
     */
    protected static $tokenPayload = [
        'scopes' => [],
        'aud' => 'my-identifier',
    ];

    /**
     * @var string[]
     */
    protected static $validAudiences = ['my-identifier'];

    /**
     * @param null|string[] $validAudiences
     * @param null|string[] $authorizedIss
     * @param null|string $key
     * @param null|string[] $allowedAlgos
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
