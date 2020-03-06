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
    public function testDtoCreationAndGetters(): void
    {
        $dto = new DeletedEntity(
            stdClass::class,
            ['id'],
            ['metadata']
        );

        self::assertEquals(['metadata'], $dto->getMetadata());
    }
}
