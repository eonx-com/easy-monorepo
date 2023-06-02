<?php

declare(strict_types=1);

namespace EonX\EasyCore\Search;

interface SearchServiceInterface
{
    /**
     * @param string[] $indices
     */
    public function deleteIndices(array $indices): void;

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
    ): array;
}
