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
     * @param null|mixed[] $argsOpts
     *
     * @throws \Exception
     */
    protected function executeCommand(
        string $command,
        ?array $inputs = null,
        ?array $configs = null,
        ?array $argsOpts = null
    ): string {
        $kernel = new EasySsmKernel($configs ?? []);
        $kernel->boot();

        $argsOpts = $argsOpts ?? [];
        $argsOpts['command'] = $command;

        $tester = new CommandTester($kernel->getContainer()->get(EasySsmApplication::class)->find($command));
        $tester->setInputs($inputs ?? []);
        $tester->execute($argsOpts, [
            'capture_stderr_separately' => true,
        ]);

        return $tester->getDisplay();
    }

    protected function tearDown(): void
    {
        $fs = new Filesystem();

        $toRemove = [
            static::$cwd,
            \sys_get_temp_dir() . '/easy_ssm',
            \sys_get_temp_dir() . '/easy_ssm_logs',
            '.env.local.php',
        ];

        foreach ($toRemove as $path) {
            if ($fs->exists($path)) {
                $fs->remove($path);
            }
        }

        parent::tearDown();
    }
}
