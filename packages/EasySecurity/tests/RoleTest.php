<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests;

use EonX\EasySecurity\Permission;
use EonX\EasySecurity\Role;

final class RoleTest extends AbstractTestCase
{
    /**
     * Test role.
     *
     * @return void
     */
    public function testRole(): void
    {
        $role = new Role('app:role', [
            new Permission('perm'),
            'non-permission'
        ]);

        self::assertEquals('app:role', $role->getIdentifier());
        self::assertEquals('app:role', (string)$role);
        self::assertCount(1, $role->getPermissions());
        self::assertEmpty($role->getMetadata());
        self::assertNull($role->getName());
    }
}
