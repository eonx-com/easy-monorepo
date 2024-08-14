<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Unit\Pagination;

use EonX\EasyPagination\Pagination\Pagination;
use EonX\EasyPagination\Pagination\PaginationInterface;
use EonX\EasyPagination\Tests\Unit\AbstractUnitTestCase;
use Spatie\Url\Url;

final class PaginationTest extends AbstractUnitTestCase
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
