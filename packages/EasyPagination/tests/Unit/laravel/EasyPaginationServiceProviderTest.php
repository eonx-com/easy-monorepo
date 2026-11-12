<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Unit\Laravel;

use EonX\EasyPagination\Laravel\EasyPaginationServiceProvider;
use EonX\EasyPagination\Laravel\Middleware\PaginationFromRequestMiddleware;
use EonX\EasyPagination\Provider\PaginationProviderInterface;
use EonX\EasyPagination\Resolver\DefaultPaginationResolver;
use EonX\EasyPagination\Resolver\FromHttpFoundationRequestPaginationResolver;
use Laravel\Lumen\Application;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;

final class EasyPaginationServiceProviderTest extends AbstractLaravelTestCase
{
    /**
     * @see testItRegistersDefaultResolver
     */
    public static function provideDefaultResolverConfig(): iterable
    {
        yield 'not set' => [
            'config' => [],
            'expectedRegistered' => true,
        ];

        yield 'use_default_resolver true' => [
            'config' => ['easy-pagination.use_default_resolver' => true],
            'expectedRegistered' => true,
        ];

        yield 'use_default_resolver false' => [
            'config' => ['easy-pagination.use_default_resolver' => false],
            'expectedRegistered' => false,
        ];

        yield 'misspelled user_default_resolver false' => [
            'config' => ['easy-pagination.user_default_resolver' => false],
            'expectedRegistered' => false,
        ];
    }

    public function testItHasDefaultConfig(): void
    {
        $app = $this->getApplication();

        $config = $app->make(PaginationProviderInterface::class)
            ->getPaginationConfigProvider();

        self::assertSame('page', $config->getPageAttribute());
        self::assertSame(1, $config->getPageDefault());
        self::assertSame('perPage', $config->getPerPageAttribute());
        self::assertSame(15, $config->getPerPageDefault());
        self::assertNull($config->getPerPageMax());
    }

    public function testItPassesPerPageMaxFromConfig(): void
    {
        $app = $this->getApplication();
        $app->make('config')
            ->set('easy-pagination.pagination', [
                'page_attribute' => 'page',
                'page_default' => 1,
                'per_page_attribute' => 'perPage',
                'per_page_default' => 15,
                'per_page_max' => '50',
            ]);
        $paginationProvider = $app->make(PaginationProviderInterface::class);
        $paginationProvider->setResolver(new FromHttpFoundationRequestPaginationResolver(
            $paginationProvider->getPaginationConfigProvider(),
            new Request(['perPage' => 500])
        ));

        $pagination = $paginationProvider->getPagination();

        self::assertSame(50, $paginationProvider->getPaginationConfigProvider()->getPerPageMax());
        self::assertSame(50, $pagination->getPerPage());
    }

    #[DataProvider('provideDefaultResolverConfig')]
    public function testItRegistersDefaultResolver(array $config, bool $expectedRegistered): void
    {
        $app = new Application(__DIR__);
        $app->make('config')
            ->set($config);

        $app->register(EasyPaginationServiceProvider::class);

        self::assertSame($expectedRegistered, $app->bound(PaginationFromRequestMiddleware::class));
    }

    public function testSanity(): void
    {
        $app = $this->getApplication();

        $paginationProvider = $app->make(PaginationProviderInterface::class);
        $paginationProvider
            ->setResolver(new DefaultPaginationResolver($paginationProvider->getPaginationConfigProvider()));

        self::assertInstanceOf(PaginationProviderInterface::class, $paginationProvider);
    }
}
