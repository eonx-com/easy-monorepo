<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests;

use EonX\EasyApiToken\Tokens\ApiKeyEasyApiToken;
use EonX\EasySecurity\Context;
use EonX\EasySecurity\Exceptions\NoProviderInContextException;
use EonX\EasySecurity\Exceptions\NoUserInContextException;
use EonX\EasySecurity\Permission;
use EonX\EasySecurity\Role;
use EonX\EasySecurity\Tests\Stubs\ProviderInterfaceStub;
use EonX\EasySecurity\Tests\Stubs\UserInterfaceStub;

final class ContextTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function gettersDataProvider(): iterable
    {
        yield '1 role 2 permissions' => [
            [
                new Role('app:role', [
                    new Permission('perm1'),
                    new Permission('perm2'),
                ]),
            ],
            1,
            2,
        ];

        yield '2 roles 3 permissions because duplicates' => [
            [
                new Role('app:role', [
                    new Permission('perm1'),
                    new Permission('perm2'),
                ]),
                new Role('app:role1', [
                    new Permission('perm1'),
                    new Permission('perm3'),
                ]),
            ],
            2,
            3,
        ];

        yield '1 role 1 permission because non role given' => [
            [
                new Role('app:role', [
                    new Permission('perm1'),
                ]),
                new \stdClass(),
            ],
            1,
            1,
        ];

        yield '2 roles 1 permission because string role given' => [
            [
                new Role('app:role', [
                    new Permission('perm1'),
                ]),
                'string:role',
            ],
            2,
            1,
        ];
    }

    /**
     * @return iterable<mixed>
     */
    public function hasDataProvider(): iterable
    {
        yield 'No role No permission' => [
            [
                new Role('app:role', [
                    new Permission('perm1'),
                ]),
            ],
            'app:role1',
            'perm2',
            false,
            false,
        ];

        yield 'Yes role No permission' => [
            [
                new Role('app:role', [
                    new Permission('perm1'),
                ]),
            ],
            'app:role',
            'perm2',
            true,
            false,
        ];

        yield 'No role Yes permission' => [
            [
                new Role('app:role1', [
                    new Permission('perm2'),
                ]),
            ],
            'app:role',
            'perm2',
            false,
            true,
        ];

        yield 'Yes role Yes permission' => [
            [
                new Role('app:role', [
                    new Permission('perm1'),
                ]),
            ],
            'app:role',
            'perm1',
            true,
            true,
        ];

        yield 'Yes role Yes permission with multiple roles' => [
            [
                new Role('app:role', [
                    new Permission('perm1'),
                ]),
                new Role('app:role1', [
                    new Permission('perm2'),
                ]),
            ],
            'app:role',
            'perm2',
            true,
            true,
        ];
    }

    public function testContextGetProviderOrFail(): void
    {
        $this->expectException(NoProviderInContextException::class);

        (new Context())->getProviderOrFail();
    }

    public function testContextGetUserOrFail(): void
    {
        $this->expectException(NoUserInContextException::class);

        (new Context())->getUserOrFail();
    }

    /**
     * @param mixed[] $roles
     *
     * @dataProvider gettersDataProvider
     */
    public function testContextGetters(array $roles, int $countRoles, int $countPermissions): void
    {
        $token = new ApiKeyEasyApiToken('api-key');
        $provider = new ProviderInterfaceStub('uniqueId');
        $user = new UserInterfaceStub('uniqueId');

        $context = new Context();
        $context->setToken($token);
        $context->setProvider($provider);
        $context->setRoles($roles);
        $context->setUser($user);
        $permissions = $context->getPermissions();

        self::assertCount($countRoles, $context->getRoles());
        self::assertCount($countPermissions, $permissions);
        self::assertEquals($permissions, $context->getPermissions());
        self::assertEquals($token, $context->getToken());
        self::assertEquals($provider, $context->getProvider());
        self::assertEquals($user, $context->getUser());
    }

    /**
     * @param mixed[] $roles
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
        $context = new Context();
        $context->setRoles($roles);

        self::assertEquals($hasRole, $context->hasRole($role));
        self::assertEquals($hasPermission, $context->hasPermission($permission));
    }
}
