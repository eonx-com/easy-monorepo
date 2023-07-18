<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Tests\CreditCard;

use EonX\EasyUtils\CreditCard\CreditCardNumberValidator;
use EonX\EasyUtils\Tests\AbstractTestCase;

final class CreditCardNumberValidatorTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testIsCreditCardNumberValidSucceeds
     */
    public static function provideCardNumbers(): iterable
    {
        yield 'maestro' => [
            'number' => '6771 7980 2100 0008',
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

        yield 'laser' => [
            'number' => '6304 9001 7740 2924 41',
            'expectedResult' => true,
        ];

        yield 'instapayment' => [
            'number' => '6397 1249 5702 0072',
            'expectedResult' => true,
        ];

        yield 'mir' => [
            'number' => '2201 6186 3771 7440',
            'expectedResult' => true,
        ];

        yield 'uatp' => [
            'number' => '1354 1001 4004 955',
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

    /**
     * @dataProvider provideCardNumbers
     */
    public function testIsCreditCardNumberValidSucceeds(string $number, bool $expectedResult): void
    {
        $validator = new CreditCardNumberValidator();

        $result = $validator->isCreditCardNumberValid($number);

        self::assertSame($expectedResult, $result);
    }
}
