<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests;

use EonX\EasySecurity\Permission;
use EonX\EasySecurity\Role;

final class ContextTest extends AbstractTestCase
{
    /**
     * Data provider for testContextGetters.
     *
     * @return iterable<mixed>
     */
    public function gettersDataProvider(): iterable
    {
        yield '1 role 2 permissions' => [
            [
                new Role('app:role', [
                    new Permission('perm1'),
                    new Permission('perm2')
                ])
            ],
            1,
            2
        ];

        yield '2 roles 3 permissions because duplicates' => [
            [
                new Role('app:role', [
                    new Permission('perm1'),
                    new Permission('perm2')
                ]),
                new Role('app:role1', [
                    new Permission('perm1'),
                    new Permission('perm3')
                ])
            ],
            2,
            3
        ];

        yield '1 role 1 permission because non role given' => [
            [
                new Role('app:role', [
                    new Permission('perm1')
                ]),
                'non-role'
            ],
            1,
            1
        ];
    }

    /**
     * Data provider for testContextHas.
     *
     * @return iterable<mixed>
     */
    public function hasDataProvider(): iterable
    {
        yield 'No role No permission' => [
            [
                new Role('app:role', [
                    new Permission('perm1')
                ])
            ],
            'app:role1',
            'perm2',
            false,
            false
        ];

        yield 'Yes role No permission' => [
            [
                new Role('app:role', [
                    new Permission('perm1')
                ])
            ],
            'app:role',
            'perm2',
            true,
            false
        ];

        yield 'No role Yes permission' => [
            [
                new Role('app:role1', [
                    new Permission('perm2')
                ])
            ],
            'app:role',
            'perm2',
            false,
            true
        ];

        yield 'Yes role Yes permission' => [
            [
                new Role('app:role', [
                    new Permission('perm1')
                ])
            ],
            'app:role',
            'perm1',
            true,
            true
        ];

        yield 'Yes role Yes permission with multiple roles' => [
            [
                new Role('app:role', [
                    new Permission('perm1')
                ]),
                new Role('app:role1', [
                    new Permission('perm2')
                ])
            ],
            'app:role',
            'perm2',
            true,
            true
        ];
    }

    /**
     * Test context getters.
     *
     * @param mixed[] $roles
     * @param int $countRoles
     * @param int $countPermissions
     *
     * @return void
     *
     * @dataProvider gettersDataProvider
     */
    public function testContextGetters(array $roles, int $countRoles, int $countPermissions): void
    {
        $context = new ContextStub($roles);
        $permissions = $context->getPermissions();

        self::assertCount($countRoles, $context->getRoles());
        self::assertCount($countPermissions, $permissions);
        self::assertEquals($permissions, $context->getPermissions());
    }

    /**
     * Test context has methods.
     *
     * @param mixed[] $roles
     * @param string $role
     * @param string $permission
     * @param bool $hasRole
     * @param bool $hasPermission
     *
     * @return void
     *
     * @dataProvider hasDataProvider
     */
    public function testContextHas(
        array $roles,
        string $role,
        string $permission,
        bool $hasRole,
        bool $hasPermission
    ): void {
        $context = new ContextStub($roles);

        self::assertEquals($hasRole, $context->hasRole($role));
        self::assertEquals($hasPermission, $context->hasPermission($permission));
    }
}
