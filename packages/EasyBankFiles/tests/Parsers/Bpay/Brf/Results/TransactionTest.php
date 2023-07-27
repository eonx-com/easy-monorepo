<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\Bpay\Brf\Results;

use DateTime;
use EonX\EasyBankFiles\Parsers\Bpay\Brf\Results\Transaction;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;

/**
 * @covers \EonX\EasyBankFiles\Parsers\Bpay\Brf\Results\Transaction
 */
final class TransactionTest extends TestCase
{
    /**
     * @see testGetDateObjectShouldReturnNull
     */
    public static function provideInvalidDates(): iterable
    {
        yield 'null paymentDate' => [
            'date' => [
                'paymentDate' => null,
            ],
            'dateGetter' => 'getPaymentDateObject',
        ];
        yield 'paymentDate has non-digital symbols' => [
            'date' => [
                'paymentDate' => '201909ab',
            ],
            'dateGetter' => 'getPaymentDateObject',
        ];
        yield 'null settlementDate' => [
            'date' => [
                'settlementDate' => null,
            ],
            'dateGetter' => 'getSettlementDateObject',
        ];
        yield 'settlementDate has non-digital symbols' => [
            'date' => [
                'settlementDate' => '201909ab',
            ],
            'dateGetter' => 'getSettlementDateObject',
        ];
    }

    /**
     * Should return date as a null when date string is invalid.
     *
     * @group Brf-Transaction
     *
     * @dataProvider provideInvalidDates
     */
    public function testGetDateObjectShouldReturnNull(array $date, string $dateGetter): void
    {
        $transaction = new Transaction($date);

        self::assertNull($transaction->{$dateGetter}());
    }

    /**
     * Should return payment date as DateTime object.
     *
     * @group Brf-Transaction
     */
    public function testShouldReturnPaymentDate(): void
    {
        $transaction = new Transaction([
            'paymentDate' => '20160426',
        ]);

        self::assertInstanceOf(DateTime::class, $transaction->getPaymentDateObject());
    }

    /**
     * Should return settlement date as DateTime object.
     *
     * @group Brf-Transaction
     */
    public function testShouldReturnSettlementDate(): void
    {
        $transaction = new Transaction([
            'settlementDate' => '20160426',
        ]);

        self::assertInstanceOf(DateTime::class, $transaction->getSettlementDateObject());
    }
}
