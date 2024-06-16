<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\Brf\ValueObject;

use DateTime;
use EonX\EasyBankFiles\Parsing\Brf\ValueObject\Transaction;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[CoversClass(Transaction::class)]
final class TransactionTest extends AbstractUnitTestCase
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
     */
    #[DataProvider('provideInvalidDates')]
    #[Group('Brf-Transaction')]
    public function testGetDateObjectShouldReturnNull(array $date, string $dateGetter): void
    {
        $transaction = new Transaction($date);

        self::assertNull($transaction->{$dateGetter}());
    }

    /**
     * Should return payment date as DateTime object.
     */
    #[Group('Brf-Transaction')]
    public function testShouldReturnPaymentDate(): void
    {
        $transaction = new Transaction([
            'paymentDate' => '20160426',
        ]);

        self::assertInstanceOf(DateTime::class, $transaction->getPaymentDateObject());
    }

    /**
     * Should return settlement date as DateTime object.
     */
    #[Group('Brf-Transaction')]
    public function testShouldReturnSettlementDate(): void
    {
        $transaction = new Transaction([
            'settlementDate' => '20160426',
        ]);

        self::assertInstanceOf(DateTime::class, $transaction->getSettlementDateObject());
    }
}
