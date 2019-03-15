<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyIdentity\Tests\Bridge\Laravel;

use StepTheFkUp\EasyIdentity\Bridge\Laravel\Auth0IdentityServiceProvider;
use StepTheFkUp\EasyIdentity\Implementations\Auth0\Auth0IdentityService;
use StepTheFkUp\EasyIdentity\Interfaces\IdentityServiceInterface;
use StepTheFkUp\EasyIdentity\Tests\AbstractLumenTestCase;

final class Auth0IdentityServiceProviderTest extends AbstractLumenTestCase
{
    /**
     * Provider should register the Auth0 identity service under the generic interface.
     *
     * @return void
     */
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
