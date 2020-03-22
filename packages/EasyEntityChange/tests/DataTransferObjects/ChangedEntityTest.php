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
    public function testDtoCreationAndGetters(): void
    {
        $dto = new UpdatedEntity(
            [],
            stdClass::class,
            ['id']
        );

        self::assertEquals(stdClass::class, $dto->getClass());
        self::assertEquals(['id'], $dto->getIds());
    }
}
