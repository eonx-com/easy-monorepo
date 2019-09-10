<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyCore\Tests\Bridge\Laravel;

use Laravel\Lumen\Application;
use LoyaltyCorp\EasyCore\Bridge\Laravel\ConfigurationServiceProvider;
use LoyaltyCorp\EasyCore\Tests\AbstractVfsTestCase;
use org\bovigo\vfs\vfsStream;

/**
 * @covers \LoyaltyCorp\EasyCore\Bridge\Laravel\ConfigurationServiceProvider
 *
 * @internal
 */
final class ConfigurationServiceProviderTest extends AbstractVfsTestCase
{
    /**
     * Test register successfully.
     *
     * @return void
     */
    public function testRegisterSucceeds(): void
    {
        $structure = [
            'config' => [
                'config1.php' => "<?php\r\nreturn ['a' => 'a'];",
                'config1.txt' => "<?php\r\nreturn ['b' => 'b'];",
                'config2.php' => "<?php\r\nreturn ['c' => 'c'];"
            ]
        ];
        $base = vfsStream::setup('base', null, $structure);
        $appProphecy = $this->prophesize(Application::class);
        $appProphecy->basePath()->willReturn($base->url());
        $appProphecy->configure('config1')->willReturn();
        $appProphecy->configure('config2')->willReturn();
        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $appProphecy->reveal();
        $serviceProvider = new ConfigurationServiceProvider($app);

        $serviceProvider->register();

        $appProphecy->basePath()->shouldHaveBeenCalledOnce();
        $appProphecy->configure('config1')->shouldHaveBeenCalledOnce();
        $appProphecy->configure('config2')->shouldHaveBeenCalledOnce();
    }

    /**
     * Test register fails if config path is not a directory.
     *
     * @return void
     */
    public function testRegisterWithConfigPathAsFileFails(): void
    {
        $base = vfsStream::setup('base', null, [
            'config' => ''
        ]);
        $appProphecy = $this->prophesize(Application::class);
        $appProphecy->basePath()->willReturn($base->url());
        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $app = $appProphecy->reveal();
        $serviceProvider = new ConfigurationServiceProvider($app);

        $serviceProvider->register();

        $appProphecy->basePath()->shouldHaveBeenCalledOnce();
        $appProphecy->configure()->shouldNotHaveBeenCalled();
    }
}
