<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Unit\Laravel;

use EonX\EasyPagination\Provider\PaginationProviderInterface;
use EonX\EasyPagination\Resolver\DefaultPaginationResolver;
use EonX\EasyPagination\ValueObject\PaginationInterface;

final class EasyPaginationServiceProviderTest extends AbstractLaravelTestCase
{
    public function testSanity(): void
    {
        $app = $this->getApplication();

        $paginationProvider = $app->make(PaginationProviderInterface::class);
        $paginationProvider->setResolver(new DefaultPaginationResolver($paginationProvider->getPaginationConfig()));

        self::assertInstanceOf(PaginationProviderInterface::class, $paginationProvider);
        self::assertInstanceOf(PaginationInterface::class, $app->make(PaginationInterface::class));
    }
}
