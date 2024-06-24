<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Configurators;

use EonX\EasyApiToken\Common\ValueObject\ApiKey;
use EonX\EasySecurity\SecurityContext;
use EonX\EasySecurity\Tests\AbstractTestCase;
use EonX\EasySecurity\Tests\Stubs\PermissionFromApiKeyConfiguratorStub;
use Symfony\Component\HttpFoundation\Request;

final class AbstractFromApiKeyConfiguratorTest extends AbstractTestCase
{
    public function testPermissionNotSetWhenNotApiKeyToken(): void
    {
        $context = new SecurityContext();
        $configurator = new PermissionFromApiKeyConfiguratorStub('my-permission');
        $configurator->configure($context, new Request());

        self::assertFalse($context->hasPermission('my-permission'));
    }

    public function testPermissionSetWhenApiKeyToken(): void
    {
        $context = new SecurityContext();
        $context->setToken(new ApiKey('api-key'));

        $configurator = new PermissionFromApiKeyConfiguratorStub('my-permission');
        $configurator->configure($context, new Request());

        self::assertTrue($context->hasPermission('my-permission'));
    }
}
