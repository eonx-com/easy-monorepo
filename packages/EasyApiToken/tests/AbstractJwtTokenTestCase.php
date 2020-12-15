<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tests;

use Nette\Utils\Strings;

abstract class AbstractJwtTokenTestCase extends AbstractTestCase
{
    /**
     * @return false|resource
     */
    protected function getOpenSslPrivateKey()
    {
        return \openssl_pkey_get_private(\sprintf('file://%s', __DIR__ . '/keys/jwt-private.pem'));
    }

    /**
     * @return false|resource
     */
    protected function getOpenSslPublicKey()
    {
        return \openssl_pkey_get_public(\sprintf('file://%s', __DIR__ . '/keys/jwt-public.pem'));
    }

    protected function isAlgoRs(string $algo): bool
    {
        return Strings::startsWith(\strtolower($algo), 'rs');
    }
}
