<?php
declare(strict_types=1);

namespace EonX\EasyIdentity\Tests\Implementations\Auth0;

use EonX\EasyIdentity\Implementations\Auth0\Auth0IdentityServiceFactory;
use EonX\EasyIdentity\Tests\AbstractTestCase;

final class Auth0IdentityServiceFactoryTest extends AbstractTestCase
{
    public function testCreate(): void
    {
        $factory = new Auth0IdentityServiceFactory();
        $factory->create([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'connection' => 'connection',
            'domain' => 'domain',
        ]);

        // If service was instantiated without error then test passes.
        $this->addToAssertionCount(1);
    }

    public function testCreateWorksWithEmptyConfig(): void
    {
        $factory = new Auth0IdentityServiceFactory();
        $factory->create([
            'client_id' => '',
            'client_secret' => '',
            'connection' => '',
            'domain' => '',
        ]);

        // assert the above code works without any exception thrown.
        $this->addToAssertionCount(1);
    }
}
