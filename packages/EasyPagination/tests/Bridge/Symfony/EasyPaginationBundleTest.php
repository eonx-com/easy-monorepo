<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Bridge\Symfony;

use EonX\EasyPagination\Interfaces\PaginationConfigInterface;
use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Interfaces\PaginationProviderInterface;
use EonX\EasyPagination\Resolvers\DefaultPaginationResolver;
use EonX\EasyPagination\Resolvers\FromHttpFoundationRequestResolver;
use EonX\EasyPagination\Tests\AbstractTestCase;
use EonX\EasyPagination\Tests\Bridge\Symfony\Stubs\KernelStub;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;

final class EasyPaginationBundleTest extends AbstractTestCase
{
    /**
     * @see testPaginationResolver
     */
    public static function providerTestPaginationResolver(): iterable
    {
        yield 'Page_PerPage_Defaults' => [
            __DIR__ . '/fixtures/data/page_perPage_1_15.yaml',
            self::createRequest(),
            1,
            15,
        ];

        yield 'Page_PerPage_2_30' => [
            __DIR__ . '/fixtures/data/page_perPage_1_15.yaml',
            self::createRequest([
                'page' => 2,
                'perPage' => 30,
            ]),
            2,
            30,
        ];
    }

    #[DataProvider('providerTestPaginationResolver')]
    public function testPaginationResolver(string $config, Request $request, int $page, int $perPage): void
    {
        $kernel = new KernelStub($config);
        KernelStub::setRequest($request);
        $kernel->boot();
        $container = $kernel->getContainer();
        /** @var \EonX\EasyPagination\Interfaces\PaginationProviderInterface $paginationProvider */
        $paginationProvider = $container->get(PaginationProviderInterface::class);
        $paginationProvider->setResolver(new FromHttpFoundationRequestResolver(
            $container->get(PaginationConfigInterface::class),
            $request
        ));

        /** @var \EonX\EasyPagination\Interfaces\PaginationInterface $pagination */
        $pagination = $container->get(PaginationInterface::class);

        self::assertSame($page, $pagination->getPage());
        self::assertSame($perPage, $pagination->getPerPage());
        self::assertSame('page', $pagination->getPageAttribute());
        self::assertSame('perPage', $pagination->getPerPageAttribute());
        self::assertSame(
            "http://eonx.com?page={$page}&perPage={$perPage}",
            $pagination->getUrl($pagination->getPage())
        );
    }

    public function testSanity(): void
    {
        $kernel = new KernelStub();
        $kernel->boot();
        $container = $kernel->getContainer();

        $paginationProvider = $container->get(PaginationProviderInterface::class);
        $paginationProvider->setResolver(new DefaultPaginationResolver($paginationProvider->getPaginationConfig()));

        self::assertInstanceOf(PaginationProviderInterface::class, $paginationProvider);
        self::assertInstanceOf(PaginationInterface::class, $container->get(PaginationInterface::class));
    }

    private static function createRequest(?array $query = null): Request
    {
        return new Request($query ?? [], [], [], [], [], [
            'HTTP_HOST' => 'eonx.com',
        ]);
    }
}
