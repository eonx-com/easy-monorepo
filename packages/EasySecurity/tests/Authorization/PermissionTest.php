<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Authorization;

use EonX\EasySecurity\Authorization\Permission;
use EonX\EasySecurity\Tests\AbstractTestCase;

final class PermissionTest extends AbstractTestCase
{
    public function testPermission(): void
    {
        $permission = new Permission('perm');

        self::assertEquals('perm', $permission->getIdentifier());
        self::assertEquals('perm', (string)$permission);
    }
}
