<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests;

use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Pagination;
use Spatie\Url\Url;

final class PaginationTest extends AbstractTestCase
{
    public function testGetUrlWithArrayInQuery(): void
    {
        $pagination = Pagination::create(1, 15);
        $pagination->setUrlResolver(static function (Url $uri, PaginationInterface $pagination, int $page): Url {
            $query = $uri->getAllQueryParameters();

            $query['page'] = [
                'number' => $page,
                'size' => $pagination->getPerPage(),
            ];

            return $uri->withQueryParameters($query);
        });

        self::assertSame('?page%5Bnumber%5D=2&page%5Bsize%5D=15', $pagination->getUrl(2));
    }
}
