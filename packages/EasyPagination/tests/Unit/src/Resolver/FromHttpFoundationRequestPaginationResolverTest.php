<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Unit\Resolver;

use EonX\EasyPagination\Provider\PaginationConfigProvider;
use EonX\EasyPagination\Provider\PaginationConfigProviderInterface;
use EonX\EasyPagination\Resolver\FromHttpFoundationRequestPaginationResolver;
use EonX\EasyPagination\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class FromHttpFoundationRequestPaginationResolverTest extends AbstractUnitTestCase
{
    /**
     * @see testItReplacesInvalidValues
     */
    public static function provideInvalidValues(): iterable
    {
        yield 'page 0' => [
            'query' => ['page' => 0, 'perPage' => 30],
            'perPageMax' => null,
            'expectedPage' => 1,
            'expectedPerPage' => 30,
        ];

        yield 'negative page' => [
            'query' => ['page' => -5, 'perPage' => 30],
            'perPageMax' => null,
            'expectedPage' => 1,
            'expectedPerPage' => 30,
        ];

        yield 'per page 0' => [
            'query' => ['page' => 2, 'perPage' => 0],
            'perPageMax' => null,
            'expectedPage' => 2,
            'expectedPerPage' => 15,
        ];

        yield 'negative per page' => [
            'query' => ['page' => 2, 'perPage' => -1],
            'perPageMax' => null,
            'expectedPage' => 2,
            'expectedPerPage' => 15,
        ];

        yield 'non-numeric per page' => [
            'query' => ['page' => 2, 'perPage' => 'abc'],
            'perPageMax' => null,
            'expectedPage' => 2,
            'expectedPerPage' => 15,
        ];

        yield 'per page with a numeric prefix' => [
            'query' => ['page' => '2abc', 'perPage' => '30abc'],
            'perPageMax' => null,
            'expectedPage' => 1,
            'expectedPerPage' => 15,
        ];

        yield 'decimal per page' => [
            'query' => ['page' => 2, 'perPage' => '1.5'],
            'perPageMax' => null,
            'expectedPage' => 2,
            'expectedPerPage' => 15,
        ];

        yield 'numeric strings' => [
            'query' => ['page' => '3', 'perPage' => '30'],
            'perPageMax' => null,
            'expectedPage' => 3,
            'expectedPerPage' => 30,
        ];

        yield 'per page above the max' => [
            'query' => ['page' => 2, 'perPage' => 2_147_483_647],
            'perPageMax' => 100,
            'expectedPage' => 2,
            'expectedPerPage' => 100,
        ];

        yield 'per page equal to the max' => [
            'query' => ['page' => 2, 'perPage' => 100],
            'perPageMax' => 100,
            'expectedPage' => 2,
            'expectedPerPage' => 100,
        ];

        yield 'large per page without a max' => [
            'query' => ['page' => 2, 'perPage' => 2_147_483_647],
            'perPageMax' => null,
            'expectedPage' => 2,
            'expectedPerPage' => 2_147_483_647,
        ];

        yield 'page with an offset above the integer range' => [
            'query' => ['page' => \PHP_INT_MAX, 'perPage' => 15],
            'perPageMax' => null,
            'expectedPage' => 1,
            'expectedPerPage' => 15,
        ];

        yield 'largest page for the per page' => [
            'query' => ['page' => \intdiv(\PHP_INT_MAX, 15) + 1, 'perPage' => 15],
            'perPageMax' => null,
            'expectedPage' => \intdiv(\PHP_INT_MAX, 15) + 1,
            'expectedPerPage' => 15,
        ];

        yield 'page above the largest page for the per page' => [
            'query' => ['page' => \intdiv(\PHP_INT_MAX, 15) + 2, 'perPage' => 15],
            'perPageMax' => null,
            'expectedPage' => 1,
            'expectedPerPage' => 15,
        ];

        yield 'page with a next page above the integer range' => [
            'query' => ['page' => \PHP_INT_MAX, 'perPage' => 1],
            'perPageMax' => null,
            'expectedPage' => 1,
            'expectedPerPage' => 1,
        ];

        yield 'largest page for the per page reduced to the max' => [
            'query' => ['page' => \intdiv(\PHP_INT_MAX, 100) + 1, 'perPage' => 2_147_483_647],
            'perPageMax' => 100,
            'expectedPage' => \intdiv(\PHP_INT_MAX, 100) + 1,
            'expectedPerPage' => 100,
        ];

        yield 'page above the integer range' => [
            'query' => ['page' => '9223372036854775808', 'perPage' => 15],
            'perPageMax' => null,
            'expectedPage' => 1,
            'expectedPerPage' => 15,
        ];

        yield 'page 1 with the largest per page' => [
            'query' => ['page' => 1, 'perPage' => \PHP_INT_MAX],
            'perPageMax' => null,
            'expectedPage' => 1,
            'expectedPerPage' => \PHP_INT_MAX,
        ];

        yield 'page 2 with the largest per page' => [
            'query' => ['page' => 2, 'perPage' => \PHP_INT_MAX],
            'perPageMax' => null,
            'expectedPage' => 2,
            'expectedPerPage' => \PHP_INT_MAX,
        ];

        yield 'page 3 with the largest per page' => [
            'query' => ['page' => 3, 'perPage' => \PHP_INT_MAX],
            'perPageMax' => null,
            'expectedPage' => 1,
            'expectedPerPage' => \PHP_INT_MAX,
        ];

        yield 'largest page for the per page 1' => [
            'query' => ['page' => \PHP_INT_MAX - 1, 'perPage' => 1],
            'perPageMax' => null,
            'expectedPage' => \PHP_INT_MAX - 1,
            'expectedPerPage' => 1,
        ];
    }

    /**
     * @see testItResolvesPageWithNotPositivePerPageDefault
     */
    public static function provideNotPositivePerPageDefaults(): iterable
    {
        yield 'per page default 0' => [0];

        yield 'negative per page default' => [-1];
    }

    /**
     * @see testItLimitsPageDefaultToLargestPage
     */
    public static function providePageDefaultsAboveLargestPage(): iterable
    {
        yield 'valid page is kept' => [
            'query' => ['page' => 1, 'perPage' => \PHP_INT_MAX],
            'expectedPage' => 1,
        ];

        yield 'page not set' => [
            'query' => ['perPage' => \PHP_INT_MAX],
            'expectedPage' => 2,
        ];

        yield 'invalid page' => [
            'query' => ['page' => 'abc', 'perPage' => \PHP_INT_MAX],
            'expectedPage' => 2,
        ];
    }

    public function testCustomConfigResolveSuccessfully(): void
    {
        $config = $this->createConfig(pageAttr: 'page', perPageAttr: 'perPage');
        $resolver = new FromHttpFoundationRequestPaginationResolver($config, $this->createServerRequest([
            'page' => 5,
            'perPage' => 100,
        ]));

        $data = $resolver();

        self::assertEquals(5, $data->getPage());
        self::assertEquals(100, $data->getPerPage());
    }

    public function testDefaultWhenQueryAttrNotSet(): void
    {
        $config = $this->createConfig();
        $resolver = new FromHttpFoundationRequestPaginationResolver($config, $this->createServerRequest([]));

        $data = $resolver();

        self::assertEquals($config->getPageDefault(), $data->getPage());
        self::assertEquals($config->getPerPageDefault(), $data->getPerPage());
    }

    #[DataProvider('providePageDefaultsAboveLargestPage')]
    public function testItLimitsPageDefaultToLargestPage(array $query, int $expectedPage): void
    {
        $config = $this->createConfig(pageDefault: 3);
        $resolver = new FromHttpFoundationRequestPaginationResolver($config, $this->createServerRequest($query));

        $data = $resolver();

        self::assertSame($expectedPage, $data->getPage());
        self::assertSame(\PHP_INT_MAX, $data->getPerPage());
    }

    #[DataProvider('provideInvalidValues')]
    public function testItReplacesInvalidValues(
        array $query,
        ?int $perPageMax,
        int $expectedPage,
        int $expectedPerPage,
    ): void {
        $config = $this->createConfig(perPageMax: $perPageMax);
        $resolver = new FromHttpFoundationRequestPaginationResolver($config, $this->createServerRequest($query));

        $data = $resolver();

        self::assertSame($expectedPage, $data->getPage());
        self::assertSame($expectedPerPage, $data->getPerPage());
    }

    #[DataProvider('provideNotPositivePerPageDefaults')]
    public function testItResolvesPageWithNotPositivePerPageDefault(int $perPageDefault): void
    {
        $config = new PaginationConfigProvider('page', 1, 'perPage', $perPageDefault);
        $resolver = new FromHttpFoundationRequestPaginationResolver($config, $this->createServerRequest([
            'page' => 2,
        ]));

        $data = $resolver();

        self::assertSame(2, $data->getPage());
        self::assertSame($perPageDefault, $data->getPerPage());
    }

    public function testItSucceedsWithConfigProviderWithoutPerPageMax(): void
    {
        $config = new class() implements PaginationConfigProviderInterface {
            public function getPageAttribute(): string
            {
                return 'page';
            }

            public function getPageDefault(): int
            {
                return 1;
            }

            public function getPerPageAttribute(): string
            {
                return 'perPage';
            }

            public function getPerPageDefault(): int
            {
                return 15;
            }
        };
        $resolver = new FromHttpFoundationRequestPaginationResolver($config, $this->createServerRequest([
            'page' => 2,
            'perPage' => 500,
        ]));

        $data = $resolver();

        self::assertSame(2, $data->getPage());
        self::assertSame(500, $data->getPerPage());
    }
}
