<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Unit\Common\Decoder;

use EonX\EasyApiToken\Common\Driver\JwtDriverInterface;
use EonX\EasyApiToken\Firebase\Driver\FirebaseJwtDriver;
use OpenSSLAsymmetricKey;

abstract class AbstractFirebaseJwtTokenTestCase extends AbstractJwtTokenTestCase
{
    /**
     * @var string[]
     */
    protected static array $algos = ['HS256', 'HS512', 'HS384', 'RS256', 'RS384', 'RS512'];

    protected static string $defaultAlgo = 'HS256';

    protected static string $key = 'key';

    protected static array $tokenPayload = [
        'iss' => 'stepthefkup.com',
        'aud' => 'stepthefkup.com.au',
        'iat' => 1_549_340_373,
        'nbf' => 1_549_340_373,
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
