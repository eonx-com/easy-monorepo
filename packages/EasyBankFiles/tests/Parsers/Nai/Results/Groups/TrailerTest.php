<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\Nai\Results\Groups;

use EonX\EasyBankFiles\Parsers\Nai\Results\Groups\Trailer;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;

/**
 * @covers \EonX\EasyBankFiles\Parsers\Nai\Results\Groups\Trailer
 */
final class TrailerTest extends TestCase
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

        $trailer = new Trailer($data);

        self::assertSame($data['code'], $trailer->getCode());
        self::assertSame((float)100, $trailer->getGroupControlTotalA());
        self::assertSame((float)100, $trailer->getGroupControlTotalB());
        self::assertSame($data['numberOfAccounts'], $trailer->getNumberOfAccounts());
    }
}
