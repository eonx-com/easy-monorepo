<?php

declare(strict_types=1);

namespace EonX\EasyCfhighlander\Tests;

use EonX\EasyCfhighlander\Console\CfhighlanderApplication;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * @var string
     */
    protected static $cwd = __DIR__ . '/../tmp';

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * @param null|mixed[] $inputs
     *
     * @throws \Exception
     */
    protected function executeCommand(string $command, ?array $inputs = null): string
    {
        $tester = new CommandTester($this->getApplication()->find($command));
        $tester->setInputs($inputs ?? []);
        $tester->execute([
            'command' => $command,
            '--cwd' => static::$cwd,
        ], [
            'capture_stderr_separately' => true,
        ]);

        return $tester->getDisplay();
    }

    protected function getApplication(): CfhighlanderApplication
    {
        /** @var \Symfony\Component\DependencyInjection\Container $container */
        $container = require __DIR__ . '/../bin/container.php';

        /** @var \EonX\EasyCfhighlander\Console\CfhighlanderApplication $app */
        $app = $container->get(CfhighlanderApplication::class);

        return $app;
    }

    protected function getFilesystem(): Filesystem
    {
        if ($this->filesystem !== null) {
            return $this->filesystem;
        }

        return $this->filesystem = new Filesystem();
    }

    protected function tearDown(): void
    {
        $this->getFilesystem()->remove(static::$cwd);

        parent::tearDown();
    }
}
