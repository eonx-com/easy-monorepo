<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\Nai\ValueObject;

use EonX\EasyBankFiles\Parsing\Nai\ValueObject\GroupHeader;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GroupHeader::class)]
final class GroupHeaderTest extends AbstractUnitTestCase
{
    /**
     * Result should return data as expected.
     */
    public function testGetDataAsExpected(): void
    {
        $data = [
            'asOfDate' => '180625',
            'asOfTime' => '0000',
            'code' => '02',
            'groupStatus' => '1',
            'originatorReceiverId' => 'original-receiver-id',
            'ultimateReceiverId' => 'ultimate-receiver-id',
        ];

        $header = new GroupHeader($data);

        self::assertSame($data['asOfDate'], $header->getAsOfDate());
        self::assertSame($data['asOfTime'], $header->getAsOfTime());
        self::assertSame($data['code'], $header->getCode());
        self::assertSame($data['groupStatus'], $header->getGroupStatus());
        self::assertSame($data['originatorReceiverId'], $header->getOriginatorReceiverId());
        self::assertSame($data['ultimateReceiverId'], $header->getUltimateReceiverId());
    }
}
