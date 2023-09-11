<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\Nai\Results\Accounts;

use EonX\EasyBankFiles\Parsers\Nai\Results\Accounts\Trailer;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Trailer::class)]
final class TrailerTest extends TestCase
{
    /**
     * Result should return data as expected.
     */
    public function testGetDataAsExpected(): void
    {
        $data = [
            'code' => '03',
            'accountControlTotalA' => '10000',
            'accountControlTotalB' => '10000',
        ];

        $trailer = new Trailer($data);

        self::assertSame($data['code'], $trailer->getCode());
        self::assertSame((float)100, $trailer->getAccountControlTotalA());
        self::assertSame((float)100, $trailer->getAccountControlTotalB());
    }
}
