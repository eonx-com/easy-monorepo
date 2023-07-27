<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Generators;

use EonX\EasyRandom\Generators\RandomIntegerGenerator;
use EonX\EasyRandom\Tests\AbstractTestCase;

final class RandomIntegerGeneratorTest extends AbstractTestCase
{
    /**
     * @see testGenerateSucceeds
     */
    public static function provideMinAndMaxValues(): iterable
    {
        yield 'Default values' => [
            'min' => null,
            'max' => null,
        ];

        yield '1 to 100' => [
            'min' => 1,
            'max' => 100,
        ];

        yield '1 to 1' => [
            'min' => 1,
            'max' => 1,
        ];
    }

    /**
     * @dataProvider provideMinAndMaxValues
     */
    public function testGenerateSucceeds(?int $min = null, ?int $max = null): void
    {
        $sut = new RandomIntegerGenerator();

        $result = $sut->generate($min, $max);

        self::assertGreaterThanOrEqual($min ?? 0, $result);
        self::assertLessThanOrEqual($max ?? \PHP_INT_MAX, $result);
    }
}
