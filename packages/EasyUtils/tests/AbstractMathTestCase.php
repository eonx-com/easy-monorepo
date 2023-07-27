<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests;

use EonX\EasyUtils\Interfaces\MathInterface;

abstract class AbstractMathTestCase extends AbstractTestCase
{
    /**
     * @see testAbsSucceeds
     */
    public static function provideAbsData(): iterable
    {
        yield [
            'value' => '-10.4',
            'result' => '10.4',
            'precision' => 1,
        ];
        yield [
            'value' => '-10',
            'result' => '10',
        ];
        yield [
            'value' => '0.0',
            'result' => '0.0',
            'precision' => 1,
        ];
        yield [
            'value' => '10',
            'result' => '10',
        ];
        yield [
            'value' => '10.4',
            'result' => '10.4',
            'precision' => 1,
        ];
    }

    /**
     * @see testCompareThatSucceeds
     */
    public static function provideCompareThatData(): iterable
    {
        yield [
            'leftOperand' => '10000000',
            'rightOperand' => '10000001',
            'compareMethod' => 'greaterOrEqualTo',
            'result' => false,
        ];
        yield [
            'leftOperand' => '10000000',
            'rightOperand' => '10000000',
            'compareMethod' => 'greaterOrEqualTo',
            'result' => true,
        ];
        yield [
            'leftOperand' => '10000001',
            'rightOperand' => '10000000',
            'compareMethod' => 'greaterOrEqualTo',
            'result' => true,
        ];
        yield [
            'leftOperand' => '10000000',
            'rightOperand' => '10000000',
            'compareMethod' => 'equalTo',
            'result' => true,
        ];
        yield [
            'leftOperand' => '10000000',
            'rightOperand' => '10000001',
            'compareMethod' => 'equalTo',
            'result' => false,
        ];
        yield [
            'leftOperand' => '10000001',
            'rightOperand' => '10000000',
            'compareMethod' => 'equalTo',
            'result' => false,
        ];
        yield [
            'leftOperand' => '10000001',
            'rightOperand' => '10000000',
            'compareMethod' => 'greaterThan',
            'result' => true,
        ];
        yield [
            'leftOperand' => '10000000',
            'rightOperand' => '10000000',
            'compareMethod' => 'greaterThan',
            'result' => false,
        ];
        yield [
            'leftOperand' => '10000000',
            'rightOperand' => '10000001',
            'compareMethod' => 'greaterThan',
            'result' => false,
        ];
        yield [
            'leftOperand' => '10000000',
            'rightOperand' => '10000001',
            'compareMethod' => 'lessOrEqualTo',
            'result' => true,
        ];
        yield [
            'leftOperand' => '10000000',
            'rightOperand' => '10000000',
            'compareMethod' => 'lessOrEqualTo',
            'result' => true,
        ];
        yield [
            'leftOperand' => '10000001',
            'rightOperand' => '10000000',
            'compareMethod' => 'lessOrEqualTo',
            'result' => false,
        ];
        yield [
            'leftOperand' => '10000001',
            'rightOperand' => '10000000',
            'compareMethod' => 'lessThan',
            'result' => false,
        ];
        yield [
            'leftOperand' => '10000000',
            'rightOperand' => '10000000',
            'compareMethod' => 'lessThan',
            'result' => false,
        ];
        yield [
            'leftOperand' => '10000000',
            'rightOperand' => '10000001',
            'compareMethod' => 'lessThan',
            'result' => true,
        ];
        yield [
            'leftOperand' => '91',
            'rightOperand' => '091',
            'compareMethod' => 'equalTo',
            'result' => true,
        ];
    }

    /**
     * @see testDivideSucceeds
     */
    public static function provideDivideData(): iterable
    {
        yield 'With null precision' => [
            'expected' => '333',
            'dividend' => '1000',
            'divisor' => '3',
            'precision' => null,
        ];
        yield 'With precision' => [
            'expected' => '333.33',
            'dividend' => '1000',
            'divisor' => '3',
            'precision' => 2,
        ];
    }

    /**
     * @see testRoundSucceeds
     */
    public static function provideRoundData(): iterable
    {
        yield [
            'value' => '10.4',
            'expected' => '10',
        ];
        yield [
            'value' => '10.5',
            'expected' => '10',
        ];
        yield [
            'value' => '10.6',
            'expected' => '11',
        ];
        yield [
            'value' => '11.5',
            'expected' => '12',
        ];
        yield [
            'value' => '12.5',
            'expected' => '12',
        ];
        yield [
            'value' => '13.5',
            'expected' => '14',
        ];
    }

    /**
     * @dataProvider provideAbsData
     */
    public function testAbsSucceeds(string $value, string $result, ?int $precision = null): void
    {
        $math = $this->getMath();
        $actual = $math->abs($value, $precision);

        self::assertSame($result, $actual);
    }

    public function testAddSucceeds(): void
    {
        $math = $this->getMath();
        $actual = $math->add('10000000000000000000', '10000000000000000000');

        self::assertSame('20000000000000000000', $actual);
    }

    /**
     * @dataProvider provideCompareThatData
     */
    public function testCompareThatSucceeds(
        string $leftOperand,
        string $rightOperand,
        string $compareMethod,
        bool $result,
    ): void {
        $math = $this->getMath();

        $actual = $math
            ->compareThat($leftOperand)
            ->{$compareMethod}($rightOperand);

        self::assertSame($result, $actual);
    }

    /**
     * @dataProvider provideDivideData
     */
    public function testDivideSucceeds(
        string $expected,
        string $dividend,
        string $divisor,
        ?int $precision = null,
    ): void {
        $math = $this->getMath();
        $actual = $math->divide($dividend, $divisor, $precision);

        self::assertSame($expected, $actual);
    }

    public function testMultiplySucceeds(): void
    {
        $math = $this->getMath();
        $actual = $math->multiply('10000000000000000000', '5');

        self::assertSame('50000000000000000000', $actual);
    }

    /**
     * @dataProvider provideRoundData
     */
    public function testRoundSucceeds(string $value, string $expected): void
    {
        $math = $this->getMath();
        $actual = $math->round($value);

        self::assertSame($expected, $actual);
    }

    public function testSubSucceeds(): void
    {
        $math = $this->getMath();
        $actual = $math->sub('20000000000000000000', '10000000000000000000');

        self::assertSame('10000000000000000000', $actual);
    }

    abstract protected function getMath(): MathInterface;
}
