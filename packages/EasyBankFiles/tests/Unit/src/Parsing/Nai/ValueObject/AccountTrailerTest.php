<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\Nai\ValueObject;

use EonX\EasyBankFiles\Parsing\Nai\ValueObject\AccountTrailer;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AccountTrailer::class)]
final class AccountTrailerTest extends AbstractUnitTestCase
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

        $trailer = new AccountTrailer($data);

        self::assertSame($data['code'], $trailer->getCode());
        self::assertSame((float)100, $trailer->getAccountControlTotalA());
        self::assertSame((float)100, $trailer->getAccountControlTotalB());
    }
}
