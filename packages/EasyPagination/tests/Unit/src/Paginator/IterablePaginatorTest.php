<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Unit\Paginator;

use ArrayIterator;
use EonX\EasyPagination\Paginator\IterablePaginator;
use EonX\EasyPagination\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyPagination\ValueObject\Pagination;
use PHPUnit\Framework\Attributes\DataProvider;

final class IterablePaginatorTest extends AbstractUnitTestCase
{
    /**
     * @see testPaginatorGetItems
     */
    public static function providePaginatorGetItemsData(): iterable
    {
        yield 'Empty array' => [
            [],
            static function (array $items): void {
                self::assertEquals([], $items);
            },
        ];

        yield 'Iterable to array' => [
            new ArrayIterator(),
            static function (array $items): void {
                self::assertEquals([], $items);
            },
        ];

        yield 'Transform null to 1' => [
            [null, null],
            static function (array $items): void {
                self::assertEquals([1, 1], $items);
            },
            static fn ($item) => $item ?? 1,
        ];
    }

    /**
     * @see testPaginatorPageMethods
     */
    public static function providePaginatorPageMethodsData(): iterable
    {
        yield 'Default' => [
            [],
            Pagination::create(1, 15),
            '?page=1&perPage=15',
            '?page=2&perPage=15',
        ];

        yield 'Different page and perPage numbers' => [
            [],
            Pagination::create(2, 30),
            '?page=1&perPage=30',
            '?page=3&perPage=30',
        ];

        yield 'Different page and perPage attributes' => [
            [],
            Pagination::create(1, 15, 'p', '_per_page'),
            '?p=1&_per_page=15',
            '?p=2&_per_page=15',
        ];

        yield 'Custom URL' => [
            [],
            Pagination::create(1, 15, null, null, 'https://eonx.com?name=value#frag'),
            'https://eonx.com?name=value&page=1&perPage=15#frag',
            'https://eonx.com?name=value&page=2&perPage=15#frag',
        ];
    }

    #[DataProvider('providePaginatorGetItemsData')]
    public function testPaginatorGetItems(iterable $items, callable $assert, ?callable $transformer = null): void
    {
        $paginator = new IterablePaginator(Pagination::create(1, 15), $items);
        $paginator->setTransformer($transformer);

        $assert($paginator->getItems());
    }

    #[DataProvider('providePaginatorPageMethodsData')]
    public function testPaginatorPageMethods(
        iterable $items,
        Pagination $pagination,
        string $previousPageUrl,
        string $nextPageUrl,
    ): void {
        $paginator = new IterablePaginator($pagination, $items);

        self::assertEquals($pagination->getPage(), $paginator->getCurrentPage());
        self::assertEquals($pagination->getPerPage(), $paginator->getItemsPerPage());
        self::assertEquals($previousPageUrl, $paginator->getPreviousPageUrl());
        self::assertEquals($nextPageUrl, $paginator->getNextPageUrl());
    }
}
