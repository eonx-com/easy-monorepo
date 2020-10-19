<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\Bpay\Brf;

use DateTime;
use EonX\EasyBankFiles\Parsers\Bpay\Brf\Results\Transaction;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;

final class TransactionTest extends TestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestAmount(): iterable
    {
        yield 'Big number' => ['000000500025', 5000.25];
        yield 'Do not drop zero in decimals' => ['000000000101', 1.01];
        yield 'Less than zero' => ['000000000001', 0.01];
    }

    /**
     * Should return transaction amount
     *
     * @param string $amount
     * @param float $expected
     *
     * @dataProvider providerTestAmount
     *
     * @group Bpay-Transaction
     */
    public function testShouldReturnAmount(string $amount, float $expected): void
    {
        $transaction = new Transaction([
            'amount' => $amount,
        ]);

        self::assertSame($expected, $transaction->getAmount());
    }

    /**
     * Should return payment date as DateTime object
     *
     * @group Bpay-Transaction
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException If datetime constructor string is invalid
     */
    public function testShouldReturnPaymentDate(): void
    {
        $transaction = new Transaction([
            'paymentDate' => '20160426',
        ]);

        self::assertInstanceOf(DateTime::class, $transaction->getPaymentDate());
    }

    /**
     * Should return settlement date as DateTime object
     *
     * @group Bpay-Transaction
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException If datetime constructor string is invalid
     */
    public function testShouldReturnSettlementDate(): void
    {
        $transaction = new Transaction([
            'settlementDate' => '20160426',
        ]);

        self::assertInstanceOf(DateTime::class, $transaction->getSettlementDate());
    }
}
