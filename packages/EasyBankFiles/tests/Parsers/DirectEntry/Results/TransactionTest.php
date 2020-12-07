<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\DirectEntry\Results;

use EonX\EasyBankFiles\Parsers\DirectEntry\Results\Transaction;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;

/**
 * @covers \EonX\EasyBankFiles\Parsers\DirectEntry\Results\Transaction
 */
final class TransactionTest extends TestCase
{
    /**
     * Test if amount conversion works as expected.
     */
    public function testAmountConversion(): void
    {
        $transaction = new Transaction([
            'amount' => '0000000000',
        ]);

        self::assertSame('0.00', $transaction->getAmount());
    }

    /**
     * Check if amount conversion works if 8 digit withholding tax provided.
     */
    public function testAmountConversionWorksOn8Digit(): void
    {
        $transaction = new Transaction([
            'withholdingTax' => '00000890',
        ]);

        self::assertSame('8.90', $transaction->getWithholdingTax());
    }
}
