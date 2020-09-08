<?php

declare(strict_types=1);

namespace EonX\EasyCore\Search;

use Elasticsearch\Client;
use stdClass;

final class ElasticsearchSearchService implements SearchServiceInterface
{
    /**
     * @var \Elasticsearch\Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param null|mixed[] $body
     * @param null|mixed[] $accessTokens
     * @param null|mixed[] $options
     *
     * @return mixed[]
     */
    public function search(
        string $index,
        ?array $body = null,
        ?array $accessTokens = null,
        ?array $options = null
    ): array {
        $options = $options ?? [];
        $accessTokensProperty = '_access_tokens';

        // Was implemented in payments
        if (($options['access_tokens_keyword'] ?? false) === true) {
            $accessTokensProperty .= '.keyword';
        }

        $query = $body['query'] ?? ['match_all' => new stdClass()];

        $body['query'] = [
            'bool' => [
                'must' => $query,
                'filter' => [
                    [
                        'terms' => [
                            $accessTokensProperty => $accessTokens ?? ['anonymous'],
                        ],
                    ],
                ],
            ],
        ];

        return $this->client->search([
            '_source_excludes' => ['_access_tokens'],
            'index' => $index,
            'body' => $body,
        ]);
    }
}
