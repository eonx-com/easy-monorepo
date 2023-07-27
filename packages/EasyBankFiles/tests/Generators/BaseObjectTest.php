<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Generators;

use EonX\EasyBankFiles\Tests\Generators\Stubs\ObjectStub;

final class BaseObjectTest extends TestCase
{
    /**
     * Should return all attributes.
     *
     * @group Generator-BaseObject
     */
    public function testShouldReturnAttributes(): void
    {
        $data = [
            'accountName' => 'John Doe',
            'accountNumber' => '11-222-33',
        ];

        $object = new ObjectStub($data);

        self::assertIsArray($object->getAttributes());
        self::assertSame($data, $object->getAttributes());
    }
}
