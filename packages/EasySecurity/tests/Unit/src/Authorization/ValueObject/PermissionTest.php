<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\Authorization\ValueObject;

use EonX\EasySecurity\Authorization\ValueObject\Permission;
use EonX\EasySecurity\Tests\Unit\AbstractUnitTestCase;

final class PermissionTest extends AbstractUnitTestCase
{
    public function testPermission(): void
    {
        $permission = new Permission('perm');

        self::assertEquals('perm', $permission->getIdentifier());
        self::assertEquals('perm', (string)$permission);
    }
}
