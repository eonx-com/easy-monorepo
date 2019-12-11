<?php
declare(strict_types=1);

namespace EonX\EasyCore\Tests\Commands\Lumen;

use Illuminate\Console\Command;
use Laravel\Lumen\Application;
use EonX\EasyCore\Console\Commands\Lumen\CacheConfigCommand;
use EonX\EasyCore\Tests\AbstractVfsTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers \EonX\EasyCore\Console\Commands\Lumen\CacheConfigCommand
 *
 * @internal
 */
final class CacheConfigCommandTest extends AbstractVfsTestCase
{
    /**
     * Test handle fails because of unserializable value in config files.
     *
     * @throws \ReflectionException
     * @throws \LogicException
     */
    public function testHandleFailsWithLogicException(): void
    {
        $structure = [
            'bootstrap' => [
                'app.php' => ''
            ],
            'config' => [
                'config1.php' => "<?php\nreturn ['a'=>'a'];",
                'config2.php' => "<?php\nreturn ['b'=>function(){}];"
            ],
            'storage' => [
                'cached_config.php' => ''
            ]
        ];
        $root = vfsStream::setup('root', null, $structure);
        $this->writeBootstrapFile($root);
        $appProphecy = $this->prophesizeAppForHandle($root);
        /** @var \Laravel\Lumen\Application $app */
        $app = $appProphecy->reveal();
        $clearProphecy = $this->prophesizeClearCacheCommand();
        /** @var \Symfony\Component\Console\Application $symfonyApp */
        $symfonyApp = $this->prophesizeSymfonyForHandle($clearProphecy)->reveal();
        // Finally, configuring the command itself
        $command = new CacheConfigCommand();
        $command->setLaravel($app);
        $command->setApplication($symfonyApp);
        $this->setCommandPrivateProperty($command, 'input', new StringInput(''));
        $this->setCommandPrivateProperty(
            $command,
            'output',
            $this->prophesize(OutputInterface::class)->reveal()
        );
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Your configuration files are not serializable.');

        $command->handle();

        $clearProphecy
            ->run(Argument::type(InputInterface::class), Argument::type(OutputInterface::class))
            ->shouldHaveBeenCalledOnce();
        $appProphecy->storagePath('cached_config.php')->shouldHaveBeenCalledOnce();
        self::assertFalse($root->hasChild('storage/cached_config.php'));
    }

    /**
     * Test handle successfully.
     *
     * @throws \ReflectionException
     * @throws \LogicException
     */
    public function testHandleSucceeds(): void
    {
        $structure = [
            'bootstrap' => [
                'app.php' => ''
            ],
            'config' => [
                'config1.php' => "<?php\nreturn ['a'=>'a'];",
                'config2.php' => "<?php\nreturn ['b'=>'b'];"
            ],
            'storage' => [
                'cached_config.php' => ''
            ]
        ];
        $root = vfsStream::setup('root', null, $structure);
        $this->writeBootstrapFile($root);
        $appProphecy = $this->prophesizeAppForHandle($root);
        /** @var \Laravel\Lumen\Application $app */
        $app = $appProphecy->reveal();
        $clearProphecy = $this->prophesizeClearCacheCommand();
        /** @var \Symfony\Component\Console\Application $symfonyApp */
        $symfonyApp = $this->prophesizeSymfonyForHandle($clearProphecy)->reveal();
        // Finally, configuring the command itself
        $command = new CacheConfigCommand();
        $command->setLaravel($app);
        $command->setApplication($symfonyApp);
        $this->setCommandPrivateProperty($command, 'input', new StringInput(''));
        $this->setCommandPrivateProperty(
            $command,
            'output',
            $this->prophesize(OutputInterface::class)->reveal()
        );

        $command->handle();

        $clearProphecy
            ->run(Argument::type(InputInterface::class), Argument::type(OutputInterface::class))
            ->shouldHaveBeenCalledOnce();
        $appProphecy->storagePath('cached_config.php')->shouldHaveBeenCalledOnce();
        self::assertTrue($root->hasChild('storage/cached_config.php'));
        self::assertSame(
            "<?php return array (\n" .
            "  'config1' => \n  array (\n    'a' => 'a',\n  ),\n" .
            "  'config2' => \n  array (\n    'b' => 'b',\n  ),\n" .
            ");\n",
            \file_get_contents($root->getChild('storage/cached_config.php')->url())
        );
    }

    /**
     * Prophesize \Laravel\Lumen\Application.
     *
     * @param \org\bovigo\vfs\vfsStreamDirectory $root
     *
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function prophesizeAppForHandle(vfsStreamDirectory $root): ObjectProphecy
    {
        $appProphecy = $this->prophesize(Application::class);
        $appProphecy->basePath()->willReturn($root->url());
        $appProphecy
            ->storagePath('cached_config.php')
            ->willReturn($root->getChild('storage/cached_config.php')->url());

        return $appProphecy;
    }

    /**
     * Prophesize ClearCacheCommand.
     *
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function prophesizeClearCacheCommand(): ObjectProphecy
    {
        $clearProphecy = $this->prophesize(Command::class);
        $clearProphecy
            ->run(Argument::type(InputInterface::class), Argument::type(OutputInterface::class))
            ->willReturn();

        return $clearProphecy;
    }

    /**
     * Prophesize \Symfony\Component\Console\Application.
     *
     * @param \Prophecy\Prophecy\ObjectProphecy $clearProphecy
     *
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function prophesizeSymfonyForHandle(ObjectProphecy $clearProphecy): ObjectProphecy
    {
        $symfonyProphecy = $this->prophesize(\Symfony\Component\Console\Application::class);
        $symfonyProphecy->getHelperSet()->willReturn($this->prophesize(HelperSet::class));
        $symfonyProphecy->find('config:clear')->willReturn($clearProphecy->reveal());

        return $symfonyProphecy;
    }

    /**
     * Set `Command`'s private property value.
     *
     * @param \Illuminate\Console\Command $command
     * @param string $propertyName
     * @param mixed $value
     *
     * @throws \ReflectionException
     */
    protected function setCommandPrivateProperty(Command $command, string $propertyName, $value): void
    {
        $reflection = new \ReflectionClass(Command::class);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($command, $value);
    }

    /**
     * Write basic bootstrap file with `ConfigurationServiceProvider` registered.
     *
     * @param \org\bovigo\vfs\vfsStreamDirectory $root
     */
    protected function writeBootstrapFile(vfsStreamDirectory $root): void
    {
        \file_put_contents(
            $root->getChild('bootstrap/app.php')->url(),
            "<?php
            \$app = new \\Laravel\\Lumen\\Application('" . $root->url() . "');
            \$app->register(\\EonX\\EasyCore\\Bridge\\Laravel\\ConfigurationServiceProvider::class);
            return \$app;
            "
        );
    }
}
