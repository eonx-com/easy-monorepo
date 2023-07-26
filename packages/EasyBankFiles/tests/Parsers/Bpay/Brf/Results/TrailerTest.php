<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers\Bpay\Brf\Results;

use EonX\EasyBankFiles\Parsers\Bpay\Brf\Exceptions\InvalidSignFieldException;
use EonX\EasyBankFiles\Parsers\Bpay\Brf\Results\Trailer;
use EonX\EasyBankFiles\Tests\Parsers\TestCase;

/**
 * @covers \EonX\EasyBankFiles\Parsers\Bpay\Brf\Results\Trailer
 */
final class TrailerTest extends TestCase
{
    /**
     * Should return amount of error corrections.
     *
     * @throws \EonX\EasyBankFiles\Parsers\Bpay\Brf\Exceptions\InvalidSignFieldException
     *
     * @group Brf-Trailer
     */
    public function testShouldReturnAmountOfErrorCorrections(): void
    {
        $expected = [
            'amount' => '2000',
            'type' => 'credit',
        ];

        $trailer = new Trailer([
            'amountOfErrorCorrections' => '00000000000200{',
        ]);

        self::assertIsArray($trailer->getAmountOfErrorCorrections());
        self::assertSame($expected, $trailer->getAmountOfErrorCorrections());
    }

    /**
     * Should return amount of payments.
     *
     * @throws \EonX\EasyBankFiles\Parsers\Bpay\Brf\Exceptions\InvalidSignFieldException
     *
     * @group Brf-Trailer
     */
    public function testShouldReturnAmountOfPayments(): void
    {
        $expected = [
            'amount' => '12015',
            'type' => 'credit',
        ];

        $trailer = new Trailer([
            'amountOfPayments' => '00000000001201E',
        ]);

        self::assertIsArray($trailer->getAmountOfPayments());
        self::assertSame($expected, $trailer->getAmountOfPayments());
    }

    /**
     * Should return amount of payments.
     *
     * @throws \EonX\EasyBankFiles\Parsers\Bpay\Brf\Exceptions\InvalidSignFieldException
     *
     * @group Brf-Trailer
     */
    public function testShouldReturnAmountOfReversals(): void
    {
        $expected = [
            'amount' => '12517',
            'type' => 'credit',
        ];

        $trailer = new Trailer([
            'amountOfReversals' => '00000000001251G',
        ]);

        self::assertIsArray($trailer->getAmountOfReversals());
        self::assertSame($expected, $trailer->getAmountOfReversals());
    }

    /**
     * Should return number of error corrections.
     *
     * @throws \EonX\EasyBankFiles\Parsers\Bpay\Brf\Exceptions\InvalidSignFieldException
     *
     * @group Brf-Trailer
     */
    public function testShouldReturnNumberOfErrorCorrections(): void
    {
        $expected = [
            'amount' => '10',
            'type' => 'credit',
        ];

        $trailer = new Trailer([
            'numberOfErrorCorrections' => '00000001{',
        ]);

        self::assertCount(2, $trailer->getNumberOfErrorCorrections());
        self::assertSame($expected, $trailer->getNumberOfErrorCorrections());
    }

    /**
     * Should return number of payments.
     *
     * @throws \EonX\EasyBankFiles\Parsers\Bpay\Brf\Exceptions\InvalidSignFieldException
     *
     * @group Brf-Trailer
     */
    public function testShouldReturnNumberOfPayments(): void
    {
        $expected = [
            'amount' => '34',
            'type' => 'credit',
        ];

        $trailer = new Trailer([
            'numberOfPayments' => '00000003D',
        ]);

        self::assertCount(2, $trailer->getNumberOfPayments());
        self::assertSame($expected, $trailer->getNumberOfPayments());
    }

    /**
     * Should return number of reversals.
     *
     * @throws \EonX\EasyBankFiles\Parsers\Bpay\Brf\Exceptions\InvalidSignFieldException
     *
     * @group Brf-Trailer
     */
    public function testShouldReturnNumberOfReversals(): void
    {
        $expected = [
            'amount' => '20',
            'type' => 'credit',
        ];

        $trailer = new Trailer([
            'numberOfReversals' => '00000002{',
        ]);

        self::assertCount(2, $trailer->getNumberOfReversals());
        self::assertSame($expected, $trailer->getNumberOfReversals());
    }

    /**
     * Should return settlement amount.
     *
     * @throws \EonX\EasyBankFiles\Parsers\Bpay\Brf\Exceptions\InvalidSignFieldException
     *
     * @group Brf-Trailer
     */
    public function testShouldReturnSettlementAmount(): void
    {
        $expected = [
            'amount' => '12517',
            'type' => 'credit',
        ];

        $trailer = new Trailer([
            'settlementAmount' => '00000000001251G',
        ]);

        self::assertCount(2, $trailer->getSettlementAmount());
        self::assertSame($expected, $trailer->getSettlementAmount());
    }

    /**
     * Should throw exception if sign field is not found.
     *
     * @throws \EonX\EasyBankFiles\Parsers\Bpay\Brf\Exceptions\InvalidSignFieldException
     *
     * @group Brf-Trailer
     */
    public function testShouldThrowExceptionIfSignedFileNotFound(): void
    {
        $this->expectException(InvalidSignFieldException::class);

        $trailer = new Trailer([
            'amountOfErrorCorrections' => '00000000000200W',
        ]);

        $trailer->getAmountOfErrorCorrections();
    }
}
