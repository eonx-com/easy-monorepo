<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony\Authorization;

use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Tests\Bridge\Symfony\AbstractSymfonyTestCase;

final class AuthorizationMatrixFactoryTest extends AbstractSymfonyTestCase
{
    public function testRolesAndPermissionsProvidersInjected(): void
    {
        $kernel = $this->getKernel([__DIR__ . '/../Fixtures/config/authorization_roles_permissions.yaml']);
        /** @var \EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixFactoryInterface $authorizationMatrixFactory */
        $authorizationMatrixFactory = $kernel->getContainer()
            ->get(AuthorizationMatrixFactoryInterface::class);
        $authorizationMatrix = $authorizationMatrixFactory->create();

        self::assertTrue($authorizationMatrix->isRole('role'));
        self::assertTrue($authorizationMatrix->isPermission('permission'));
    }
}
