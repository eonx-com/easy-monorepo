<?php
declare(strict_types=1);

namespace EonX\EasyTest\Tests;

use EonX\EasyTest\Console\Commands\CheckCoverageCommand;
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
     * Execute command and return display.
     *
     * @param string $command
     * @param null|mixed[] $inputs
     *
     * @return string
     *
     * @throws \Exception
     */
    protected function executeCommand(string $command, ?array $inputs = null): string
    {
        $tester = new CommandTester($this->getApplication()->find($command));
        $tester->execute(\array_merge(['command' => $command], $inputs ?? []), ['capture_stderr_separately' => true]);

        return $tester->getDisplay();
    }

    /**
     * Get application.
     *
     * @return \Symfony\Component\Console\Application
     */
    private function getApplication(): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $app = new Application();
        $app->addCommands([new CheckCoverageCommand()]);

        return $this->app = $app;
    }
}
