<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\DataTransferObjects;

final class ErrorCodeCategoryDto
{
    public function __construct(
        private string $categoryName,
        private int $nextErrorCodeToUse
    ) {
    }

    public function getCategoryName(): string
    {
        return $this->categoryName;
    }

    /**
     * @return mixed[]
     */
    public function asArray(): array
    {
        return [
            'categoryName' => $this->categoryName,
            'nextErrorCodeToUse' => $this->nextErrorCodeToUse,
        ];
    }
}
