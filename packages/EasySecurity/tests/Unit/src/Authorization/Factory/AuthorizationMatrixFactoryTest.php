<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\Authorization\Factory;

use EonX\EasySecurity\Authorization\Factory\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Tests\Unit\AbstractSymfonyTestCase;

final class AuthorizationMatrixFactoryTest extends AbstractSymfonyTestCase
{
    public function testRolesAndPermissionsProvidersInjected(): void
    {
        $kernel = $this->getKernel([__DIR__ . '/../../../../Fixture/config/authorization_roles_permissions.php']);
        /** @var \EonX\EasySecurity\Authorization\Factory\AuthorizationMatrixFactoryInterface $authorizationMatrixFactory */
        $authorizationMatrixFactory = $kernel->getContainer()
            ->get(AuthorizationMatrixFactoryInterface::class);
        $authorizationMatrix = $authorizationMatrixFactory->create();

        self::assertTrue($authorizationMatrix->isRole('role'));
        self::assertTrue($authorizationMatrix->isPermission('permission'));
    }
}
