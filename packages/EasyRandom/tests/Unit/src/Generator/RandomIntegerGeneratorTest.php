<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Unit\Generator;

use EonX\EasyRandom\Generator\RandomIntegerGenerator;
use EonX\EasyRandom\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class RandomIntegerGeneratorTest extends AbstractUnitTestCase
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

    #[DataProvider('provideMinAndMaxValues')]
    public function testGenerateSucceeds(?int $min = null, ?int $max = null): void
    {
        $sut = new RandomIntegerGenerator();

        $result = $sut->generate($min, $max);

        self::assertGreaterThanOrEqual($min ?? 0, $result);
        self::assertLessThanOrEqual($max ?? \PHP_INT_MAX, $result);
    }
}
