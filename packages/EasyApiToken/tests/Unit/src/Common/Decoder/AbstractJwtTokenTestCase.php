<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Unit\Common\Decoder;

use EonX\EasyApiToken\Tests\Unit\AbstractUnitTestCase;
use OpenSSLAsymmetricKey;

abstract class AbstractJwtTokenTestCase extends AbstractUnitTestCase
{
    protected function getOpenSslPrivateKey(): false|OpenSSLAsymmetricKey
    {
        return \openssl_pkey_get_private(\sprintf('file://%s', __DIR__ . '/../../../../Fixture/keys/jwt_private.pem'));
    }

    protected function getOpenSslPublicKey(): false|OpenSSLAsymmetricKey
    {
        return \openssl_pkey_get_public(\sprintf('file://%s', __DIR__ . '/../../../../Fixture/keys/jwt_public.pem'));
    }

    protected function isAlgoRs(string $algo): bool
    {
        return \str_starts_with(\strtolower($algo), 'rs');
    }
}
