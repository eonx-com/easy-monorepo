<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\Common\Configurator;

use EonX\EasySecurity\Common\Context\SecurityContext;
use EonX\EasySecurity\Tests\Stub\Configurator\PermissionFromHeaderConfiguratorStub;
use EonX\EasySecurity\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

final class AbstractFromHeaderConfiguratorTest extends AbstractUnitTestCase
{
    public function testPermissionNotSetWhenNotApiKeyToken(): void
    {
        $context = new SecurityContext();
        $configurator = new PermissionFromHeaderConfiguratorStub('my-permission', ['my-header']);
        $configurator->configure($context, new Request());

        self::assertFalse($context->hasPermission('my-permission'));
    }

    public function testPermissionSetWhenApiKeyToken(): void
    {
        $context = new SecurityContext();
        $request = new Request([], [], [], [], [], [
            'HTTP_my-header' => 'value',
        ]);

        $configurator = new PermissionFromHeaderConfiguratorStub('my-permission', ['my-header']);
        $configurator->configure($context, $request);

        self::assertTrue($context->hasPermission('my-permission'));
    }
}
