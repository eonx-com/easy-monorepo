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

        $query = $body['query'] ?? [
            'match_all' => new stdClass(),
        ];
        $query = $this->replaceEmptyArrayWithEmptyObjectInMatchAllRecursively($query);

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

        $params = [
            '_source_excludes' => ['_access_tokens'],
            'index' => $index,
            'body' => $body,
        ];

        foreach (['from', 'size'] as $name) {
            if (isset($options[$name])) {
                $params[$name] = $options[$name];
            }
        }

        return $this->client->search($params);
    }

    /**
     * Needed to avoid "[match_all] query malformed, no start_object after query name" error.
     *
     * @param mixed[] $query
     *
     * @return mixed[]
     */
    private function replaceEmptyArrayWithEmptyObjectInMatchAllRecursively(array $query): array
    {
        foreach ($query as $key => $value) {
            if ($key === 'match_all' && $value === []) {
                $query[$key] = new stdClass();

                continue;
            }

            if (\is_array($value)) {
                $query[$key] = $this->replaceEmptyArrayWithEmptyObjectInMatchAllRecursively($value);
            }
        }

        return $query;
    }
}
