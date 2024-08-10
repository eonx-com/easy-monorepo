<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\Nai\ValueObject;

use EonX\EasyBankFiles\Parsing\Nai\ValueObject\Group;
use EonX\EasyBankFiles\Parsing\Nai\ValueObject\GroupHeader;
use EonX\EasyBankFiles\Parsing\Nai\ValueObject\GroupTrailer;
use EonX\EasyBankFiles\Parsing\Nai\ValueObject\ResultsContext;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Group::class)]
final class GroupTest extends AbstractUnitTestCase
{
    /**
     * Result should return data as expected.
     */
    public function testGetDataAsExpected(): void
    {
        $data = [
            'header' => new GroupHeader(),
            'index' => 2,
            'trailer' => new GroupTrailer(),
        ];

        $group = new Group(new ResultsContext([], [], [], [], []), $data);

        self::assertIsArray($group->getAccounts());
        self::assertNull($group->getFile());
        self::assertInstanceOf(GroupHeader::class, $group->getHeader());
        self::assertInstanceOf(GroupTrailer::class, $group->getTrailer());
    }
}
