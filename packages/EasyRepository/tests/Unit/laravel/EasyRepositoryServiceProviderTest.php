<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Tests\Unit\Laravel;

use EonX\EasyRepository\Laravel\EasyRepositoryServiceProvider;
use EonX\EasyRepository\Tests\Stub\Repository\ARepositoryStub;
use EonX\EasyRepository\Tests\Stub\Repository\BRepositoryStub;
use LogicException;

final class EasyRepositoryServiceProviderTest extends AbstractLumenTestCase
{
    public function testEmptyRepositoriesListException(): void
    {
        $this->expectException(LogicException::class);

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $this->getApplication();

        new EasyRepositoryServiceProvider($app)
            ->register();
    }

    public function testRegisterRepositoriesSuccessfully(): void
    {
        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $this->getApplication();
        /** @var \Illuminate\Config\Repository $config */
        $config = \config();
        $config->set('easy-repository.repositories', [
            'interface-1' => ARepositoryStub::class,
            'interface-2' => BRepositoryStub::class,
        ]);

        $provider = new EasyRepositoryServiceProvider($app);
        $provider->boot();
        $provider->register();

        $this->assertInstanceInApp(ARepositoryStub::class, 'interface-1');
        $this->assertInstanceInApp(BRepositoryStub::class, 'interface-2');
    }
}
