<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Unit\Twig;

use EonX\EasyPagination\Resolver\FromHttpFoundationRequestPaginationResolver;
use EonX\EasyPagination\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyPagination\Twig\FormatTotalResultsExtension;
use PHPUnit\Framework\Attributes\DataProvider;

final class FormatTotalResultsExtensionTest extends AbstractUnitTestCase
{
    /**
     * @see testFormatTotalResults
     */
    public static function provideNumResults(): iterable
    {
        yield 'NumResults lower than maxPreciseNumResults' => [
            'numResults' => 109000,
            'expectedResult' => [
                'numResults' => '109000',
                'prefix' => null,
                'suffix' => null,
            ],
        ];

        yield 'NumResults lower than 100_000' => [
            'numResults' => 10000,
            'expectedResult' => [
                'numResults' => '10000',
                'prefix' => null,
                'suffix' => null,
            ],
        ];

        yield 'NumResults greate than maxPreciseNumResults, between 100_000 and 1000_000' => [
            'numResults' => 151467,
            'expectedResult' => [
                'numResults' => '151',
                'prefix' => 'About',
                'suffix' => 'K',
            ],
        ];

        yield 'NumResults greate than maxPreciseNumResults, between 1000_000 and 1000_000_000' => [
            'numResults' => 151467988,
            'expectedResult' => [
                'numResults' => '151.5',
                'prefix' => 'About',
                'suffix' => 'M',
            ],
        ];

        yield 'NumResults greate than maxPreciseNumResults, greate than 1000_000_000' => [
            'numResults' => 151467988344,
            'expectedResult' => [
                'numResults' => '151.47',
                'prefix' => 'About',
                'suffix' => 'B',
            ],
        ];
    }

    #[DataProvider('provideNumResults')]
    public function testFormatTotalResults(int $numResults, array $expectedResult): void
    {
        $formatTotalResultsExtension = new FormatTotalResultsExtension(110_000);
        $formatTotalResults = $formatTotalResultsExtension->getFunctions()[0]
            ->getCallable();
        $result = $formatTotalResults($numResults);

        self::assertSame($expectedResult, $result);
    }
}
