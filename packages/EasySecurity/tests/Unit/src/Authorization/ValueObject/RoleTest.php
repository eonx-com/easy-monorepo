<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\Authorization\ValueObject;

use EonX\EasySecurity\Authorization\ValueObject\Permission;
use EonX\EasySecurity\Authorization\ValueObject\Role;
use EonX\EasySecurity\Tests\Unit\AbstractUnitTestCase;
use stdClass;

final class RoleTest extends AbstractUnitTestCase
{
    public function testRole(): void
    {
        $role = new Role('app:role', [new Permission('perm'), 'perm-as-string', new stdClass()]);

        self::assertEquals('app:role', $role->getIdentifier());
        self::assertEquals('app:role', (string)$role);
        self::assertCount(2, $role->getPermissions());
        self::assertNull($role->getName());

        // Test metadata
        self::assertEmpty($role->getMetadata());
        self::assertFalse($role->hasMetadata('metadata'));

        $role->addMetadata('metadata', true);
        self::assertTrue($role->hasMetadata('metadata'));

        $role->removeMetadata('metadata');
        self::assertFalse($role->hasMetadata('metadata'));

        $metadata = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
        $role->setMetadata($metadata);
        self::assertEquals($metadata, $role->getMetadata());
        self::assertEquals('value1', $role->getMetadata('key1'));
    }
}
