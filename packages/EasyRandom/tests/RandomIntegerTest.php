<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests;

use EonX\EasyRandom\RandomGenerator;

final class RandomIntegerTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testRandomInteger
     */
    public static function providerTestRandomInteger(): iterable
    {
        yield 'Default values' => [];

        yield '1 to 100' => [1, 100];
    }

    /**
     * @dataProvider providerTestRandomInteger
     */
    public function testRandomInteger(?int $min = null, ?int $max = null, ?int $iterations = null): void
    {
        $min = $min ?? 0;
        $max = $max ?? \PHP_INT_MAX;
        $iterations = $iterations ?? 100;
        $generator = new RandomGenerator();

        for ($i = 0; $i < $iterations; $i++) {
            $randomInteger = $generator->randomInteger($min, $max);

            self::assertGreaterThanOrEqual($min, $randomInteger);
            self::assertLessThanOrEqual($max, $randomInteger);
        }
    }
}
