<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\RolesProviders;

use EonX\EasySecurity\Role;
use EonX\EasySecurity\RolesProviders\InMemoryRolesProvider;
use EonX\EasySecurity\Tests\AbstractTestCase;

final class InMemoryRolesProviderTest extends AbstractTestCase
{
    /**
     * Data provider for getRolesByIdentifiers tests.
     *
     * @return iterable<mixed>
     */
    public function getRolesByIdentifiersDataProvider(): iterable
    {
        yield 'Zero role in provider' => [
            [],
            'app:role',
            0
        ];

        yield 'One match using string identifier' => [
            [new Role('app:role', [])],
            'app:role',
            1
        ];

        yield 'One match using array identifier' => [
            [new Role('app:role', [])],
            ['app:role'],
            1
        ];

        yield 'Only one match for two identifiers' => [
            [new Role('app:role', [])],
            ['app:role', 'app:role1'],
            1
        ];

        yield 'Multiple matches' => [
            [new Role('app:role', []), new Role('app:role1', [])],
            ['app:role', 'app:role1'],
            2
        ];
    }

    /**
     * Data provider for getRoles tests.
     *
     * @return iterable<mixed>
     */
    public function getRolesDataProvider(): iterable
    {
        yield 'Zero role' => [
            [],
            0
        ];

        yield 'One role' => [
            [new Role('app:role', [])],
            1
        ];

        yield 'Two roles' => [
            [new Role('app:role1', []), new Role('app:role2', [])],
            2
        ];

        yield 'Three roles but only two instances' => [
            [new Role('app:role1', []), new Role('app:role2', []), 'non-role'],
            2
        ];
    }

    /**
     * Test getRoles method.
     *
     * @param mixed[] $roles
     * @param int $count
     *
     * @return void
     *
     * @dataProvider getRolesDataProvider
     */
    public function testGetRoles(array $roles, int $count): void
    {
        $provider = new InMemoryRolesProvider($roles);

        self::assertCount($count, $provider->getRoles());
    }

    /**
     * Test getRolesByIdentifiers method.
     *
     * @param mixed[] $roles
     * @param string|string[] $identifiers
     * @param int $count
     *
     * @return void
     *
     * @dataProvider getRolesByIdentifiersDataProvider
     */
    public function testGetRolesByIdentifiers(array $roles, $identifiers, int $count): void
    {
        $provider = new InMemoryRolesProvider($roles);

        self::assertCount($count, $provider->getRolesByIdentifiers($identifiers));
    }
}
