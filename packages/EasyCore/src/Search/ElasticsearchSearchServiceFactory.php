<?php

declare(strict_types=1);

namespace EonX\EasyCore\Search;

use Elasticsearch\ClientBuilder;

final class ElasticsearchSearchServiceFactory implements SearchServiceFactoryInterface
{
    /**
     * @var string
     */
    private $elasticsearchHost;

    public function __construct(string $elasticsearchHost)
    {
        $this->elasticsearchHost = $elasticsearchHost;
    }

    public function create(): SearchServiceInterface
    {
        return new ElasticsearchSearchService(ClientBuilder::create()->setHosts([$this->elasticsearchHost])->build());
    }
}
