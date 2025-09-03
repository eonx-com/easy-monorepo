<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\Common\Command;

use EonX\EasySecurity\Common\Command\ListSecurityContextConfiguratorsCommand;
use EonX\EasySecurity\Tests\Stub\Configurator\PermissionFromApiKeyConfiguratorStub;
use EonX\EasySecurity\Tests\Stub\Configurator\PermissionFromHashedApiKeyConfiguratorStub;
use EonX\EasySecurity\Tests\Stub\Configurator\PermissionFromHeaderConfiguratorStub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class ListSecurityContextConfiguratorsCommandTest extends TestCase
{
    public function testExecuteListsConfiguratorsInExecutionOrder(): void
    {
        // Priorities chosen so ordering (lower first) is: -10, 0, 50
        $cHigh = new PermissionFromHashedApiKeyConfiguratorStub('perm_high', 50);
        $cDefault = new PermissionFromApiKeyConfiguratorStub('perm_default', 0);
        $cLow = new PermissionFromHeaderConfiguratorStub('perm_low', [], -10);
        // Provide in random order to ensure command sorts them
        $command = new ListSecurityContextConfiguratorsCommand([
            $cDefault,
            $cHigh,
            $cLow,
        ]);
        $tester = new CommandTester($command);

        $tester->execute([]);

        $output = $tester->getDisplay();
        $posLow = \strpos($output, PermissionFromHeaderConfiguratorStub::class);
        // Find occurrences; first occurrence should be low priority configurator (-10)
        $posDefault = \strpos($output, PermissionFromApiKeyConfiguratorStub::class, $posLow + 1);
        $posHigh = \strpos($output, PermissionFromHashedApiKeyConfiguratorStub::class, $posDefault + 1);
        self::assertNotFalse($posLow, 'Low priority configurator not found in output');
        self::assertNotFalse($posDefault, 'Default priority configurator not found in output');
        self::assertNotFalse($posHigh, 'High priority configurator not found in output');
        self::assertTrue($posLow < $posDefault && $posDefault < $posHigh, 'Configurators not listed in expected order');
        self::assertStringContainsString('Execution order = lower priority first. Higher priority runs last.', $output);
    }

    public function testExecuteWithNoConfigurators(): void
    {
        $command = new ListSecurityContextConfiguratorsCommand([]);
        $tester = new CommandTester($command);

        $tester->execute([]);

        $output = $tester->getDisplay();
        self::assertSame("No security context configurator registered.\n", $output);
    }
}
