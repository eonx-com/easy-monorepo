<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Paginators;

use EonX\EasyPagination\Pagination;
use EonX\EasyPagination\Paginators\IterableLengthAwarePaginator;
use EonX\EasyPagination\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class IterableLengthAwarePaginatorTest extends AbstractTestCase
{
    /**
     * @see testUrls
     */
    public static function providerTestUrls(): iterable
    {
        yield 'Prev: no, Next: yes' => [
            10,
            new Pagination(1, 5),
            '?page=1&perPage=5',
            '?page=2&perPage=5',
        ];

        yield 'Prev: yes, Next: yes' => [10, new Pagination(2, 2), '?page=1&perPage=2', '?page=3&perPage=2'];

        yield 'Prev: yes, Next: yes (with query)' => [
            10,
            new Pagination(2, 2, null, null, '/?arr=1'),
            '?arr=1&page=1&perPage=2',
            '?arr=1&page=3&perPage=2',
        ];

        yield 'Prev: yes, Next: yes (with fragment)' => [
            10,
            new Pagination(2, 2, null, null, '/#frag'),
            '?page=1&perPage=2#frag',
            '?page=3&perPage=2#frag',
        ];

        yield 'Prev: yes, Next: yes (with query, fragment)' => [
            10,
            new Pagination(2, 2, null, null, '/?myAttr=1#frag'),
            '?myAttr=1&page=1&perPage=2#frag',
            '?myAttr=1&page=3&perPage=2#frag',
        ];

        yield 'Prev: yes, Next: yes (with scheme, host, query, fragment)' => [
            10,
            new Pagination(2, 2, null, null, 'http://eonx.com/?myAttr=1#frag'),
            'http://eonx.com?myAttr=1&page=1&perPage=2#frag',
            'http://eonx.com?myAttr=1&page=3&perPage=2#frag',
        ];
    }

    public function testPaginator(): void
    {
        $paginator = new IterableLengthAwarePaginator(new Pagination(1, 15), [], 10);

        self::assertEquals([], $paginator->getItems());
        self::assertEquals(10, $paginator->getTotalItems());
        self::assertFalse($paginator->hasPreviousPage());
        self::assertFalse($paginator->hasNextPage());
    }

    #[DataProvider('providerTestUrls')]
    public function testUrls(
        int $total,
        Pagination $pagination,
        ?string $previousUrl = null,
        ?string $nextUrl = null,
    ): void {
        $paginator = new IterableLengthAwarePaginator($pagination, [], $total);

        self::assertEquals($previousUrl, $paginator->getPreviousPageUrl());
        self::assertEquals($nextUrl, $paginator->getNextPageUrl());
    }
}
