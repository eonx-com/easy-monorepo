<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests;

use EonX\EasySecurity\Permission;

final class PermissionTest extends AbstractTestCase
{
    /**
     * Test permission.
     *
     * @return void
     */
    public function testPermission(): void
    {
        $permission = new Permission('perm');

        self::assertEquals('perm', $permission->getIdentifier());
        self::assertEquals('perm', (string)$permission);
    }
}
