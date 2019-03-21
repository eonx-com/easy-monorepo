<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tests;

use StepTheFkUp\ApiToken\External\Auth0JwtDriver;
use StepTheFkUp\ApiToken\External\Interfaces\JwtDriverInterface;

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
        'aud' => 'my-identifier'
    ];

    /**
     * @var string[]
     */
    protected static $validAudiences = ['my-identifier'];

    /**
     * Create Auth0 JWT driver.
     *
     * @param null|string[] $validAudiences
     * @param null|string[] $authorizedIss
     * @param null|string|resource $key
     * @param null|string $audienceForEncode
     * @param null|string[] $allowedAlgos
     *
     * @return \StepTheFkUp\ApiToken\External\Interfaces\JwtDriverInterface
     */
    protected function createAuth0JwtDriver(
        ?array $validAudiences = null,
        ?array $authorizedIss = null,
        $key = null,
        ?string $audienceForEncode = null,
        ?array $allowedAlgos = null
    ): JwtDriverInterface {
        return new Auth0JwtDriver(
            $validAudiences ?? static::$validAudiences,
            $authorizedIss ?? static::$authorizedIss,
            $key ?? static::$key,
            $audienceForEncode,
            $allowedAlgos
        );
    }

    /**
     * Create JWT token for given algo.
     *
     * @return string
     */
    protected function createToken(): string
    {
        return $this->createAuth0JwtDriver(null, null, static::$key)->encode([]);
    }
}
