<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\DataTransferObjects;

final readonly class ErrorCodesDto
{
    /**
     * @param \EonX\EasyErrorHandler\DataTransferObjects\ErrorCodeCategoryDto[] $nextGroupedErrorCodes
     */
    public function __construct(
        private int $nextGroupErrorCode = 0,
        private array $nextGroupedErrorCodes = [],
    ) {
    }

    public function getNextGroupErrorCode(): int
    {
        return $this->nextGroupErrorCode;
    }

    public function getNextGroupedErrorCodesAsArray(): array
    {
        return \array_map(
            static fn (ErrorCodeCategoryDto $errorCodeCategoryDto): array => $errorCodeCategoryDto->asArray(),
            $this->nextGroupedErrorCodes
        );
    }

    public function hasErrorCodes(): bool
    {
        return \count($this->nextGroupedErrorCodes) > 0;
    }
}
