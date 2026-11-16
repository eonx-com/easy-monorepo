<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Unit\Provider;

use EonX\EasyPagination\Exception\InvalidPerPageMaxException;
use EonX\EasyPagination\Provider\PaginationConfigProvider;
use EonX\EasyPagination\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\TestWith;

final class PaginationConfigProviderTest extends AbstractUnitTestCase
{
    #[TestWith([1])]
    #[TestWith([100])]
    #[TestWith([null])]
    public function testItSucceedsWithValidPerPageMax(?int $perPageMax): void
    {
        $config = new PaginationConfigProvider('page', 1, 'perPage', 15, $perPageMax);

        self::assertSame($perPageMax, $config->getPerPageMax());
    }

    #[TestWith([-1])]
    #[TestWith([0])]
    public function testItThrowsOnPerPageMaxBelowOne(int $perPageMax): void
    {
        $this->expectException(InvalidPerPageMaxException::class);
        $this->expectExceptionMessage(\sprintf(
            'The maximum page size must be at least 1 or null, %d given.',
            $perPageMax
        ));

        new PaginationConfigProvider('page', 1, 'perPage', 15, $perPageMax);
    }
}
