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
     * @param null|mixed[] $query
     * @param null|mixed[] $accessTokens
     * @param null|mixed[] $options
     *
     * @return mixed[]
     */
    public function search(
        string $index,
        ?array $query = null,
        ?array $accessTokens = null,
        ?array $options = null
    ): array {
        $options = $options ?? [];
        $accessTokensProperty = '_access_tokens';

        // Was implemented in payments
        if (($options['access_tokens_keyword'] ?? false) === true) {
            $accessTokensProperty .= '.keyword';
        }

        return $this->client->search([
            '_source_excludes' => [
                '_access_tokens',
            ],
            'index' => $index,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => $query ?? ['match_all' => new stdClass()],
                        'filter' => [
                            ['terms' => [$accessTokensProperty => $accessTokens ?? 'anonymous']],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
