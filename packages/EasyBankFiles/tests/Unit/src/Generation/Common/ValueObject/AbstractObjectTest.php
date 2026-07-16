<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Generation\Common\ValueObject;

use EonX\EasyBankFiles\Tests\Stub\ValueObject\ObjectStub;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Group;

final class AbstractObjectTest extends AbstractUnitTestCase
{
    /**
     * Should return all attributes.
     */
    #[Group('Generator-BaseObject')]
    public function testShouldReturnAttributes(): void
    {
        $data = [
            'accountName' => 'John Doe',
            'accountNumber' => '11-222-33',
        ];

        $object = new ObjectStub($data);

        self::assertSame($data, $object->getAttributes());
    }
}
