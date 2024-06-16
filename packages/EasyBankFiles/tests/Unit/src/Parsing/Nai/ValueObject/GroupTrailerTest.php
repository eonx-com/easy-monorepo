<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\Nai\ValueObject;

use EonX\EasyBankFiles\Parsing\Nai\ValueObject\GroupTrailer;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GroupTrailer::class)]
final class GroupTrailerTest extends AbstractUnitTestCase
{
    /**
     * Result should return data as expected.
     */
    public function testGetDataAsExpected(): void
    {
        $data = [
            'code' => '98',
            'groupControlTotalA' => '10000',
            'groupControlTotalB' => '10000',
            'numberOfAccounts' => '4',
        ];

        $trailer = new GroupTrailer($data);

        self::assertSame($data['code'], $trailer->getCode());
        self::assertSame((float)100, $trailer->getGroupControlTotalA());
        self::assertSame((float)100, $trailer->getGroupControlTotalB());
        self::assertSame($data['numberOfAccounts'], $trailer->getNumberOfAccounts());
    }
}
