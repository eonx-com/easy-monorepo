<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyRepository\Tests\Bridge\Laravel;

use Illuminate\Contracts\Foundation\Application;
use Mockery\MockInterface;
use StepTheFkUp\EasyRepository\Bridge\Laravel\EasyRepositoryProvider;
use StepTheFkUp\EasyRepository\Bridge\Laravel\Exceptions\EmptyRepositoriesListException;
use StepTheFkUp\EasyRepository\Tests\AbstractTestCase;

final class EasyRepositoryProviderTest extends AbstractTestCase
{
    /**
     * Provider should throw exception when no repositories to register.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyRepository\Bridge\Laravel\Exceptions\EmptyRepositoriesListException
     */
    public function testEmptyRepositoriesListException(): void
    {
        $this->expectException(EmptyRepositoriesListException::class);

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $this->mockApp();

        (new EasyRepositoryProvider($app))->register();
    }

    /**
     * Provider should call the application to bind all the repositories with their interfaces.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyRepository\Bridge\Laravel\Exceptions\EmptyRepositoriesListException
     */
    public function testRegisterRepositoriesSuccessfully(): void
    {
        $config = [
            'repositories' => [
                'interface-1' => 'repository-1',
                'interface-2' => 'repository-2',
                'interface-3' => 'repository-3'
            ]
        ];

        $app = $this->mockApp($config);
        $app->shouldReceive('bind')
            ->times(\count($config['repositories']))
            ->withArgs(function ($interface, $repository) use ($config): bool {
                $repositories = $config['repositories'];
                $return = \array_key_exists($interface, $repositories) && \in_array($repository, $repositories);

                self::assertTrue($return);

                return $return;
            });

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        (new EasyRepositoryProvider($app))->register();
    }

    /**
     * Mock Illuminate application for given config.
     *
     * @param mixed[]|null $config
     *
     * @return \Mockery\MockInterface
     */
    private function mockApp(?array $config = null): MockInterface
    {
        return $this->mock(Application::class, function (MockInterface $app) use ($config): void {
            $config = new class($config ?? []) {
                private $config;

                public function __construct(array $config)
                {
                    $this->config = $config;
                }

                public function get(string $key) {
                    return $this->config;
                }
            };

            $app->shouldReceive('make')->once()->with('config')->andReturn($config);
        });
    }
}