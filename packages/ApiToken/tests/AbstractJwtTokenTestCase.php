<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tests;

use EoneoPay\Utils\Str;
use StepTheFkUp\ApiToken\External\Interfaces\JwtDriverInterface;
use StepTheFkUp\ApiToken\Interfaces\Tokens\Factories\JwtApiTokenFactoryInterface;
use StepTheFkUp\ApiToken\Tokens\Factories\JwtApiTokenFactory;

abstract class AbstractJwtTokenTestCase extends AbstractTestCase
{
    /**
     * Create JwtApiTokenFactory using Firebase JWT driver.
     *
     * @param \StepTheFkUp\ApiToken\External\Interfaces\JwtDriverInterface $jwtDriver
     *
     * @return \StepTheFkUp\ApiToken\Tokens\Factories\JwtApiTokenFactory
     */
    protected function createJwtApiTokenFactory(JwtDriverInterface $jwtDriver): JwtApiTokenFactoryInterface
    {
        return new JwtApiTokenFactory($jwtDriver);
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
