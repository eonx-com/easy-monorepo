<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Unit\Parsing\Brf\ValueObject;

use DateTime;
use EonX\EasyBankFiles\Parsing\Brf\ValueObject\DetailRecord;
use EonX\EasyBankFiles\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[CoversClass(DetailRecord::class)]
final class DetailRecordTest extends AbstractUnitTestCase
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
        $transaction = new DetailRecord($date);

        self::assertNull($transaction->{$dateGetter}());
    }

    /**
     * Should return payment date as DateTime object.
     */
    #[Group('Brf-Transaction')]
    public function testShouldReturnPaymentDate(): void
    {
        $transaction = new DetailRecord([
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
        $transaction = new DetailRecord([
            'settlementDate' => '20160426',
        ]);

        self::assertInstanceOf(DateTime::class, $transaction->getSettlementDateObject());
    }
}
