<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests;

use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Pagination;
use Laminas\Uri\Uri;

final class PaginationTest extends AbstractTestCase
{
    public function testGetUrlWithArrayInQuery(): void
    {
        $pagination = Pagination::create(1, 15);
        $pagination->setUrlResolver(static function (Uri $uri, PaginationInterface $pagination, int $page): Uri {
            $query = $uri->getQueryAsArray();

            $query['page'] = [
                'number' => $page,
                'size' => $pagination->getPerPage(),
            ];

            return $uri->setQuery($query);
        });

        self::assertEquals('/?page%5Bnumber%5D=2&page%5Bsize%5D=15', $pagination->getUrl(2));
    }
}
