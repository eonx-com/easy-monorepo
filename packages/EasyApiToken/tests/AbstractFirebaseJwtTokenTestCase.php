<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tests;

use EonX\EasyApiToken\External\FirebaseJwtDriver;
use EonX\EasyApiToken\External\Interfaces\JwtDriverInterface;
use OpenSSLAsymmetricKey;

abstract class AbstractFirebaseJwtTokenTestCase extends AbstractJwtTokenTestCase
{
    /**
     * @var string[]
     */
    protected static array $algos = ['HS256', 'HS512', 'HS384', 'RS256', 'RS384', 'RS512'];

    protected static string $defaultAlgo = 'HS256';

    protected static string $key = 'key';

    /**
     * @var mixed[]
     */
    protected static array $tokenPayload = [
        'iss' => 'stepthefkup.com',
        'aud' => 'stepthefkup.com.au',
        'iat' => 1549340373,
        'nbf' => 1549340373,
    ];

    protected function createFirebaseJwtDriver(
        ?string $algo = null,
        OpenSSLAsymmetricKey|string|null $publicKey = null,
        OpenSSLAsymmetricKey|string|null $privateKey = null,
        ?int $leeway = null,
    ): JwtDriverInterface {
        return new FirebaseJwtDriver(
            $algo ?? static::$defaultAlgo,
            $publicKey ?? static::$key,
            $privateKey ?? static::$key,
            $leeway
        );
    }

    protected function createToken(?string $algo = null): string
    {
        $key = static::$key;

        if ($algo !== null && $this->isAlgoRs($algo)) {
            $key = $this->getOpenSslPrivateKey();
            $key = $key === false ? null : $key;
        }

        return $this->createFirebaseJwtDriver($algo, null, $key)
            ->encode(static::$tokenPayload);
    }
}
