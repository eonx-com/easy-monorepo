<?php

declare(strict_types=1);

namespace EonX\EasySsm\Tests;

use EonX\EasySsm\Console\EasySsmApplication;
use EonX\EasySsm\HttpKernel\EasySsmKernel;
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
    protected static $cwd = __DIR__ . '/../var';

    /**
     * @param null|mixed[] $inputs
     * @param null|mixed[] $configs
     *
     * @throws \Exception
     */
    protected function executeCommand(string $command, ?array $inputs = null, ?array $configs = null): string
    {
        $kernel = new EasySsmKernel($configs ?? []);
        $kernel->boot();

        $tester = new CommandTester($kernel->getContainer()->get(EasySsmApplication::class)->find($command));
        $tester->setInputs($inputs ?? []);
        $tester->execute(['command' => $command], ['capture_stderr_separately' => true]);

        return $tester->getDisplay();
    }

    protected function tearDown(): void
    {
        $fs = new Filesystem();

        if ($fs->exists(static::$cwd)) {
            $fs->remove(static::$cwd);
        }

        parent::tearDown();
    }
}
