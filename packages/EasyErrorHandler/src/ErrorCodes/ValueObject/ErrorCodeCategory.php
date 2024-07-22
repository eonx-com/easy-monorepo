<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\ErrorCodes\ValueObject;

final readonly class ErrorCodeCategory
{
    public function __construct(
        private string $categoryName,
        private int $nextErrorCodeToUse,
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
