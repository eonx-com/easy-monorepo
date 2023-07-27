<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\DataTransferObjects;

final class ErrorCodeCategoryDto
{
    public function __construct(
        private readonly string $categoryName,
        private readonly int $nextErrorCodeToUse,
    ) {
    }

    public function asArray(): array
    {
        return [
            'categoryName' => $this->categoryName,
            'nextErrorCodeToUse' => $this->nextErrorCodeToUse,
        ];
    }

    public function getCategoryName(): string
    {
        return $this->categoryName;
    }
}
