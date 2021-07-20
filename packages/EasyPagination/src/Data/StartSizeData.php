<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Data;

use EonX\EasyPagination\Interfaces\StartSizeDataInterface;

/**
 * @deprecated since 3.2, will be removed in 4.0.
 */
final class StartSizeData implements StartSizeDataInterface
{
    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $sizeAttribute;

    /**
     * @var int
     */
    private $start;

    /**
     * @var string
     */
    private $startAttribute;

    /**
     * @var string
     */
    private $url;

    public function __construct(
        int $start,
        int $size,
        ?string $startAttribute = null,
        ?string $sizeAttribute = null,
        ?string $url = null
    ) {
        $this->start = $start;
        $this->size = $size;
        $this->startAttribute = $startAttribute ?? 'page';
        $this->sizeAttribute = $sizeAttribute ?? 'perPage';
        $this->url = $url ?? '/';
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getSizeAttribute(): string
    {
        return $this->sizeAttribute;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getStartAttribute(): string
    {
        return $this->startAttribute;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
