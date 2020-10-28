<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Paginators;

use EonX\EasyPagination\Exceptions\InvalidPathException;
use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;

abstract class AbstractLengthAwarePaginator implements LengthAwarePaginatorInterface
{
    /**
     * @var \EonX\EasyPagination\Interfaces\StartSizeDataInterface
     */
    protected $paginationData;

    /**
     * @var int
     */
    private $totalPages;

    public function __construct(StartSizeDataInterface $startSizeData)
    {
        $this->paginationData = $startSizeData;
    }

    public function getCurrentPage(): int
    {
        return $this->paginationData->getStart();
    }

    public function getItemsPerPage(): int
    {
        return $this->paginationData->getSize();
    }

    public function getNextPageUrl(): ?string
    {
        return $this->hasNextPage() ? $this->getUrl($this->getCurrentPage() + 1) : null;
    }

    public function getPreviousPageUrl(): ?string
    {
        return $this->hasPreviousPage() ? $this->getUrl($this->getCurrentPage() - 1) : null;
    }

    public function getTotalPages(): int
    {
        if ($this->totalPages !== null) {
            return $this->totalPages;
        }

        return $this->totalPages = \max((int)\ceil($this->getTotalItems() / $this->getItemsPerPage()), 1);
    }

    public function hasNextPage(): bool
    {
        return $this->getTotalPages() > $this->getCurrentPage();
    }

    public function hasPreviousPage(): bool
    {
        return $this->getCurrentPage() > 1;
    }

    private function getUrl(int $start): string
    {
        $urlArr = \parse_url($this->paginationData->getUrl());

        if ($urlArr === false) {
            throw new InvalidPathException(\sprintf(
                'Given path "%s" is invalid and cannot be parsed',
                $this->paginationData->getUrl()
            ));
        }

        return \sprintf(
            '%s?%s%s',
            $this->parseUrl($urlArr),
            \http_build_query($this->parseQuery($start, $urlArr['query'] ?? null), '', '&'),
            isset($urlArr['fragment']) === true ? \sprintf('#%s', $urlArr['fragment']) : ''
        );
    }

    /**
     * @return mixed[]
     */
    private function parseQuery(int $start, ?string $query = null): array
    {
        $default = [
            $this->paginationData->getStartAttribute() => $start,
            $this->paginationData->getSizeAttribute() => $this->paginationData->getSize(),
        ];

        if ($query === null) {
            return $default;
        }

        \parse_str($query, $array);

        return \array_merge($array, $default);
    }

    /**
     * @param mixed[] $urlArr
     */
    private function parseUrl(array $urlArr): string
    {
        $url = [];

        if (empty($urlArr['scheme']) === false) {
            $url[] = \sprintf('%s://', $urlArr['scheme']);
        }

        foreach (['host', 'path'] as $key) {
            if (empty($urlArr[$key])) {
                continue;
            }

            $url[] = $urlArr[$key];
        }

        return \implode('', $url);
    }
}
