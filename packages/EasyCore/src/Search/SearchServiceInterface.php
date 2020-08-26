<?php

declare(strict_types=1);

namespace EonX\EasyCore\Search;

interface SearchServiceInterface
{
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
    ): array;
}
