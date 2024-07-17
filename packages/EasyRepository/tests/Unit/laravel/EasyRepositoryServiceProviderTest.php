<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Tests\Unit\Laravel;

use EonX\EasyRepository\Laravel\EasyRepositoryServiceProvider;
use EonX\EasyRepository\Tests\Stub\Repository\FirstRepositoryStub;
use EonX\EasyRepository\Tests\Stub\Repository\SecondRepositoryStub;
use LogicException;

final class EasyRepositoryServiceProviderTest extends AbstractLumenTestCase
{
    public function testEmptyRepositoriesListException(): void
    {
        $this->expectException(LogicException::class);

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $this->getApplication();

        (new EasyRepositoryServiceProvider($app))->register();
    }

    public function testRegisterRepositoriesSuccessfully(): void
    {
        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $this->getApplication();
        \config()
            ->set('easy-repository.repositories', [
                'interface-1' => FirstRepositoryStub::class,
                'interface-2' => SecondRepositoryStub::class,
            ]);

        $provider = new EasyRepositoryServiceProvider($app);
        $provider->boot();
        $provider->register();

        $this->assertInstanceInApp(FirstRepositoryStub::class, 'interface-1');
        $this->assertInstanceInApp(SecondRepositoryStub::class, 'interface-2');
    }
}
