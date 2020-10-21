<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\Bpay\Batch;

use EonX\EasyBankFiles\Parsers\Bpay\Batch\Results\Transaction;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;

final class TransactionTest extends TestCase
{
    /**
     * Should return transaction amount as a decimal
     *
     * @group Batch-Transaction
     */
    public function testShouldReturnAmountDecimal(): void
    {
        $transaction = new Transaction([
            'amount' => '0000000050028',
        ]);

        self::assertSame('500.28', $transaction->getAmountDecimal());
    }

    /**
     * Should return transaction amount
     *
     * @group Batch-Transaction
     */
    public function testShouldReturnAmountDecimalNull(): void
    {
        $transaction = new Transaction();

        self::assertNull($transaction->getAmountDecimal());
    }
}
