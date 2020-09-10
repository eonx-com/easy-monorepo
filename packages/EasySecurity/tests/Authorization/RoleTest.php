<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Authorization;

use EonX\EasySecurity\Authorization\Permission;
use EonX\EasySecurity\Authorization\Role;
use EonX\EasySecurity\Tests\AbstractTestCase;

final class RoleTest extends AbstractTestCase
{
    public function testRole(): void
    {
        $role = new Role('app:role', [new Permission('perm'), 'perm-as-string', new \stdClass()]);

        self::assertEquals('app:role', $role->getIdentifier());
        self::assertEquals('app:role', (string)$role);
        self::assertCount(2, $role->getPermissions());
        self::assertEmpty($role->getMetadata());
        self::assertNull($role->getName());
    }
}
