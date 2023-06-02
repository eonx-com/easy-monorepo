<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Search;

use Elasticsearch\Client;
use EonX\EasyCore\Search\ElasticsearchSearchService;
use EonX\EasyCore\Tests\AbstractTestCase;
use Mockery\MockInterface;
use stdClass;

final class ElasticsearchSearchServiceTest extends AbstractTestCase
{
    public function testSearchSucceedsAndReplacesEmptyArraysWithEmptyObjectsInMatchAllRecursively(): void
    {
        $index = 'no-matter';
        $body = [
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'term' => [
                                'some-field' => 123,
                            ],
                            // wrong query, only for recursive fix tests
                            'match_all' => [],
                        ],
                    ],
                ],
                'match_all' => [],
            ],
        ];
        $expectedParams = [
            '_source_excludes' => ['_access_tokens'],
            'index' => $index,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            'bool' => [
                                'must' => [
                                    [
                                        'term' => [
                                            'some-field' => 123,
                                        ],
                                        'match_all' => new stdClass(),
                                    ],
                                ],
                            ],
                            'match_all' => new stdClass(),
                        ],
                        'filter' => [
                            [
                                'terms' => [
                                    '_access_tokens' => ['anonymous'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        /** @var \Elasticsearch\Client $client */
        $client = $this->mock(
            Client::class,
            static function (MockInterface $mock) use ($expectedParams): void {
                $mock
                    ->shouldReceive('search')
                    ->once()
                    ->withArgs(static function ($arg) use ($expectedParams) {
                        self::assertEquals($expectedParams, $arg);

                        return true;
                    })
                    ->andReturn([]);
            }
        );
        $searchService = new ElasticsearchSearchService($client);

        $searchService->search($index, $body);
    }
}
