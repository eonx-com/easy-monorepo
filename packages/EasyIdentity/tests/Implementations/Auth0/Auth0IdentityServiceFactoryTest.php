<?php
declare(strict_types=1);

namespace EonX\EasyIdentity\Tests\Implementations\Auth0;

use EonX\EasyIdentity\Implementations\Auth0\Auth0IdentityServiceFactory;
use EonX\EasyIdentity\Tests\AbstractTestCase;

final class Auth0IdentityServiceFactoryTest extends AbstractTestCase
{
    /**
     * Factory should create the Auth0IdentityService instance.
     *
     * @return void
     */
    public function testCreate(): void
    {
        $factory = new Auth0IdentityServiceFactory();
        $factory->create([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'connection' => 'connection',
            'domain' => 'domain'
        ]);

        // If service was instantiated without error then test passes.
        $this->addToAssertionCount(1);
    }

    /**
     * Test config with empty string.
     * This can happen when auth0 is not setup in env or has empty values.
     * @see https://eonx.atlassian.net/browse/PYMT-1020
     *
     * @return void
     */
    public function testCreateWorksWithEmptyConfig(): void
    {
        $factory = new Auth0IdentityServiceFactory();
        $factory->create([
            'client_id' => '',
            'client_secret' => '',
            'connection' => '',
            'domain' => ''
        ]);

        // assert the above code works without any exception thrown.
        $this->addToAssertionCount(1);
    }
}

\class_alias(
    Auth0IdentityServiceFactoryTest::class,
    EonX\EasyIdentity\Tests\Implementations\Auth0\Auth0IdentityServiceFactoryTest::class,
    false
);
