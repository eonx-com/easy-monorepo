<?php
declare(strict_types=1);

namespace EonX\EasyTest\Tests\Unit;

use EonX\EasyTest\Coverage\Application\EasyTestApplication;
use EonX\EasyTest\Coverage\Kernel\EasyTestKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractUnitTestCase extends TestCase
{
    private ?Application $app = null;

    /**
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

        /** @var \EonX\EasyTest\Coverage\Application\EasyTestApplication $app */
        $app = $kernel->getContainer()
            ->get(EasyTestApplication::class);
        $this->app = $app;

        return $this->app;
    }
}
