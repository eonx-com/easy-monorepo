<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Unit\Bundle;

use EonX\EasyPagination\Provider\PaginationProviderInterface;
use EonX\EasyPagination\Resolver\DefaultPaginationResolver;
use EonX\EasyPagination\Resolver\FromHttpFoundationRequestPaginationResolver;
use EonX\EasyPagination\Tests\Stub\Kernel\KernelStub;
use EonX\EasyPagination\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyPagination\ValueObject\PaginationConfigInterface;
use EonX\EasyPagination\ValueObject\PaginationInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;

final class EasyPaginationBundleTest extends AbstractUnitTestCase
{
    /**
     * @see testPaginationResolver
     */
    public static function providerTestPaginationResolver(): iterable
    {
        yield 'Page_PerPage_Defaults' => [
            __DIR__ . '/../../Fixture/config/page_perPage_1_15.php',
            self::createRequest(),
            1,
            15,
        ];

        yield 'Page_PerPage_2_30' => [
            __DIR__ . '/../../Fixture/config/page_perPage_1_15.php',
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
        /** @var \EonX\EasyPagination\Provider\PaginationProviderInterface $paginationProvider */
        $paginationProvider = $container->get(PaginationProviderInterface::class);
        $paginationProvider->setResolver(new FromHttpFoundationRequestPaginationResolver(
            $container->get(PaginationConfigInterface::class),
            $request
        ));

        /** @var \EonX\EasyPagination\ValueObject\PaginationInterface $pagination */
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
