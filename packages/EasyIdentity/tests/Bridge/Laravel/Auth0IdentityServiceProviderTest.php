<?php
declare(strict_types=1);

namespace EonX\EasyIdentity\Tests\Bridge\Laravel;

use EonX\EasyIdentity\Bridge\Laravel\Auth0IdentityServiceProvider;
use EonX\EasyIdentity\Implementations\Auth0\Auth0IdentityService;
use EonX\EasyIdentity\Interfaces\IdentityServiceInterface;
use EonX\EasyIdentity\Tests\AbstractLumenTestCase;

final class Auth0IdentityServiceProviderTest extends AbstractLumenTestCase
{
    public function testRegister(): void
    {
        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $this->getApplication();

        $serviceProvider = new Auth0IdentityServiceProvider($app);
        $serviceProvider->boot();
        $serviceProvider->register();

        self::assertInstanceOf(IdentityServiceInterface::class, $app->get(IdentityServiceInterface::class));
        self::assertInstanceOf(Auth0IdentityService::class, $app->get(IdentityServiceInterface::class));
    }
}
