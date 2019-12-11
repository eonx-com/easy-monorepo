<?php
declare(strict_types=1);

namespace EonX\EasyCore\Tests\Commands\Lumen;

use Illuminate\Console\Command;
use Laravel\Lumen\Application;
use EonX\EasyCore\Console\Commands\Lumen\ClearConfigCommand;
use EonX\EasyCore\Tests\AbstractVfsTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers \EonX\EasyCore\Console\Commands\Lumen\ClearConfigCommand
 *
 * @internal
 */
final class ClearConfigCommandTest extends AbstractVfsTestCase
{
    /**
     * Test handle successfully.
     *
     * @throws \ReflectionException
     */
    public function testHandleSucceeds(): void
    {
        $structure = [
            'bootstrap' => [
                'app.php' => ''
            ],
            'storage' => [
                'cached_config.php' => ''
            ]
        ];
        $root = vfsStream::setup('root', null, $structure);
        $appProphecy = $this->prophesizeAppForHandle($root);
        /** @var \Laravel\Lumen\Application $app */
        $app = $appProphecy->reveal();
        /** @var \Symfony\Component\Console\Application $symfonyApp */
        $symfonyApp = $this->prophesizeSymfonyForHandle()->reveal();
        // Finally, configuring the command itself
        $command = new ClearConfigCommand();
        $command->setLaravel($app);
        $command->setApplication($symfonyApp);
        $this->setCommandPrivateProperty(
            $command,
            'output',
            $this->prophesize(OutputInterface::class)->reveal()
        );

        $command->handle();

        $appProphecy->storagePath('cached_config.php')->shouldHaveBeenCalledOnce();
        self::assertFalse($root->hasChild('storage/cached_config.php'));
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
     * Prophesize \Symfony\Component\Console\Application.
     *
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function prophesizeSymfonyForHandle(): ObjectProphecy
    {
        $symfonyProphecy = $this->prophesize(\Symfony\Component\Console\Application::class);
        $symfonyProphecy->getHelperSet()->willReturn($this->prophesize(HelperSet::class));

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
}
