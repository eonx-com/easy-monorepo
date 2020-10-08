<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Bridge\Symfony;

use EonX\EasyPagination\Interfaces\StartSizeDataFactoryInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataResolverInterface;
use EonX\EasyPagination\Resolvers\StartSizeAsArrayInQueryResolver;
use EonX\EasyPagination\Resolvers\StartSizeInQueryResolver;
use EonX\EasyPagination\Tests\AbstractTestCase;
use EonX\EasyPagination\Tests\Bridge\Symfony\Stubs\KernelStub;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\HttpFoundation\Request;

final class EasyPaginationBundleTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestResolverInstance(): iterable
    {
        yield 'array_in_query' => [
            __DIR__ . '/fixtures/array_in_query.yaml',
            StartSizeAsArrayInQueryResolver::class,
        ];

        yield 'in_query' => [__DIR__ . '/fixtures/in_query.yaml', StartSizeInQueryResolver::class];
    }

    /**
     * @return iterable<mixed>
     */
    public function providerTestStartSizeDataResolver(): iterable
    {
        yield 'InQuery_Page_PerPage_Defaults' => [
            __DIR__ . '/fixtures/data/in_query_page_perPage_1_15.yaml',
            $this->createRequest(),
            1,
            15,
        ];

        yield 'InQuery_Page_PerPage_2_30' => [
            __DIR__ . '/fixtures/data/in_query_page_perPage_1_15.yaml',
            $this->createRequest([
                'page' => 2,
                'perPage' => 30,
            ]),
            2,
            30,
        ];
    }

    public function testInvalidResolverInConfig(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            'Invalid configuration for path "easy_pagination.resolver": Invalid resolver "invalid"'
        );

        $kernel = new KernelStub(__DIR__ . '/fixtures/invalid.yaml');
        $kernel->boot();
    }

    /**
     * @dataProvider providerTestResolverInstance
     */
    public function testResolverInstance(string $config, string $instance): void
    {
        $kernel = new KernelStub($config);
        $kernel->boot();

        self::assertInstanceOf($instance, $kernel->getContainer()->get(StartSizeDataResolverInterface::class));
    }

    public function testStartSizeDataFactory(): void
    {
        $kernel = new KernelStub(__DIR__ . '/fixtures/data/in_query_page_perPage_1_15.yaml');
        KernelStub::setRequest($this->createRequest());
        $kernel->boot();

        $factory = $kernel->getContainer()->get(StartSizeDataFactoryInterface::class);
        $startSizeData = $factory->create();

        self::assertInstanceOf(StartSizeDataInterface::class, $startSizeData);
        self::assertEquals(1, $startSizeData->getStart());
        self::assertEquals(15, $startSizeData->getSize());
        self::assertEquals('page', $startSizeData->getStartAttribute());
        self::assertEquals('perPage', $startSizeData->getSizeAttribute());
        self::assertEquals('http://eonx.com/', $startSizeData->getUrl());
    }

    /**
     * @dataProvider providerTestStartSizeDataResolver
     */
    public function testStartSizeDataResolver(string $config, Request $request, int $start, int $size): void
    {
        $kernel = new KernelStub($config);
        KernelStub::setRequest($request);
        $kernel->boot();

        $startSizeData = $kernel->getContainer()->get(StartSizeDataInterface::class);

        self::assertEquals($start, $startSizeData->getStart());
        self::assertEquals($size, $startSizeData->getSize());
    }

    /**
     * @param null|mixed[] $query
     */
    private function createRequest(?array $query = null): Request
    {
        return new Request($query ?? [], [], [], [], [], [
            'HTTP_HOST' => 'eonx.com',
        ]);
    }
}
