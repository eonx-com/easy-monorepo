<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\Authorization\Provider;

use EonX\EasySecurity\Authorization\Factory\AuthorizationMatrixFactory;
use EonX\EasySecurity\Authorization\Provider\AuthorizationMatrixProvider;
use EonX\EasySecurity\Authorization\ValueObject\Permission;
use EonX\EasySecurity\Authorization\ValueObject\Role;
use EonX\EasySecurity\Tests\Stub\Provider\AuthorizationPermissionsProviderStub;
use EonX\EasySecurity\Tests\Stub\Provider\AuthorizationRolesProviderStub;
use EonX\EasySecurity\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class AuthorizationMatrixProviderTest extends AbstractUnitTestCase
{
    /**
     * @see testMatrix
     */
    public static function provideMatrixData(): iterable
    {
        yield 'Empty roles and permissions' => [
            static function (AuthorizationMatrixProvider $matrix): void {
                self::assertEmpty($matrix->getRoles());
                self::assertEmpty($matrix->getPermissions());
                self::assertEmpty($matrix->getRolesByIdentifiers(['role']));
                self::assertEmpty($matrix->getPermissionsByIdentifiers(['permission']));
                self::assertFalse($matrix->isRole('role'));
                self::assertFalse($matrix->isPermission('permission'));
            },
        ];

        yield '1 role with 1 permission' => [
            static function (AuthorizationMatrixProvider $matrix): void {
                self::assertCount(1, $matrix->getRoles());
                self::assertCount(1, $matrix->getPermissions());
                self::assertCount(1, $matrix->getRolesByIdentifiers(['role']));
                self::assertCount(1, $matrix->getPermissionsByIdentifiers(['permission']));
                self::assertTrue($matrix->isRole('role'));
                self::assertTrue($matrix->isPermission('permission'));
            },
            [new Role('role', [new Permission('permission')])],
        ];

        yield '2 roles with 1 permission + 1 permission' => [
            static function (AuthorizationMatrixProvider $matrix): void {
                self::assertCount(2, $matrix->getRoles());
                self::assertCount(3, $matrix->getPermissions());
                self::assertCount(1, $matrix->getRolesByIdentifiers(['role1']));
                self::assertCount(1, $matrix->getPermissionsByIdentifiers(['permission1']));
                self::assertTrue($matrix->isRole('role2'));
                self::assertTrue($matrix->isPermission('permission3'));
            },
            [
                new Role('role1', [new Permission('permission1')]),
                new Role('role2', [new Permission('permission2')]),
            ],
            [new Permission('permission3')],
        ];
    }

    /**
     * @param string[]|\EonX\EasySecurity\Authorization\ValueObject\RoleInterface[]|null $roles
     * @param string[]|\EonX\EasySecurity\Authorization\ValueObject\PermissionInterface[]|null $permissions
     */
    #[DataProvider('provideMatrixData')]
    public function testMatrix(callable $test, ?array $roles = null, ?array $permissions = null): void
    {
        $factory = new AuthorizationMatrixFactory(
            [new AuthorizationRolesProviderStub($roles)],
            [new AuthorizationPermissionsProviderStub($permissions)]
        );

        $test($factory->create());
    }
}
