<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Bridge\Laravel;

use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Interfaces\PaginationProviderInterface;
use EonX\EasyPagination\Resolvers\DefaultPaginationResolver;

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
