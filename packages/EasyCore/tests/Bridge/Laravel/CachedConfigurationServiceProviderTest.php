<?php
declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Laravel;

use Illuminate\Config\Repository;
use Illuminate\Console\Command;
use Illuminate\Events\Dispatcher;
use Laravel\Lumen\Application;
use EonX\EasyCore\Bridge\Laravel\CachedConfigurationServiceProvider;
use EonX\EasyCore\Bridge\Laravel\ConfigurationServiceProvider;
use EonX\EasyCore\Console\Commands\Lumen\CacheConfigCommand;
use EonX\EasyCore\Console\Commands\Lumen\ClearConfigCommand;
use EonX\EasyCore\Tests\AbstractVfsTestCase;
use org\bovigo\vfs\vfsStream;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @covers \EonX\EasyCore\Bridge\Laravel\CachedConfigurationServiceProvider
 *
 * @internal
 */
final class CachedConfigurationServiceProviderTest extends AbstractVfsTestCase
{
    /**
     * Test register falls back to ConfigurationServiceProvider.
     *
     * @return void
     */
    public function testRegisterFallsBackToServiceProvider(): void
    {
        $structure = [
            'storage' => [
                'cached_config.php' => ''
            ]
        ];
        $base = vfsStream::setup('base', null, $structure);
        $appProphecy = $this->prophesize(Application::class);
        $appProphecy
            ->storagePath('cached_config.php')
            ->willReturn($base->getChild('storage/cached_config.php')->url());
        $base->removeChild('storage/cached_config.php');
        $appProphecy->runningInConsole()->willReturn(false);
        $appProphecy->register(ConfigurationServiceProvider::class)->willReturn();
        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $appProphecy->reveal();
        $serviceProvider = new CachedConfigurationServiceProvider($app);

        $serviceProvider->register();

        $appProphecy->storagePath('cached_config.php')->shouldHaveBeenCalledOnce();
        $appProphecy->runningInConsole()->shouldHaveBeenCalledOnce();
        $appProphecy->register(ConfigurationServiceProvider::class)->shouldHaveBeenCalledOnce();
    }

    /**
     * Test register successfully with Application not loading original config files via `configure` method.
     *
     * @return void
     */
    public function testRegisterSucceedsWithApplicationConfigureNotLoadingOriginalConfig(): void
    {
        $structure = [
            'config' => [
                'a.php' => "<?php\r\nreturn ['a' => 'original-value'];"
            ],
            'storage' => [
                'cached_config.php' => "<?php\r\nreturn ['a' => 'cached-value'];"
            ]
        ];
        $base = vfsStream::setup('base', null, $structure);
        $repositoryProphecy = $this->prophesize(Repository::class);
        $repositoryProphecy->has('a')->willReturn(false);
        $repositoryProphecy->set('a', 'cached-value')->willReturn();
        $repository = $repositoryProphecy->reveal();
        $app = new Application($base->url());
        $app->instance('config', $repository);
        $serviceProvider = new CachedConfigurationServiceProvider($app);
        $serviceProvider->register();

        $app->configure('a');

        self::assertSame($repository, $app->make('config'));
        $repositoryProphecy->has('a')->shouldHaveBeenCalledOnce();
        $repositoryProphecy->set('a', 'cached-value')->shouldHaveBeenCalledOnce();
        $repositoryProphecy->set('a', 'original-value')->shouldNotHaveBeenCalled();
    }

    /**
     * Test register successfully.
     *
     * @return void
     */
    public function testRegisterSucceeds(): void
    {
        $structure = [
            'storage' => [
                'cached_config.php' => "<?php\r\nreturn ['a' => 'a-value'];"
            ]
        ];
        $base = vfsStream::setup('base', null, $structure);
        $repositoryProphecy = $this->prophesize(Repository::class);
        $repositoryProphecy->has('a')->willReturn(false);
        $repositoryProphecy->set('a', 'a-value')->willReturn();
        $appProphecy = $this->prophesize(Application::class);
        $appProphecy
            ->storagePath('cached_config.php')
            ->willReturn($base->getChild('storage/cached_config.php')->url());
        $appProphecy->runningInConsole()->willReturn(true);
        $appProphecy->make('config')->willReturn($repositoryProphecy->reveal());
        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $appProphecy->reveal();
        $cacheCommandProphecy = $this->prophesizeCommand($app, 'config:cache');
        $clearCommandProphecy = $this->prophesizeCommand($app, 'config:clear');
        $appProphecy->make(CacheConfigCommand::class)->willReturn($cacheCommandProphecy);
        $appProphecy->make(ClearConfigCommand::class)->willReturn($clearCommandProphecy);
        $serviceProvider = new CachedConfigurationServiceProvider($app);

        $serviceProvider->register();
        // Constructor of \Illuminate\Console\Application triggers commands registration.
        new \Illuminate\Console\Application(
            $app,
            $this->prophesize(Dispatcher::class)->reveal(),
            ''
        );

        $appProphecy->storagePath('cached_config.php')->shouldHaveBeenCalledOnce();
        $appProphecy->runningInConsole()->shouldHaveBeenCalledOnce();
        $appProphecy->make('config')->shouldHaveBeenCalledOnce();
        $repositoryProphecy->has('a')->shouldHaveBeenCalledOnce();
        $repositoryProphecy->set('a', 'a-value')->shouldHaveBeenCalledOnce();
        $cacheCommandProphecy->setLaravel($app)->shouldHaveBeenCalledTimes(2);
        $clearCommandProphecy->setLaravel($app)->shouldHaveBeenCalledTimes(2);
    }

    /**
     * Prophesize \Illuminate\Console\Command.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param string $name
     *
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function prophesizeCommand(
        $app,
        string $name
    ): ObjectProphecy {
        $cacheCommandProphecy = $this->prophesize(Command::class);
        $cacheCommandProphecy->setLaravel($app)->willReturn();
        $cacheCommandProphecy->setApplication(Argument::type(\Illuminate\Console\Application::class))->willReturn();
        $cacheCommandProphecy->isEnabled()->willReturn(true);
        $cacheCommandProphecy->getDefinition()->willReturn('');
        $cacheCommandProphecy->getName()->willReturn($name);
        $cacheCommandProphecy->getAliases()->willReturn([]);

        return $cacheCommandProphecy;
    }
}
