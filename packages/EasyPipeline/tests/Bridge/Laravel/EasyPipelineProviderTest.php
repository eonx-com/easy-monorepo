<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Tests\Bridge\Laravel;

use Illuminate\Contracts\Foundation\Application;
use Mockery\MockInterface;
use StepTheFkUp\EasyPipeline\Bridge\Laravel\EasyIlluminatePipelineServiceProvider;
use StepTheFkUp\EasyPipeline\Exceptions\EmptyMiddlewareProvidersListException;
use StepTheFkUp\EasyPipeline\Interfaces\PipelineFactoryInterface;
use StepTheFkUp\EasyPipeline\Tests\AbstractTestCase;

final class EasyPipelineProviderTest extends AbstractTestCase
{
    /**
     * Provider should throw exception when no repositories to register.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyPipeline\Exceptions\EmptyMiddlewareProvidersListException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testEmptyProvidersListException(): void
    {
        $this->expectException(EmptyMiddlewareProvidersListException::class);

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $this->mockApp();

        (new EasyIlluminatePipelineServiceProvider($app))->register();
    }

    /**
     * Provider should call the application to bind all the repositories with their interfaces.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyPipeline\Exceptions\EmptyMiddlewareProvidersListException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testRegisterProvidersSuccessfully(): void
    {
        $config = [
            'providers' => [
                'pipeline-1' => 'provider-1',
                'pipeline-2' => 'provider-2',
                'pipeline-3' => 'provider-3'
            ]
        ];

        $prefixedKeys = [];
        foreach (\array_keys($config['providers']) as $key) {
            $prefixedKeys[] = EasyIlluminatePipelineServiceProvider::PROVIDERS_PREFIX . $key;
        }

        $app = $this->mockApp($config);
        $app->shouldReceive('bind')
            ->times(\count($config['providers']))
            ->withArgs(function ($interface, $provider) use ($config, $prefixedKeys): bool {
                $providers = $config['providers'];
                $return = \in_array($interface, $prefixedKeys, true) && \in_array($provider, $providers, true);

                self::assertTrue($return);

                return $return;
            });

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        (new EasyIlluminatePipelineServiceProvider($app))->register();
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
            $app->shouldReceive('singleton')->once()->with(PipelineFactoryInterface::class, \Mockery::type(\Closure::class));
        });
    }
}