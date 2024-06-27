<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\Laravel;

use EonX\EasySecurity\Authorization\Factory\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Tests\Stub\ServiceProvider\RolesAndPermissionsServiceProvider;

final class AuthorizationMatrixProviderTest extends AbstractLumenTestCase
{
    public function testRolesAndPermissionsProvidersInjected(): void
    {
        $app = $this->getApplication([RolesAndPermissionsServiceProvider::class]);

        /** @var \EonX\EasySecurity\Authorization\Factory\AuthorizationMatrixFactoryInterface $authorizationMatrixFactory */
        $authorizationMatrixFactory = $app->get(AuthorizationMatrixFactoryInterface::class);
        $authorizationMatrix = $authorizationMatrixFactory->create();

        self::assertTrue($authorizationMatrix->isRole('role'));
        self::assertTrue($authorizationMatrix->isPermission('permission'));
    }
}
