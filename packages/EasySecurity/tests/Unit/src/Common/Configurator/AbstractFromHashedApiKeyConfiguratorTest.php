<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\Common\Configurator;

use EonX\EasyApiToken\Common\ValueObject\HashedApiKey;
use EonX\EasySecurity\Common\Context\SecurityContext;
use EonX\EasySecurity\Tests\Stub\Configurator\PermissionFromHashedApiKeyConfiguratorStub;
use EonX\EasySecurity\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

final class AbstractFromHashedApiKeyConfiguratorTest extends AbstractUnitTestCase
{
    public function testPermissionNotSetWhenNotHashedApiKeyToken(): void
    {
        $context = new SecurityContext();
        $configurator = new PermissionFromHashedApiKeyConfiguratorStub('my-permission');
        $configurator->configure($context, new Request());

        self::assertFalse($context->hasPermission('my-permission'));
    }

    public function testPermissionSetWhenHashedApiKeyToken(): void
    {
        $context = new SecurityContext();
        $context->setToken(new HashedApiKey('my-id', 'api-key', 'v1', 'api-key'));

        $configurator = new PermissionFromHashedApiKeyConfiguratorStub('my-permission');
        $configurator->configure($context, new Request());

        self::assertTrue($context->hasPermission('my-permission'));
    }
}
