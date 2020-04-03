<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Paginators;

use EonX\EasyPagination\Data\StartSizeData;
use EonX\EasyPagination\Exceptions\InvalidPathException;
use EonX\EasyPagination\Paginators\ArrayPaginator;
use EonX\EasyPagination\Tests\AbstractTestCase;

final class ArrayPaginatorTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestUrls(): iterable
    {
        yield 'Prev: no, Next: yes' => [
            10,
            new StartSizeData(1, 5),
            null,
            '/?page=2&perPage=5'
        ];

        yield 'Prev: yes, Next: yes' => [
            10,
            new StartSizeData(2, 2),
            '/?page=1&perPage=2',
            '/?page=3&perPage=2'
        ];

        yield 'Prev: yes, Next: yes (with query)' => [
            10,
            new StartSizeData(2, 2),
            '/?arr=1&page=1&perPage=2',
            '/?arr=1&page=3&perPage=2',
            '/?arr=1'
        ];

        yield 'Prev: yes, Next: yes (with fragment)' => [
            10,
            new StartSizeData(2, 2),
            '/?page=1&perPage=2#frag',
            '/?page=3&perPage=2#frag',
            '/#frag'
        ];

        yield 'Prev: yes, Next: yes (with query, fragment)' => [
            10,
            new StartSizeData(2, 2),
            '/?myAttr=1&page=1&perPage=2#frag',
            '/?myAttr=1&page=3&perPage=2#frag',
            '/?myAttr=1#frag'
        ];

        yield 'Prev: yes, Next: yes (with scheme, host, query, fragment)' => [
            10,
            new StartSizeData(2, 2),
            'http://eonx.com/?myAttr=1&page=1&perPage=2#frag',
            'http://eonx.com/?myAttr=1&page=3&perPage=2#frag',
            'http://eonx.com/?myAttr=1#frag'
        ];
    }

    public function testInvalidPathException(): void
    {
        $this->expectException(InvalidPathException::class);

        $paginator = new ArrayPaginator([], 10, new StartSizeData(1, 2), 'http:///');
        $paginator->getNextPageUrl();
    }

    public function testPaginator(): void
    {
        $paginator = new ArrayPaginator([], 10, new StartSizeData(1, 15));

        self::assertEquals([], $paginator->getItems());
        self::assertEquals(10, $paginator->getTotalItems());
    }

    /**
     * @dataProvider providerTestUrls
     */
    public function testUrls(
        int $total,
        StartSizeData $data,
        ?string $previousUrl = null,
        ?string $nextUrl = null,
        ?string $path = null
    ): void {
        $paginator = new ArrayPaginator([], $total, $data, $path);

        self::assertEquals($previousUrl, $paginator->getPreviousPageUrl());
        self::assertEquals($nextUrl, $paginator->getNextPageUrl());
    }
}
