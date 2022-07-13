<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\DataTransferObjects;

final class ErrorCodesDto
{
    /**
     * @param mixed[] $nextGroupedErrorCodes
     */
    public function __construct(
        private int $nextGroupErrorCode = 0,
        private array $nextGroupedErrorCodes = []
    ) {
    }

    public function getNextGroupErrorCode(): int
    {
        return $this->nextGroupErrorCode;
    }

    /**
     * @return mixed[]
     */
    public function getNextGroupedErrorCodes(): array
    {
        return $this->nextGroupedErrorCodes;
    }

    public function hasErrorCodes(): bool
    {
        return \count($this->nextGroupedErrorCodes) > 0;
    }
}
