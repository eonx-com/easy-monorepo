<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\Common\Context;

use EonX\EasyApiToken\Common\ValueObject\ApiKey;
use EonX\EasySecurity\Authorization\Provider\AuthorizationMatrixProvider;
use EonX\EasySecurity\Authorization\ValueObject\Permission;
use EonX\EasySecurity\Authorization\ValueObject\Role;
use EonX\EasySecurity\Common\Context\SecurityContext;
use EonX\EasySecurity\Common\Exception\NoProviderInContextException;
use EonX\EasySecurity\Common\Exception\NoUserInContextException;
use EonX\EasySecurity\Tests\Stub\Entity\ProviderStub;
use EonX\EasySecurity\Tests\Stub\Entity\UserStub;
use EonX\EasySecurity\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;

final class SecurityContextTest extends AbstractUnitTestCase
{
    /**
     * @see testContextGetters
     */
    public static function gettersDataProvider(): iterable
    {
        yield '1 role 2 permissions' => [
            [new Role('app:role', [new Permission('perm1'), new Permission('perm2')])],
            1,
            2,
        ];

        yield '2 roles 3 permissions because duplicates' => [
            [
                new Role('app:role', [new Permission('perm1'), new Permission('perm2')]),
                new Role('app:role1', [new Permission('perm1'), new Permission('perm3')]),
            ],
            2,
            3,
        ];

        yield '1 role 1 permission because non role given' => [
            [new Role('app:role', [new Permission('perm1')]), new stdClass()],
            1,
            1,
        ];

        yield '2 roles 1 permission because string role given' => [
            [new Role('app:role', [new Permission('perm1')]), 'string:role'],
            2,
            1,
        ];
    }

    /**
     * @see testContextHas
     */
    public static function hasDataProvider(): iterable
    {
        yield 'No role No permission' => [
            [new Role('app:role', [new Permission('perm1')])],
            'app:role1',
            'perm2',
            false,
            false,
        ];

        yield 'Yes role No permission' => [
            [new Role('app:role', [new Permission('perm1')])],
            'app:role',
            'perm2',
            true,
            false,
        ];

        yield 'No role Yes permission' => [
            [new Role('app:role1', [new Permission('perm2')])],
            'app:role',
            'perm2',
            false,
            true,
        ];

        yield 'Yes role Yes permission' => [
            [new Role('app:role', [new Permission('perm1')])],
            'app:role',
            'perm1',
            true,
            true,
        ];

        yield 'Yes role Yes permission with multiple roles' => [
            [new Role('app:role', [new Permission('perm1')]), new Role('app:role1', [new Permission('perm2')])],
            'app:role',
            'perm2',
            true,
            true,
        ];
    }

    public function testContextGetProviderOrFail(): void
    {
        $this->expectException(NoProviderInContextException::class);

        new SecurityContext()
            ->getProviderOrFail();
    }

    public function testContextGetUserOrFail(): void
    {
        $this->expectException(NoUserInContextException::class);

        new SecurityContext()
            ->getUserOrFail();
    }

    #[DataProvider('gettersDataProvider')]
    public function testContextGetters(array $roles, int $countRoles, int $countPermissions): void
    {
        $token = new ApiKey('api-key');
        $provider = new ProviderStub('uniqueId');
        $user = new UserStub('uniqueId');

        $authorizationMatrix = new AuthorizationMatrixProvider($roles, []);

        $context = new SecurityContext();
        $context->setAuthorizationMatrix($authorizationMatrix);
        $context->setToken($token);
        $context->setProvider($provider);
        $context->setRoles($roles);
        $context->setUser($user);
        $permissions = $context->getPermissions();

        // Override permissions
        $context->setPermissions(['my-permission']);

        self::assertCount($countRoles, $context->getRoles());
        self::assertCount($countPermissions, $permissions);
        self::assertCount(1, $context->getPermissions());
        self::assertEquals($token, $context->getToken());
        self::assertEquals($provider, $context->getProvider());
        self::assertEquals($provider, $context->getProviderOrFail());
        self::assertEquals($user, $context->getUser());
        self::assertEquals($user, $context->getUserOrFail());
    }

    #[DataProvider('hasDataProvider')]
    public function testContextHas(
        array $roles,
        string $role,
        string $permission,
        bool $hasRole,
        bool $hasPermission,
    ): void {
        $authorizationMatrix = new AuthorizationMatrixProvider($roles, []);

        $context = new SecurityContext();
        $context->setAuthorizationMatrix($authorizationMatrix);
        $context->setRoles($roles);

        self::assertEquals($hasRole, $context->hasRole($role));
        self::assertEquals($hasPermission, $context->hasPermission($permission));

        $hasRole
            ? self::assertInstanceOf(Role::class, $context->getRole($role))
            : self::assertNull($context->getRole($role));

        $hasPermission
            ? self::assertInstanceOf(Permission::class, $context->getPermission($permission))
            : self::assertNull($context->getPermission($permission));
    }
}
