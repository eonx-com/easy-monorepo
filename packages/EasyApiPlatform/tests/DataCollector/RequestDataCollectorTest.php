<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\DataCollector;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use ApiPlatform\Symfony\Bundle\DataCollector\RequestDataCollector as DecoratedRequestDataCollector;
use EonX\EasyApiPlatform\DataCollector\RequestDataCollector;
use EonX\EasyApiPlatform\Tests\AbstractTestCase;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\TraceableChainSimpleDataPersister;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequestDataCollectorTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testGetAcceptableContentTypesSucceeds
     */
    public function providerDataForGetAcceptableContentTypesMethod(): iterable
    {
        yield 'the "data" array with the "acceptable_content_types" key' => [
            'data' => [
                'acceptable_content_types' => ['some-type'],
            ],
            'expectedTypes' => ['some-type'],
        ];

        yield 'the "data" array without the "acceptable_content_types" key' => [
            'data' => ['foo' => 'bar'],
            'expectedTypes' => [],
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testGetCollectionDataProvidersSucceeds
     */
    public function providerDataForGetCollectionDataProvidersMethod(): iterable
    {
        yield 'the "data" array with the "dataProviders.collection" key' => [
            'data' => [
                'dataProviders' => [
                    'collection' => [
                        'context' => ['some-context'],
                        'responses' => ['some-value'],
                    ],
                ],
            ],
            'expectedCollectionData' => [
                'context' => ['some-context'],
                'responses' => ['some-value'],
            ],
        ];

        yield 'the "data" array without the "dataProviders.collection" key' => [
            'data' => ['foo' => 'bar'],
            'expectedCollectionData' => [
                'context' => [],
                'responses' => [],
            ],
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testGetCountersSucceeds
     */
    public function providerDataForGetCountersMethod(): iterable
    {
        yield 'the "data" array with the "counters" key' => [
            'data' => [
                'counters' => ['some-counter'],
            ],
            'expectedCounters' => ['some-counter'],
        ];

        yield 'the "data" array without the "counters" key' => [
            'data' => ['foo' => 'bar'],
            'expectedCounters' => [],
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testGetDataPersistersSucceeds
     */
    public function providerDataForGetDataPersistersMethod(): iterable
    {
        yield 'the "data" array with the "dataPersisters" key' => [
            'data' => [
                'dataPersisters' => ['responses' => ['some-response']],
            ],
            'expectedDataPersisters' => ['responses' => ['some-response']],
        ];

        yield 'the "data" array without the "dataPersisters" key' => [
            'data' => ['foo' => 'bar'],
            'expectedDataPersisters' => ['responses' => []],
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testGetFiltersSucceeds
     */
    public function providerDataForGetFiltersMethod(): iterable
    {
        yield 'the "data" array with the "filters" key' => [
            'data' => [
                'filters' => ['some-filter'],
            ],
            'expectedFilters' => ['some-filter'],
        ];

        yield 'the "data" array without the "filters" key' => [
            'data' => ['foo' => 'bar'],
            'expectedFilters' => [],
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testGetItemDataProvidersSucceeds
     */
    public function providerDataForGetItemDataProvidersMethod(): iterable
    {
        yield 'the "data" array with the "dataProviders.item" key' => [
            'data' => [
                'dataProviders' => [
                    'item' => [
                        'context' => ['some-context'],
                        'responses' => ['some-value'],
                    ],
                ],
            ],
            'expectedItemData' => [
                'context' => ['some-context'],
                'responses' => ['some-value'],
            ],
        ];

        yield 'the "data" array without the "dataProviders.item" key' => [
            'data' => ['foo' => 'bar'],
            'expectedItemData' => [
                'context' => [],
                'responses' => [],
            ],
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testGetRequestAttributesSucceeds
     */
    public function providerDataForGetRequestAttributesMethod(): iterable
    {
        yield 'the "data" array with the "request_attributes" key' => [
            'data' => [
                'request_attributes' => ['some-request-attribute'],
            ],
            'expectedRequestAttributes' => ['some-request-attribute'],
        ];

        yield 'the "data" array without the "request_attributes" key' => [
            'data' => ['foo' => 'bar'],
            'expectedRequestAttributes' => [],
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testGetResourceClassSucceeds
     */
    public function providerDataForGetResourceClassMethod(): iterable
    {
        yield 'the "data" array with the "resource_class" key' => [
            'data' => [
                'resource_class' => 'some-resource-class',
            ],
            'expectedResourceClass' => 'some-resource-class',
        ];

        yield 'the "data" array without the "resource_class" key' => [
            'data' => ['foo' => 'bar'],
            'expectedResourceClass' => null,
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testGetResourceMetadataCollectionSucceeds
     */
    public function providerDataForGetResourceMetadataCollectionMethod(): iterable
    {
        yield 'the "data" array with the "resource_metadata_collection" key' => [
            'data' => [
                'resource_metadata_collection' => 'some-mixed-data',
            ],
            'expectedResourceMetadataCollection' => 'some-mixed-data',
        ];

        yield 'the "data" array without the "resource_metadata_collection" key' => [
            'data' => ['foo' => 'bar'],
            'expectedResourceMetadataCollection' => null,
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testGetSubresourceDataProvidersSucceeds
     */
    public function providerDataForGetSubresourceDataProvidersMethod(): iterable
    {
        yield 'the "data" array with the "dataProviders.subresource" key' => [
            'data' => [
                'dataProviders' => [
                    'subresource' => [
                        'context' => ['some-context'],
                        'responses' => ['some-value'],
                    ],
                ],
            ],
            'expectedSubresourceData' => [
                'context' => ['some-context'],
                'responses' => ['some-value'],
            ],
        ];

        yield 'the "data" array without the "dataProviders.subresource" key' => [
            'data' => ['foo' => 'bar'],
            'expectedSubresourceData' => [
                'context' => [],
                'responses' => [],
            ],
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testGetVersionSucceeds
     */
    public function providerDataForGetVersionMethod(): iterable
    {
        yield 'the "data" array with the "version" key' => [
            'data' => [
                'version' => 'some-version',
            ],
            'expectedVersion' => 'some-version',
        ];

        yield 'the "data" array without the "version" key' => [
            'data' => ['foo' => 'bar'],
            'expectedVersion' => null,
        ];
    }

    public function testCollectSucceeds(): void
    {
        /** @var \ApiPlatform\Core\DataPersister\DataPersisterInterface $dataPersister */
        $dataPersister = $this->mock(DataPersisterInterface::class);
        $request = new Request();
        $response = new Response();
        /** @var \ApiPlatform\Symfony\Bundle\DataCollector\RequestDataCollector $decoratedRequestDataCollector */
        $decoratedRequestDataCollector = $this->mock(
            DecoratedRequestDataCollector::class,
            static function (MockInterface $mock) use ($request, $response): void {
                $mock->expects('collect')
                    ->with($request, $response, null);

                $mock->expects('getVersion')
                    ->withNoArgs()
                    ->andReturn('some-version');
            }
        );
        $requestDataCollector = new RequestDataCollector($decoratedRequestDataCollector, $dataPersister);

        $requestDataCollector->collect($request, $response);

        /** @var array<string, mixed> $actualData */
        $actualData = $this->getPrivatePropertyValue($requestDataCollector, 'data');
        self::assertArrayNotHasKey('dataPersisters', $actualData);
        self::assertSame('some-version', $actualData['version']);
    }

    public function testCollectSucceedsWithTraceableChainSimpleDataPersister(): void
    {
        /** @var \EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\TraceableChainSimpleDataPersister $dataPersister */
        $dataPersister = $this->mock(
            TraceableChainSimpleDataPersister::class,
            static function (MockInterface $mock): void {
                $mock->expects('getPersistersResponse')
                    ->withNoArgs()
                    ->andReturn(['response-1', 'response-2']);
            }
        );
        $request = new Request();
        $response = new Response();
        /** @var \ApiPlatform\Symfony\Bundle\DataCollector\RequestDataCollector $decoratedRequestDataCollector */
        $decoratedRequestDataCollector = $this->mock(
            DecoratedRequestDataCollector::class,
            static function (MockInterface $mock) use ($request, $response): void {
                $mock->expects('collect')
                    ->with($request, $response, null);

                $mock->expects('getVersion')
                    ->withNoArgs()
                    ->andReturn('some-version');
            }
        );
        $requestDataCollector = new RequestDataCollector($decoratedRequestDataCollector, $dataPersister);

        $requestDataCollector->collect($request, $response);

        /** @var array<string, mixed> $actualData */
        $actualData = $this->getPrivatePropertyValue($requestDataCollector, 'data');
        self::assertSame(['responses' => ['response-1', 'response-2']], $actualData['dataPersisters']);
        self::assertSame('some-version', $actualData['version']);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, mixed> $expectedTypes
     *
     * @dataProvider providerDataForGetAcceptableContentTypesMethod
     */
    public function testGetAcceptableContentTypesSucceeds(array $data, array $expectedTypes): void
    {
        /** @var \ApiPlatform\Core\DataPersister\DataPersisterInterface $dataPersister */
        $dataPersister = $this->mock(DataPersisterInterface::class);
        /** @var \ApiPlatform\Symfony\Bundle\DataCollector\RequestDataCollector $decoratedRequestDataCollector */
        $decoratedRequestDataCollector = $this->mock(DecoratedRequestDataCollector::class);
        $requestDataCollector = new RequestDataCollector($decoratedRequestDataCollector, $dataPersister);
        $this->setPrivatePropertyValue($requestDataCollector, 'data', $data);

        $result = $requestDataCollector->getAcceptableContentTypes();

        self::assertSame($expectedTypes, $result);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $expectedCollectionData
     *
     * @dataProvider providerDataForGetCollectionDataProvidersMethod
     */
    public function testGetCollectionDataProvidersSucceeds(array $data, array $expectedCollectionData): void
    {
        /** @var \ApiPlatform\Core\DataPersister\DataPersisterInterface $dataPersister */
        $dataPersister = $this->mock(DataPersisterInterface::class);
        /** @var \ApiPlatform\Symfony\Bundle\DataCollector\RequestDataCollector $decoratedRequestDataCollector */
        $decoratedRequestDataCollector = $this->mock(DecoratedRequestDataCollector::class);
        $requestDataCollector = new RequestDataCollector($decoratedRequestDataCollector, $dataPersister);
        $this->setPrivatePropertyValue($requestDataCollector, 'data', $data);

        $result = $requestDataCollector->getCollectionDataProviders();

        self::assertSame($expectedCollectionData, $result);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, mixed> $expectedCounters
     *
     * @dataProvider providerDataForGetCountersMethod
     */
    public function testGetCountersSucceeds(array $data, array $expectedCounters): void
    {
        /** @var \ApiPlatform\Core\DataPersister\DataPersisterInterface $dataPersister */
        $dataPersister = $this->mock(DataPersisterInterface::class);
        /** @var \ApiPlatform\Symfony\Bundle\DataCollector\RequestDataCollector $decoratedRequestDataCollector */
        $decoratedRequestDataCollector = $this->mock(DecoratedRequestDataCollector::class);
        $requestDataCollector = new RequestDataCollector($decoratedRequestDataCollector, $dataPersister);
        $this->setPrivatePropertyValue($requestDataCollector, 'data', $data);

        $result = $requestDataCollector->getCounters();

        self::assertSame($expectedCounters, $result);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $expectedDataPersisters
     *
     * @dataProvider providerDataForGetDataPersistersMethod
     */
    public function testGetDataPersistersSucceeds(array $data, array $expectedDataPersisters): void
    {
        /** @var \ApiPlatform\Core\DataPersister\DataPersisterInterface $dataPersister */
        $dataPersister = $this->mock(DataPersisterInterface::class);
        /** @var \ApiPlatform\Symfony\Bundle\DataCollector\RequestDataCollector $decoratedRequestDataCollector */
        $decoratedRequestDataCollector = $this->mock(DecoratedRequestDataCollector::class);
        $requestDataCollector = new RequestDataCollector($decoratedRequestDataCollector, $dataPersister);
        $this->setPrivatePropertyValue($requestDataCollector, 'data', $data);

        $result = $requestDataCollector->getDataPersisters();

        self::assertSame($expectedDataPersisters, $result);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, mixed> $expectedFilters
     *
     * @dataProvider providerDataForGetFiltersMethod
     */
    public function testGetFiltersSucceeds(array $data, array $expectedFilters): void
    {
        /** @var \ApiPlatform\Core\DataPersister\DataPersisterInterface $dataPersister */
        $dataPersister = $this->mock(DataPersisterInterface::class);
        /** @var \ApiPlatform\Symfony\Bundle\DataCollector\RequestDataCollector $decoratedRequestDataCollector */
        $decoratedRequestDataCollector = $this->mock(DecoratedRequestDataCollector::class);
        $requestDataCollector = new RequestDataCollector($decoratedRequestDataCollector, $dataPersister);
        $this->setPrivatePropertyValue($requestDataCollector, 'data', $data);

        $result = $requestDataCollector->getFilters();

        self::assertSame($expectedFilters, $result);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $expectedItemData
     *
     * @dataProvider providerDataForGetItemDataProvidersMethod
     */
    public function testGetItemDataProvidersSucceeds(array $data, array $expectedItemData): void
    {
        /** @var \ApiPlatform\Core\DataPersister\DataPersisterInterface $dataPersister */
        $dataPersister = $this->mock(DataPersisterInterface::class);
        /** @var \ApiPlatform\Symfony\Bundle\DataCollector\RequestDataCollector $decoratedRequestDataCollector */
        $decoratedRequestDataCollector = $this->mock(DecoratedRequestDataCollector::class);
        $requestDataCollector = new RequestDataCollector($decoratedRequestDataCollector, $dataPersister);
        $this->setPrivatePropertyValue($requestDataCollector, 'data', $data);

        $result = $requestDataCollector->getItemDataProviders();

        self::assertSame($expectedItemData, $result);
    }

    public function testGetNameSucceeds(): void
    {
        /** @var \ApiPlatform\Core\DataPersister\DataPersisterInterface $dataPersister */
        $dataPersister = $this->mock(DataPersisterInterface::class);
        /** @var \ApiPlatform\Symfony\Bundle\DataCollector\RequestDataCollector $decoratedRequestDataCollector */
        $decoratedRequestDataCollector = $this->mock(DecoratedRequestDataCollector::class);
        $requestDataCollector = new RequestDataCollector($decoratedRequestDataCollector, $dataPersister);

        $result = $requestDataCollector->getName();

        self::assertSame('api_platform.data_collector.request', $result);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, mixed> $expectedRequestAttributes
     *
     * @dataProvider providerDataForGetRequestAttributesMethod
     */
    public function testGetRequestAttributesSucceeds(array $data, array $expectedRequestAttributes): void
    {
        /** @var \ApiPlatform\Core\DataPersister\DataPersisterInterface $dataPersister */
        $dataPersister = $this->mock(DataPersisterInterface::class);
        /** @var \ApiPlatform\Symfony\Bundle\DataCollector\RequestDataCollector $decoratedRequestDataCollector */
        $decoratedRequestDataCollector = $this->mock(DecoratedRequestDataCollector::class);
        $requestDataCollector = new RequestDataCollector($decoratedRequestDataCollector, $dataPersister);
        $this->setPrivatePropertyValue($requestDataCollector, 'data', $data);

        $result = $requestDataCollector->getRequestAttributes();

        self::assertSame($expectedRequestAttributes, $result);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @dataProvider providerDataForGetResourceClassMethod
     */
    public function testGetResourceClassSucceeds(array $data, ?string $expectedResourceClass = null): void
    {
        /** @var \ApiPlatform\Core\DataPersister\DataPersisterInterface $dataPersister */
        $dataPersister = $this->mock(DataPersisterInterface::class);
        /** @var \ApiPlatform\Symfony\Bundle\DataCollector\RequestDataCollector $decoratedRequestDataCollector */
        $decoratedRequestDataCollector = $this->mock(DecoratedRequestDataCollector::class);
        $requestDataCollector = new RequestDataCollector($decoratedRequestDataCollector, $dataPersister);
        $this->setPrivatePropertyValue($requestDataCollector, 'data', $data);

        $result = $requestDataCollector->getResourceClass();

        self::assertSame($expectedResourceClass, $result);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @dataProvider providerDataForGetResourceMetadataCollectionMethod
     */
    public function testGetResourceMetadataCollectionSucceeds(
        array $data,
        mixed $expectedResourceMetadataCollection
    ): void {
        /** @var \ApiPlatform\Core\DataPersister\DataPersisterInterface $dataPersister */
        $dataPersister = $this->mock(DataPersisterInterface::class);
        /** @var \ApiPlatform\Symfony\Bundle\DataCollector\RequestDataCollector $decoratedRequestDataCollector */
        $decoratedRequestDataCollector = $this->mock(DecoratedRequestDataCollector::class);
        $requestDataCollector = new RequestDataCollector($decoratedRequestDataCollector, $dataPersister);
        $this->setPrivatePropertyValue($requestDataCollector, 'data', $data);

        $result = $requestDataCollector->getResourceMetadataCollection();

        self::assertSame($expectedResourceMetadataCollection, $result);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $expectedSubresourceData
     *
     * @dataProvider providerDataForGetSubresourceDataProvidersMethod
     */
    public function testGetSubresourceDataProvidersSucceeds(array $data, array $expectedSubresourceData): void
    {
        /** @var \ApiPlatform\Core\DataPersister\DataPersisterInterface $dataPersister */
        $dataPersister = $this->mock(DataPersisterInterface::class);
        /** @var \ApiPlatform\Symfony\Bundle\DataCollector\RequestDataCollector $decoratedRequestDataCollector */
        $decoratedRequestDataCollector = $this->mock(DecoratedRequestDataCollector::class);
        $requestDataCollector = new RequestDataCollector($decoratedRequestDataCollector, $dataPersister);
        $this->setPrivatePropertyValue($requestDataCollector, 'data', $data);

        $result = $requestDataCollector->getSubresourceDataProviders();

        self::assertSame($expectedSubresourceData, $result);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @dataProvider providerDataForGetVersionMethod
     */
    public function testGetVersionSucceeds(array $data, ?string $expectedVersion = null): void
    {
        /** @var \ApiPlatform\Core\DataPersister\DataPersisterInterface $dataPersister */
        $dataPersister = $this->mock(DataPersisterInterface::class);
        /** @var \ApiPlatform\Symfony\Bundle\DataCollector\RequestDataCollector $decoratedRequestDataCollector */
        $decoratedRequestDataCollector = $this->mock(DecoratedRequestDataCollector::class);
        $requestDataCollector = new RequestDataCollector($decoratedRequestDataCollector, $dataPersister);
        $this->setPrivatePropertyValue($requestDataCollector, 'data', $data);

        $result = $requestDataCollector->getVersion();

        self::assertSame($expectedVersion, $result);
    }

    public function testResetSucceeds(): void
    {
        /** @var \ApiPlatform\Core\DataPersister\DataPersisterInterface $dataPersister */
        $dataPersister = $this->mock(DataPersisterInterface::class);
        /** @var \ApiPlatform\Symfony\Bundle\DataCollector\RequestDataCollector $decoratedRequestDataCollector */
        $decoratedRequestDataCollector = $this->mock(DecoratedRequestDataCollector::class);
        $requestDataCollector = new RequestDataCollector($decoratedRequestDataCollector, $dataPersister);
        $this->setPrivatePropertyValue($requestDataCollector, 'data', ['foo' => 'bar']);

        $requestDataCollector->reset();

        self::assertSame([], $this->getPrivatePropertyValue($requestDataCollector, 'data'));
    }
}
