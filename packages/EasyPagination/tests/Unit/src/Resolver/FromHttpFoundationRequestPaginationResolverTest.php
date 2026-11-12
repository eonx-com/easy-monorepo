<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Unit\Resolver;

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
}
