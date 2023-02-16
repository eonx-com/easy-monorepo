<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\DataTransferObjects;

final class ErrorCodeDto
{
    /**
     * @param array<int, string> $splittedName
     */
    public function __construct(
        private string $originalName,
        private int $errorCode,
        private array $splittedName,
        private ?string $groupSeparator = null
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function getSplittedName(): array
    {
        return $this->splittedName;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    public function getGroupSeparator(): string
    {
        return $this->groupSeparator ?? '';
    }
}
