<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Tests\CreditCard;

use EonX\EasyUtils\CreditCard\CreditCardNumberValidator;
use EonX\EasyUtils\Tests\AbstractTestCase;

final class CreditCardNumberValidatorTest extends AbstractTestCase
{
    /**
     * @dataProvider provideCardNumbers
     */
    public function testIsCreditCardNumberValidSucceeds(string $number, bool $expectedResult): void
    {
        $validator = new CreditCardNumberValidator();

        $result = $validator->isCreditCardNumberValid($number);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @return iterable<mixed>
     */
    public function provideCardNumbers(): iterable
    {
        yield 'visaelectron' => [
            'number' => '4001 0200 0000 0009',
            'expectedResult' => true,
        ];

        yield 'maestro' => [
            'number' => '6771 7980 2100 0008',
            'expectedResult' => true,
        ];

        yield 'dankort' => [
            'number' => '5019 5555 4444 5555',
            'expectedResult' => true,
        ];

        yield 'visa' => [
            'number' => '4988 4388 4388 4305',
            'expectedResult' => true,
        ];

        yield 'mastercard' => [
            'number' => '5577 0000 5577 0004',
            'expectedResult' => true,
        ];

        yield 'amex' => [
            'number' => '3700 0000 0000 002',
            'expectedResult' => true,
        ];

        yield 'dinersclub' => [
            'number' => '3600 6666 3333 44',
            'expectedResult' => true,
        ];

        yield 'discover' => [
            'number' => '6011 6011 6011 6611',
            'expectedResult' => true,
        ];

        yield 'unionpay' => [
            'number' => '6243 0300 0000 0001',
            'expectedResult' => true,
        ];

        yield 'jcb' => [
            'number' => '3569 9900 1009 5841',
            'expectedResult' => true,
        ];

        yield 'invalid card number' => [
            'number' => '1234 5678 9012 3456',
            'expectedResult' => false,
        ];

        yield 'alphabetical card number' => [
            'number' => 'abcd efgh igkl mnop',
            'expectedResult' => false,
        ];

        yield 'too short card number' => [
            'number' => '1234',
            'expectedResult' => false,
        ];

        yield 'too long card number' => [
            'number' => '6243 0300 0000 0001 3201',
            'expectedResult' => false,
        ];
    }
}
