<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tests;

use EonX\EasyApiToken\External\FirebaseJwtDriver;
use EonX\EasyApiToken\External\Interfaces\JwtDriverInterface;

abstract class AbstractFirebaseJwtTokenTestCase extends AbstractJwtTokenTestCase
{
    /**
     * @var string[]
     */
    protected static $algos = [
        'HS256',
        'HS512',
        'HS384',
        'RS256',
        'RS384',
        'RS512',
    ];

    /**
     * @var string
     */
    protected static $defaultAlgo = 'HS256';

    /**
     * @var string
     */
    protected static $key = 'key';

    /**
     * @var mixed[]
     */
    protected static $tokenPayload = [
        'iss' => 'stepthefkup.com',
        'aud' => 'stepthefkup.com.au',
        'iat' => 1549340373,
        'nbf' => 1549340373,
    ];

    /**
     * @param null|string|resource $publicKey
     * @param null|string|resource $privateKey
     * @param null|string[] $allowedAlgos
     */
    protected function createFirebaseJwtDriver(
        ?string $algo = null,
        $publicKey = null,
        $privateKey = null,
        ?array $allowedAlgos = null,
        ?int $leeway = null
    ): JwtDriverInterface {
        return new FirebaseJwtDriver(
            $algo ?? static::$defaultAlgo,
            $publicKey ?? static::$key,
            $privateKey ?? static::$key,
            $allowedAlgos,
            $leeway
        );
    }

    protected function createToken(?string $algo = null): string
    {
        $key = static::$key;

        if ($algo !== null && $this->isAlgoRs($algo)) {
            $key = $this->getOpenSslPrivateKey();
        }

        /** @var resource|string|null $key */

        return $this->createFirebaseJwtDriver($algo, null, $key)->encode(static::$tokenPayload);
    }
}
