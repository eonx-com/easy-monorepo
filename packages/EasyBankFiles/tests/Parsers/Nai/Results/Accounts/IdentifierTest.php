<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\Nai\Results\Accounts;

use EonX\EasyBankFiles\Parsers\Nai\Results\Accounts\Identifier;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;

/**
 * @covers \EonX\EasyBankFiles\Parsers\Nai\Results\Accounts\Identifier
 */
final class IdentifierTest extends TestCase
{
    /**
     * Result should return data as expected.
     */
    public function testGetDataAsExpected(): void
    {
        $data = [
            'code' => '03',
            'commercialAccountNumber' => 'account-number',
            'currencyCode' => 'AUD',
            'transactionCodes' => [],
        ];

        $identifier = new Identifier($data);

        self::assertSame($data['code'], $identifier->getCode());
        self::assertSame($data['commercialAccountNumber'], $identifier->getCommercialAccountNumber());
        self::assertSame($data['currencyCode'], $identifier->getCurrencyCode());
        self::assertSame($data['transactionCodes'], $identifier->getTransactionCodes());
    }
}
