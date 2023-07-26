<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Tests\ValueObjects;

use EonX\EasyUtils\ValueObjects\Number;
use PHPUnit\Framework\TestCase;
use Throwable;
use UnexpectedValueException;

final class NumberTest extends TestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testMathOperationSucceeds
     */
    public static function provideFunctionData(): iterable
    {
        yield [
            'function' => 'abs',
            'input' => -100,
            'args' => [],
            'expectedResult' => 100,
        ];
        yield [
            'function' => 'abs',
            'input' => 200,
            'args' => [],
            'expectedResult' => 200,
        ];

        yield [
            'function' => 'add',
            'input' => 100,
            'args' => [20],
            'expectedResult' => 120,
        ];
        yield [
            'function' => 'add',
            'input' => -10,
            'args' => [20],
            'expectedResult' => 10,
        ];

        yield [
            'function' => 'subtract',
            'input' => 120,
            'args' => [20],
            'expectedResult' => 100,
        ];
        yield [
            'function' => 'subtract',
            'input' => -10,
            'args' => [20],
            'expectedResult' => -30,
        ];

        yield [
            'function' => 'compare',
            'input' => 10,
            'args' => [20],
            'expectedResult' => -1,
        ];
        yield [
            'function' => 'compare',
            'input' => 10,
            'args' => [10],
            'expectedResult' => 0,
        ];
        yield [
            'function' => 'compare',
            'input' => 10,
            'args' => [5],
            'expectedResult' => 1,
        ];

        yield [
            'function' => 'multiply',
            'input' => 10,
            'args' => [4],
            'expectedResult' => 40,
        ];
        yield [
            'function' => 'multiply',
            'input' => -10,
            'args' => [2],
            'expectedResult' => -20,
        ];
        yield [
            'function' => 'multiply',
            'input' => 1000,
            'args' => [1],
            'expectedResult' => 1000,
        ];

        yield [
            'function' => 'divide',
            'input' => 10,
            'args' => [2],
            'expectedResult' => 5,
        ];
        yield [
            'function' => 'divide',
            'input' => -10,
            'args' => [2],
            'expectedResult' => -5,
        ];

        yield [
            'function' => 'isEqualTo',
            'input' => 10,
            'args' => [10],
            'expectedResult' => true,
        ];
        yield [
            'function' => 'isEqualTo',
            'input' => 10,
            'args' => [3],
            'expectedResult' => false,
        ];

        yield [
            'function' => 'isGreaterThan',
            'input' => 10,
            'args' => [5],
            'expectedResult' => true,
        ];
        yield [
            'function' => 'isGreaterThan',
            'input' => 10,
            'args' => [10],
            'expectedResult' => false,
        ];
        yield [
            'function' => 'isGreaterThan',
            'input' => 10,
            'args' => [20],
            'expectedResult' => false,
        ];

        yield [
            'function' => 'isGreaterThanOrEqualTo',
            'input' => 10,
            'args' => [5],
            'expectedResult' => true,
        ];
        yield [
            'function' => 'isGreaterThanOrEqualTo',
            'input' => 10,
            'args' => [10],
            'expectedResult' => true,
        ];
        yield [
            'function' => 'isGreaterThanOrEqualTo',
            'input' => 10,
            'args' => [20],
            'expectedResult' => false,
        ];

        yield [
            'function' => 'isLessThan',
            'input' => 10,
            'args' => [5],
            'expectedResult' => false,
        ];
        yield [
            'function' => 'isLessThan',
            'input' => 10,
            'args' => [10],
            'expectedResult' => false,
        ];
        yield [
            'function' => 'isLessThan',
            'input' => 10,
            'args' => [20],
            'expectedResult' => true,
        ];

        yield [
            'function' => 'isLessThanOrEqualTo',
            'input' => 10,
            'args' => [5],
            'expectedResult' => false,
        ];
        yield [
            'function' => 'isLessThanOrEqualTo',
            'input' => 10,
            'args' => [10],
            'expectedResult' => true,
        ];
        yield [
            'function' => 'isLessThanOrEqualTo',
            'input' => 10,
            'args' => [20],
            'expectedResult' => true,
        ];

        yield [
            'function' => 'isZero',
            'input' => 0,
            'args' => [],
            'expectedResult' => true,
        ];

        yield [
            'function' => 'isNegative',
            'input' => -10,
            'args' => [],
            'expectedResult' => true,
        ];
        yield [
            'function' => 'isNegative',
            'input' => 0,
            'args' => [],
            'expectedResult' => false,
        ];
        yield [
            'function' => 'isNegative',
            'input' => 10,
            'args' => [],
            'expectedResult' => false,
        ];

        yield [
            'function' => 'isNegativeOrZero',
            'input' => -10,
            'args' => [],
            'expectedResult' => true,
        ];
        yield [
            'function' => 'isNegativeOrZero',
            'input' => 0,
            'args' => [],
            'expectedResult' => true,
        ];

        yield [
            'function' => 'isPositive',
            'input' => 10,
            'args' => [],
            'expectedResult' => true,
        ];
        yield [
            'function' => 'isPositive',
            'input' => 0,
            'args' => [],
            'expectedResult' => false,
        ];
        yield [
            'function' => 'isPositive',
            'input' => -1,
            'args' => [],
            'expectedResult' => false,
        ];

        yield [
            'function' => 'isPositiveOrZero',
            'input' => 10,
            'args' => [],
            'expectedResult' => true,
        ];
        yield [
            'function' => 'isPositiveOrZero',
            'input' => 0,
            'args' => [],
            'expectedResult' => true,
        ];
        yield [
            'function' => 'isPositiveOrZero',
            'input' => -1,
            'args' => [],
            'expectedResult' => false,
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testConstructorThrowsExceptionWithInvalidValue
     */
    public static function provideInvalidData(): iterable
    {
        yield [
            'value' => '3.2.1',
        ];
        yield [
            'value' => 'some-string',
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testMaxSucceeds
     */
    public static function provideMaxData(): iterable
    {
        yield [
            'values' => [new Number('100'), new Number('10'), new Number(400)],
            'expectedValue' => 400,
        ];
        yield [
            'values' => [new Number('-10'), new Number(-30)],
            'expectedValue' => -10,
        ];
        yield [
            'values' => [new Number(30)],
            'expectedValue' => 30,
        ];
        yield [
            'values' => [],
            'expectedValue' => null,
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testMinSucceeds
     */
    public static function provideMinData(): iterable
    {
        yield [
            'values' => [new Number('100'), new Number(10), new Number('400')],
            'expectedValue' => 10,
        ];
        yield [
            'values' => [new Number(-10), new Number('-30')],
            'expectedValue' => -30,
        ];
        yield [
            'values' => [new Number(30)],
            'expectedValue' => 30,
        ];
        yield [
            'values' => [],
            'expectedValue' => null,
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testToStringSucceedsAndDoesCorrectRounding
     */
    public static function provideRoundData(): iterable
    {
        yield [
            'value' => '3.2',
            'precision' => 0,
            'expectedResult' => '3',
        ];
        yield [
            'value' => '3.4',
            'precision' => 0,
            'expectedResult' => '3',
        ];
        yield [
            'value' => '3.5',
            'precision' => 0,
            'expectedResult' => '4',
        ];
        yield [
            'value' => '4.5',
            'precision' => 0,
            'expectedResult' => '4',
        ];
        yield [
            'value' => '4.51',
            'precision' => 0,
            'expectedResult' => '5',
        ];
        yield [
            'value' => '5.5',
            'precision' => 0,
            'expectedResult' => '6',
        ];
        yield [
            'value' => '-7.5',
            'precision' => 0,
            'expectedResult' => '-8',
        ];
        yield [
            'value' => '-8.3',
            'precision' => 0,
            'expectedResult' => '-8',
        ];
        yield [
            'value' => '-8.5',
            'precision' => 0,
            'expectedResult' => '-8',
        ];
        yield [
            'value' => '-8.7',
            'precision' => 0,
            'expectedResult' => '-9',
        ];
        yield [
            'value' => '10.1234',
            'precision' => 2,
            'expectedResult' => '10.12',
        ];
        yield [
            'value' => '10.1234',
            'precision' => 3,
            'expectedResult' => '10.123',
        ];
        yield [
            'value' => '10.1235',
            'precision' => 3,
            'expectedResult' => '10.124',
        ];
        yield [
            'value' => '10.1245',
            'precision' => 3,
            'expectedResult' => '10.124',
        ];
        yield [
            'value' => '10',
            'precision' => 3,
            'expectedResult' => '10.000',
        ];
        yield [
            'value' => '0.8',
            'precision' => 2,
            'expectedResult' => '0.80',
        ];
        yield [
            'value' => '0.25',
            'precision' => 2,
            'expectedResult' => '0.25',
        ];
        yield [
            'value' => '0.253',
            'precision' => 2,
            'expectedResult' => '0.25',
        ];
        yield [
            'value' => '0.255',
            'precision' => 2,
            'expectedResult' => '0.26',
        ];
        yield [
            'value' => '0.256',
            'precision' => 2,
            'expectedResult' => '0.26',
        ];
        yield [
            'value' => '0.5',
            'precision' => 2,
            'expectedResult' => '0.50',
        ];
        yield [
            'value' => '0.35',
            'precision' => 2,
            'expectedResult' => '0.35',
        ];
        yield [
            'value' => '0.35',
            'precision' => 5,
            'expectedResult' => '0.35000',
        ];
    }

    /**
     * @dataProvider provideInvalidData
     */
    public function testConstructorThrowsExceptionWithInvalidValue(string $value): void
    {
        $exception = null;

        try {
            new Number($value);
        } catch (Throwable $exception) {
        }

        self::assertInstanceOf(UnexpectedValueException::class, $exception);
    }

    /**
     * @param mixed[] $args
     *
     * @dataProvider provideFunctionData
     */
    public function testMathOperationSucceeds(string $function, int $input, array $args, int|bool $expectedResult): void
    {
        $constructorValueCastFunctions = [
            '\strval',
            '\intval',
        ];
        $object = new Number($constructorValueCastFunctions[\random_int(0, 1)]($input));
        $argumentCastFunctions = [
            '\strval',
            '\intval',
            '\floatval',
            static fn (int $a): Number => new Number($a),
        ];
        $args = \array_map($argumentCastFunctions[\random_int(0, 3)], $args);
        $objectValue = (string)$object;

        $result = $object->{$function}(...$args);

        self::assertSame((string)$expectedResult, (string)$result);
        self::assertSame($objectValue, (string)$object);
    }

    /**
     * @param \EonX\EasyUtils\ValueObjects\Number[] $values
     *
     * @dataProvider provideMaxData
     */
    public function testMaxSucceeds(array $values, ?int $expectedValue = null): void
    {
        $result = Number::max(...$values);

        self::assertSame((string)$expectedValue, (string)$result);
    }

    /**
     * @param \EonX\EasyUtils\ValueObjects\Number[] $values
     *
     * @dataProvider provideMinData
     */
    public function testMinSucceeds(array $values, ?int $expectedValue = null): void
    {
        $result = Number::min(...$values);

        self::assertSame((string)$expectedValue, (string)$result);
    }

    /**
     * @dataProvider provideRoundData
     */
    public function testToStringSucceedsAndDoesCorrectRounding(
        string $value,
        int $precision,
        string $expectedResult,
    ): void {
        $integer = new Number($value, $precision);

        self::assertSame($expectedResult, (string)$integer);
    }

    public function testToStringSucceedsWithBigNumbers(): void
    {
        $integer1 = new Number('9999999999999999999999');

        $result = $integer1->add(1);

        self::assertSame('10000000000000000000000', (string)$result);
    }

    public function testToStringSucceedsWithMultipleOperations(): void
    {
        $integer1 = new Number(10);
        $integer2 = $integer1->divide(3);
        $integer3 = $integer2->multiply(3);

        self::assertSame('9', (string)$integer3);
    }
}
