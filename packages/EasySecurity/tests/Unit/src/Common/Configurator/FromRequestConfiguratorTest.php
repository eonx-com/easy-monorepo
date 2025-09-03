<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\Common\Configurator;

use EonX\EasySecurity\Common\Configurator\FromRequestConfigurator;
use EonX\EasySecurity\Common\Context\SecurityContext;
use EonX\EasySecurity\Tests\Stub\Configurator\PermissionFromApiKeyConfiguratorStub;
use EonX\EasySecurity\Tests\Stub\Configurator\StopPropagationConfiguratorStub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class FromRequestConfiguratorTest extends TestCase
{
    public function testStopPropagationPreventsLaterConfigurators(): void
    {
        $request = new Request();
        // Priorities: lower first -> stop(-10) runs, then 0 would be skipped because stopPropagation called
        $stop = new StopPropagationConfiguratorStub(-10);
        $second = new PermissionFromApiKeyConfiguratorStub('should_not_be_added', 0);
        $fromRequest = new FromRequestConfigurator($request, [$second, $stop]); // Out of order to ensure sorting
        $context = new SecurityContext();

        $fromRequest($context);

        self::assertTrue($context->hasPermission('stop'), 'First configurator should add permission');
        self::assertFalse($context->hasPermission('should_not_be_added'), 'Second configurator should not execute');
    }
}
