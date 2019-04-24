<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Tests\Implementations\Auth0;

use Auth0\SDK\JWTVerifier;
use LoyaltyCorp\EasyIdentity\Implementations\Auth0\Config;
use LoyaltyCorp\EasyIdentity\Implementations\Auth0\TokenVerifierFactory;
use LoyaltyCorp\EasyIdentity\Tests\AbstractTestCase;

/**
 * @covers \LoyaltyCorp\EasyIdentity\Implementations\Auth0\TokenVerifierFactory
 */
class TokenVerifierFactoryTest extends AbstractTestCase
{
    /**
     * Factory should create the expected token verifier instance.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\RequiredDataMissingException
     * @throws \Auth0\SDK\Exception\CoreException
     */
    public function testCreate(): void
    {
        $config = new Config([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'connection' => 'connection',
            'domain' => 'domain'
        ]);

        self::assertInstanceOf(JWTVerifier::class, (new TokenVerifierFactory($config))->create());
    }
}

\class_alias(
    TokenVerifierFactoryTest::class,
    'StepTheFkUp\EasyIdentity\Tests\Implementations\Auth0\TokenVerifierFactoryTest',
    false
);
