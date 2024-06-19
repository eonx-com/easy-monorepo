<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\Nai\ValueObject;

use EonX\EasyBankFiles\Parsing\Nai\ValueObject\AccountIdentifier;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AccountIdentifier::class)]
final class AccountIdentifierTest extends AbstractUnitTestCase
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

        $identifier = new AccountIdentifier($data);

        self::assertSame($data['code'], $identifier->getCode());
        self::assertSame($data['commercialAccountNumber'], $identifier->getCommercialAccountNumber());
        self::assertSame($data['currencyCode'], $identifier->getCurrencyCode());
        self::assertSame($data['transactionCodes'], $identifier->getTransactionCodes());
    }
}
