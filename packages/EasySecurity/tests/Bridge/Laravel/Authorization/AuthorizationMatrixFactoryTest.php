<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Laravel\Authorization;

use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Tests\Bridge\Laravel\AbstractLumenTestCase;
use EonX\EasySecurity\Tests\Bridge\Laravel\Fixtures\Providers\RolesAndPermissionsServiceProvider;

final class AuthorizationMatrixFactoryTest extends AbstractLumenTestCase
{
    public function testRolesAndPermissionsProvidersInjected(): void
    {
        $app = $this->getApplication([RolesAndPermissionsServiceProvider::class]);

        /** @var \EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixFactoryInterface $authorizationMatrixFactory */
        $authorizationMatrixFactory = $app->get(AuthorizationMatrixFactoryInterface::class);
        $authorizationMatrix = $authorizationMatrixFactory->create();

        self::assertTrue($authorizationMatrix->isRole('role'));
        self::assertTrue($authorizationMatrix->isPermission('permission'));
    }
}
