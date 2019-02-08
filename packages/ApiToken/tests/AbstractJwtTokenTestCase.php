<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tests;

use EoneoPay\Utils\Str;
use StepTheFkUp\ApiToken\External\FirebaseJwtDriver;
use StepTheFkUp\ApiToken\External\Interfaces\JwtDriverInterface;
use StepTheFkUp\ApiToken\Tokens\Factories\JwtApiTokenFactory;

abstract class AbstractJwtTokenTestCase extends AbstractTestCase
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
        'RS512'
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
        'nbf' => 1549340373
    ];

    /**
     * Create Firebase JWT driver.
     *
     * @param null|string $algo
     * @param null|string|resource $publicKey
     * @param null|string|resource $privateKey
     * @param null|string[] $allowedAlgos
     * @param null|int $leeway
     *
     * @return \StepTheFkUp\ApiToken\External\Interfaces\JwtDriverInterface
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

    /**
     * Create JwtApiTokenFactory using Firebase JWT driver.
     *
     * @param null|string $algo
     * @param null|string|resource $publicKey
     * @param null|string|resource $privateKey
     * @param null|string[] $allowedAlgos
     * @param null|int $leeway
     *
     * @return \StepTheFkUp\ApiToken\Tokens\Factories\JwtApiTokenFactory
     */
    protected function createJwtApiTokenFactory(
        ?string $algo = null,
        $publicKey = null,
        $privateKey = null,
        ?array $allowedAlgos = null,
        ?int $leeway = null
    ): JwtApiTokenFactory {
        return new JwtApiTokenFactory($this->createFirebaseJwtDriver(
            $algo,
            $publicKey,
            $privateKey,
            $allowedAlgos,
            $leeway
        ));
    }

    /**
     * Get the openssl private key for algorithms using it.
     *
     * @return bool|resource
     */
    protected function getOpenSslPrivateKey()
    {
        return \openssl_pkey_get_private(\sprintf('file://%s', __DIR__ . '/keys/jwt-private.pem'));
    }

    /**
     * Get the openssl public key for algorithms using it.
     *
     * @return resource
     */
    protected function getOpenSslPublicKey()
    {
        return \openssl_pkey_get_public(\sprintf('file://%s', __DIR__ . '/keys/jwt-public.pem'));
    }

    /**
     * Check if given algo starts with RS.
     *
     * @param string $algo
     *
     * @return bool
     */
    protected function isAlgoRs(string $algo): bool
    {
        return (new Str())->startsWith(\strtolower($algo), 'rs');
    }
}