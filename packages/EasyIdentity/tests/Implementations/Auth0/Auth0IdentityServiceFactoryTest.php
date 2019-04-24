<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Tests\Implementations\Auth0;

use LoyaltyCorp\EasyIdentity\Implementations\Auth0\Auth0IdentityService;
use LoyaltyCorp\EasyIdentity\Implementations\Auth0\Auth0IdentityServiceFactory;
use LoyaltyCorp\EasyIdentity\Tests\AbstractTestCase;

final class Auth0IdentityServiceFactoryTest extends AbstractTestCase
{
    /**
     * Factory should create the Auth0IdentityService instance.
     *
     * @return void
     */
    public function testCreate(): void
    {
        self::assertInstanceOf(
            Auth0IdentityService::class,
            (new Auth0IdentityServiceFactory())->create([
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'connection' => 'connection',
                'domain' => 'domain'
            ])
        );
    }
}

\class_alias(
    Auth0IdentityServiceFactoryTest::class,
    'StepTheFkUp\EasyIdentity\Tests\Implementations\Auth0\Auth0IdentityServiceFactoryTest',
    false
);
