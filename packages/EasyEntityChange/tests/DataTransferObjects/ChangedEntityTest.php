<?php
declare(strict_types=1);

namespace EonX\EasyEntityChange\Tests\DataTransferObjects;

use EonX\EasyEntityChange\DataTransferObjects\UpdatedEntity;
use EonX\EasyEntityChange\Tests\AbstractTestCase;
use stdClass;

/**
 * @covers \EonX\EasyEntityChange\DataTransferObjects\ChangedEntity
 */
class ChangedEntityTest extends AbstractTestCase
{
    /**
     * Tests that DTO's constructor and getters are :chefs-kiss:
     *
     * @return void
     */
    public function testDtoCreationAndGetters(): void
    {
        $dto = new UpdatedEntity(
            [],
            stdClass::class,
            ['id']
        );

        self::assertSame(stdClass::class, $dto->getClass());
        self::assertSame(['id'], $dto->getIds());
    }
}
