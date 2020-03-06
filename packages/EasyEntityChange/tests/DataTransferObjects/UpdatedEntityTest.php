<?php
declare(strict_types=1);

namespace EonX\EasyEntityChange\Tests\DataTransferObjects;

use EonX\EasyEntityChange\DataTransferObjects\UpdatedEntity;
use EonX\EasyEntityChange\Tests\AbstractTestCase;
use stdClass;

/**
 * @covers \EonX\EasyEntityChange\DataTransferObjects\UpdatedEntity
 */
class UpdatedEntityTest extends AbstractTestCase
{
    public function testDtoCreationAndGetters(): void
    {
        $dto = new UpdatedEntity(
            ['changed'],
            stdClass::class,
            ['id']
        );

        self::assertEquals(['changed'], $dto->getChangedProperties());
    }
}
