<?php

declare(strict_types=1);

namespace EonX\EasyCore\Search;

use Elasticsearch\Client;
use stdClass;

final class ElasticsearchSearchService implements SearchServiceInterface
{
    /**
     * @var string[]
     */
    private const EXCLUDE_OPTIONS = ['_source_excludes', 'index', 'body'];

    /**
     * @var \Elasticsearch\Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string[] $indices
     */
    public function deleteIndices(array $indices): void
    {
        $indicesNamespace = $this->client->indices();

        foreach ($indices as $index) {
            $indicesNamespace->delete([
                'allow_no_indices' => true,
                'index' => $index,
            ]);
        }
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
        ?array $options = null,
    ): array {
        $options = $options ?? [];
        $accessTokensProperty = '_access_tokens';

        // Was implemented in payments
        if (($options['access_tokens_keyword'] ?? false) === true) {
            $accessTokensProperty .= '.keyword';
        }

        $defaultQuery = [
            'match_all' => new stdClass(),
        ];
        $query = $body['query'] ?? $defaultQuery;
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

        foreach ($options as $name => $value) {
            $name = (string)$name;

            if (\in_array($name, self::EXCLUDE_OPTIONS, true)) {
                continue;
            }

            $params[$name] = $value;
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
