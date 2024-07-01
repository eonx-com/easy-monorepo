<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\ErrorCodes\ValueObject;

final readonly class ErrorCodes
{
    /**
     * @param \EonX\EasyErrorHandler\ErrorCodes\ValueObject\ErrorCodeCategory[] $nextGroupedErrorCodes
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
            static fn (ErrorCodeCategory $errorCodeCategoryDto): array => $errorCodeCategoryDto->asArray(),
            $this->nextGroupedErrorCodes
        );
    }

    public function hasErrorCodes(): bool
    {
        return \count($this->nextGroupedErrorCodes) > 0;
    }
}
