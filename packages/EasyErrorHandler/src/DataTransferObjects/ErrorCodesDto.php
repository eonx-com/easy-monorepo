<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\DataTransferObjects;

final class ErrorCodesDto
{
    /**
     * @param \EonX\EasyErrorHandler\DataTransferObjects\ErrorCodeCategoryDto[] $nextGroupedErrorCodes
     */
    public function __construct(
        private readonly int $nextGroupErrorCode = 0,
        private readonly array $nextGroupedErrorCodes = [],
    ) {
    }

    public function getNextGroupErrorCode(): int
    {
        return $this->nextGroupErrorCode;
    }

    /**
     * @return mixed[]
     */
    public function getNextGroupedErrorCodesAsArray(): array
    {
        return \array_map(
            static fn (ErrorCodeCategoryDto $errorCodeCategoryDto) => $errorCodeCategoryDto->asArray(),
            $this->nextGroupedErrorCodes,
        );
    }

    public function hasErrorCodes(): bool
    {
        return \count($this->nextGroupedErrorCodes) > 0;
    }
}
