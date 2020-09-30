<?php

declare(strict_types=1);

namespace EonX\EasyTest\Tests;

use EonX\EasyTest\Console\EasyTestApplication;
use EonX\EasyTest\HttpKernel\EasyTestKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractTestCase extends TestCase
{
    /**
     * @var \Symfony\Component\Console\Application
     */
    private $app;

    /**
     * @param null|mixed[] $inputs
     *
     * @throws \Exception
     */
    protected function executeCommand(string $command, ?array $inputs = null): string
    {
        $tester = new CommandTester($this->getApplication()->find($command));
        $tester->execute(\array_merge([
            'command' => $command,
        ], $inputs ?? []), [
            'capture_stderr_separately' => true,
        ]);

        return $tester->getDisplay();
    }

    private function getApplication(): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $kernel = new EasyTestKernel('test', true);
        $kernel->boot();

        /** @var \Symfony\Component\Console\Application $app */
        $app = $kernel->getContainer()->get(EasyTestApplication::class);

        return $this->app = $app;
    }
}
