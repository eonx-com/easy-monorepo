<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Tests\Bridge\Laravel;

use EonX\EasyRepository\Bridge\Laravel\EasyRepositoryProvider;
use EonX\EasyRepository\Bridge\Laravel\Exceptions\EmptyRepositoriesListException;
use EonX\EasyRepository\Tests\AbstractLumenTestCase;
use EonX\EasyRepository\Tests\Bridge\Laravel\Stubs\Repository1Stub;
use EonX\EasyRepository\Tests\Bridge\Laravel\Stubs\Repository2Stub;

final class EasyRepositoryProviderTest extends AbstractLumenTestCase
{
    /**
     * Provider should throw exception when no repositories to register.
     *
     * @return void
     *
     * @throws \EonX\EasyRepository\Bridge\Laravel\Exceptions\EmptyRepositoriesListException
     */
    public function testEmptyRepositoriesListException(): void
    {
        $this->expectException(EmptyRepositoriesListException::class);

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $this->getApplication();

        (new EasyRepositoryProvider($app))->register();
    }

    /**
     * Provider should call the application to bind all the repositories with their interfaces.
     *
     * @return void
     *
     * @throws \EonX\EasyRepository\Bridge\Laravel\Exceptions\EmptyRepositoriesListException
     */
    public function testRegisterRepositoriesSuccessfully(): void
    {
        $app = $this->getApplication();
        \config()->set('easy-repository.repositories', [
            'interface-1' => Repository1Stub::class,
            'interface-2' => Repository2Stub::class
        ]);

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $provider = new EasyRepositoryProvider($app);
        $provider->boot();
        $provider->register();

        $this->assertInstanceInApp(Repository1Stub::class, 'interface-1');
        $this->assertInstanceInApp(Repository2Stub::class, 'interface-2');
    }
}
