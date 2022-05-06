<?php

declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData;

interface SensitiveDataSanitizerInterface
{
    public const DEFAULT_MASK_PATTERN = '*REDACTED*';

    public function sanitize(mixed $data): mixed;
}
