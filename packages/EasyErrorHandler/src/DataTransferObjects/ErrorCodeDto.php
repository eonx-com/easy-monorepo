<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\DataTransferObjects;

final class ErrorCodeDto
{
    /**
     * @param array<int, string> $splitName
     */
    public function __construct(
        private string $originalName,
        private int $errorCode,
        private array $splitName,
        private ?string $groupSeparator = null,
    ) {
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    public function getGroupSeparator(): string
    {
        return $this->groupSeparator ?? '';
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    /**
     * @return array<int, string>
     */
    public function getSplitName(): array
    {
        return $this->splitName;
    }
}
