<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Provider;

use EonX\EasyPagination\Exception\InvalidPageDefaultException;
use EonX\EasyPagination\Exception\InvalidPerPageDefaultException;
use EonX\EasyPagination\Exception\InvalidPerPageMaxException;

final readonly class PaginationConfigProvider implements PaginationConfigProviderInterface
{
    public function __construct(
        private string $pageAttribute,
        private int $pageDefault,
        private string $perPageAttribute,
        private int $perPageDefault,
        private ?int $perPageMax = null,
    ) {
        // A default below 1 is not a valid page or page size, the resolver would divide by zero on a page size of 0
        if ($pageDefault < 1) {
            throw new InvalidPageDefaultException(\sprintf(
                'The default page must be at least 1, %d given.',
                $pageDefault
            ));
        }

        if ($perPageDefault < 1) {
            throw new InvalidPerPageDefaultException(\sprintf(
                'The default page size must be at least 1, %d given.',
                $perPageDefault
            ));
        }

        // A maximum below 1 would turn a valid page size into 0 or a negative one (no limit in Eloquent)
        if ($perPageMax !== null && $perPageMax < 1) {
            throw new InvalidPerPageMaxException(\sprintf(
                'The maximum page size must be at least 1 or null, %d given.',
                $perPageMax
            ));
        }
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

    public function getPerPageMax(): ?int
    {
        return $this->perPageMax;
    }
}
