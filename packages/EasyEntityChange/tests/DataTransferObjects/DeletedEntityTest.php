<?php
declare(strict_types=1);

namespace EonX\EasyEntityChange\Tests\DataTransferObjects;

use EonX\EasyEntityChange\DataTransferObjects\DeletedEntity;
use EonX\EasyEntityChange\Tests\AbstractTestCase;
use stdClass;

/**
 * @covers \EonX\EasyEntityChange\DataTransferObjects\DeletedEntity
 */
class DeletedEntityTest extends AbstractTestCase
{
    /**
     * Tests that DTO's constructor and getters are :chefs-kiss:
     *
     * @return void
     */
    public function testDtoCreationAndGetters(): void
    {
        $dto = new DeletedEntity(
            stdClass::class,
            ['id'],
            ['metadata']
        );

        self::assertSame(['metadata'], $dto->getMetadata());
    }
}
