<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests;

use EoneoPay\Utils\Str;
use EonX\EasyApiToken\External\Interfaces\JwtDriverInterface;
use EonX\EasyApiToken\Interfaces\Tokens\Factories\JwtEasyApiTokenFactoryInterface;
use EonX\EasyApiToken\Tokens\Factories\JwtEasyApiTokenFactory;

abstract class AbstractJwtTokenTestCase extends AbstractTestCase
{
    /**
     * Create JwtEasyApiTokenFactory using Firebase JWT driver.
     *
     * @param \EonX\EasyApiToken\External\Interfaces\JwtDriverInterface $jwtDriver
     *
     * @return \EonX\EasyApiToken\Tokens\Factories\JwtEasyApiTokenFactory
     */
    protected function createJwtEasyApiTokenFactory(JwtDriverInterface $jwtDriver): JwtEasyApiTokenFactoryInterface
    {
        return new JwtEasyApiTokenFactory($jwtDriver);
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
     * @return bool|resource
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
