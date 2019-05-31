<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDocker\Tests;

use LoyaltyCorp\EasyDocker\Console\EasyDockerApplication;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    /** @var string */
    protected static $cwd = __DIR__ . '/../tmp';

    /** @var \Symfony\Component\Filesystem\Filesystem */
    private $filesystem;

    /**
     * Execute command and return display.
     *
     * @param string $command
     * @param null|array $inputs
     *
     * @return string
     *
     * @throws \Exception
     */
    protected function executeCommand(string $command, ?array $inputs = null): string
    {
        $tester = new CommandTester($this->getApplication()->find($command));
        $tester->setInputs($inputs ?? []);
        $tester->execute([
            'command' => $command,
            '--cwd' => static::$cwd
        ], ['capture_stderr_separately' => true]);

        return $tester->getDisplay();
    }

    /**
     * Get application.
     *
     * @return \LoyaltyCorp\EasyDocker\Console\EasyDockerApplication
     *
     * @throws \Exception
     */
    protected function getApplication(): EasyDockerApplication
    {
        /** @var \Symfony\Component\DependencyInjection\Container $container */
        $container = require __DIR__ . '/../bin/container.php';

        return $container->get(EasyDockerApplication::class);
    }

    /**
     * Get filesystem.
     *
     * @return \Symfony\Component\Filesystem\Filesystem
     */
    protected function getFilesystem(): Filesystem
    {
        if ($this->filesystem !== null) {
            return $this->filesystem;
        }

        return $this->filesystem = new Filesystem();
    }

    /**
     * Remove tmp dir.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->getFilesystem()->remove(static::$cwd);

        parent::tearDown();
    }
}
