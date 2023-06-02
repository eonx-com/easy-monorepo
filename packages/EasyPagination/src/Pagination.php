<?php

declare(strict_types=1);

namespace EonX\EasyPagination;

use EonX\EasyPagination\Interfaces\PaginationInterface;
use Laminas\Uri\Uri;

final class Pagination implements PaginationInterface
{
    /**
     * @var int
     */
    private $page;

    /**
     * @var string
     */
    private $pageAttribute;

    /**
     * @var int
     */
    private $perPage;

    /**
     * @var string
     */
    private $perPageAttribute;

    /**
     * @var string
     */
    private $url;

    /**
     * @var null|callable
     */
    private $urlResolver;

    public function __construct(
        int $page,
        int $perPage,
        ?string $pageAttribute = null,
        ?string $perPageAttribute = null,
        ?string $url = null,
    ) {
        $this->page = $page;
        $this->perPage = $perPage;
        $this->pageAttribute = $pageAttribute ?? self::DEFAULT_PAGE_ATTRIBUTE;
        $this->perPageAttribute = $perPageAttribute ?? self::DEFAULT_PER_PAGE_ATTRIBUTE;
        $this->url = $url ?? self::DEFAULT_URL;
    }

    public static function create(
        int $page,
        int $perPage,
        ?string $pageAttribute = null,
        ?string $perPageAttribute = null,
        ?string $url = null,
    ): self {
        return new self($page, $perPage, $pageAttribute, $perPageAttribute, $url);
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPageAttribute(): string
    {
        return $this->pageAttribute;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getPerPageAttribute(): string
    {
        return $this->perPageAttribute;
    }

    public function getUrl(int $page): string
    {
        $urlResolver = $this->urlResolver ?? $this->getDefaultUrlResolver();

        return $urlResolver(new Uri($this->url), $this, $page)
            ->toString();
    }

    public function setUrlResolver(?callable $urlResolver = null): PaginationInterface
    {
        $this->urlResolver = $urlResolver;

        return $this;
    }

    private function getDefaultUrlResolver(): callable
    {
        return static function (Uri $uri, PaginationInterface $pagination, int $page): Uri {
            $query = $uri->getQueryAsArray();

            $query[$pagination->getPageAttribute()] = $page > 0 ? $page : 1;
            $query[$pagination->getPerPageAttribute()] = $pagination->getPerPage();

            return $uri->setQuery($query);
        };
    }
}
