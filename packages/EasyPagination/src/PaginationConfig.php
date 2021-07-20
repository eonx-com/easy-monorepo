<?php
declare(strict_types=1);

namespace EonX\EasyPagination;

use EonX\EasyPagination\Interfaces\PaginationConfigInterface;

final class PaginationConfig implements PaginationConfigInterface
{
    /**
     * @var string
     */
    private $pageAttribute;

    /**
     * @var int
     */
    private $pageDefault;

    /**
     * @var string
     */
    private $perPageAttribute;

    /**
     * @var int
     */
    private $perPageDefault;

    public function __construct(string $pageAttribute, int $pageDefault, string $perPageAttribute, int $perPageDefault)
    {
        $this->pageAttribute = $pageAttribute;
        $this->pageDefault = $pageDefault;
        $this->perPageAttribute = $perPageAttribute;
        $this->perPageDefault = $perPageDefault;
    }

    public function getPageAttribute(): string
    {
        return $this->pageAttribute;
    }

    public function getPageDefault(): int
    {
        return $this->pageDefault;
    }

    public function getPerPageAttribute(): string
    {
        return $this->perPageAttribute;
    }

    public function getPerPageDefault(): int
    {
        return $this->perPageDefault;
    }
}
