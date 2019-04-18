<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyIdentity\Tests\Implementations\Auth0;

use Auth0\SDK\JWTVerifier;
use StepTheFkUp\EasyIdentity\Implementations\Auth0\Config;
use StepTheFkUp\EasyIdentity\Implementations\Auth0\TokenVerifierFactory;
use StepTheFkUp\EasyIdentity\Tests\AbstractTestCase;

/**
 * @covers \StepTheFkUp\EasyIdentity\Implementations\Auth0\TokenVerifierFactory
 */
class TokenVerifierFactoryTest extends AbstractTestCase
{
    /**
     * Factory should create the expected token verifier instance.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\RequiredDataMissingException
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
    'LoyaltyCorp\EasyIdentity\Tests\Implementations\Auth0\TokenVerifierFactoryTest',
    false
);
