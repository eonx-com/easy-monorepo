<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests;

use OpenSSLAsymmetricKey;

abstract class AbstractJwtTokenTestCase extends AbstractTestCase
{
    protected function getOpenSslPrivateKey(): false|OpenSSLAsymmetricKey
    {
        return \openssl_pkey_get_private(\sprintf('file://%s', __DIR__ . '/keys/jwt-private.pem'));
    }

    protected function getOpenSslPublicKey(): false|OpenSSLAsymmetricKey
    {
        return \openssl_pkey_get_public(\sprintf('file://%s', __DIR__ . '/keys/jwt-public.pem'));
    }

    protected function isAlgoRs(string $algo): bool
    {
        return \str_starts_with(\strtolower($algo), 'rs');
    }
}
